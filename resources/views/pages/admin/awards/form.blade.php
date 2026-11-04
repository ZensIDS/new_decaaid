@extends('layouts.admin')

@php
    $editing = (bool) $award;
    $canDelete = auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_ADMIN);

    $val = fn (string $field, string $lang) => $editing ? ($award->{$field}[$lang] ?? '') : '';

    // B5: terjemahan otomatis (butuh penyedia di .env dan migration kolom translated_from).
    $autoAvailable = \App\Support\Translator::enabled();
    $transColumns  = \App\Models\Award::hasTranslatedFrom();

    $currentTrans = old('translation_status', $editing ? $award->translation_status : 'manual');
    $imageUrl = $editing && $award->image && file_exists(public_path($award->image)) ? asset($award->image) : null;

    // Buka tab EN otomatis bila galat hanya ada di kolom EN.
    $enErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.en'));
    $idErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.id'));
    $startLang = $enErrors && ! $idErrors ? 'en' : 'id';
@endphp

@section('title', $editing ? 'Ubah Penghargaan' : 'Tambah Penghargaan')

@section('content')
    <x-admin.page-header :title="$editing ? 'Ubah Penghargaan' : 'Tambah Penghargaan'"
                         :description="$editing ? 'Perbarui isi, tahun, atau foto penghargaan ini.' : 'Isi nama penghargaan, tahun, dan keterangan singkat. Bahasa Inggris boleh dikosongkan; pengunjung EN akan melihat teks Indonesia.'"
                         :breadcrumbs="[['label' => 'Penghargaan', 'url' => route('admin.awards.index')], ['label' => $editing ? 'Ubah' : 'Tambah']]" />

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate enctype="multipart/form-data" class="contents"
              action="{{ $editing ? route('admin.awards.update', $award) : route('admin.awards.store') }}"
              @media-chosen.window="chosen($event.detail)"
              x-data="{
                  lang: @js($startLang),
                  title: @js(old('title.id', $val('title', 'id'))),
                  year: @js((string) old('year', $editing ? $award->year : now()->year)),
                  visible: @js((bool) old('is_visible', $editing ? $award->is_visible : true)),
                  imageUrl: @js($imageUrl),
                  removeImage: false,
                  pick(e) {
                      const f = e.target.files[0];
                      if (!f) return;
                      if (f.size > 5 * 1024 * 1024) { this.$dispatch('toast', { type: 'error', text: 'Gambar terlalu besar (maksimal 5 MB).' }); e.target.value = ''; return; }
                      this.imageUrl = URL.createObjectURL(f);
                      this.removeImage = false;
                  },
                  clearImage() { this.$refs.file.value = ''; this.imageUrl = null; this.removeImage = true; },
                  chosen(d) { if (d.name !== 'image_path') return; this.imageUrl = d.url; this.removeImage = false; if (this.$refs.file) this.$refs.file.value = ''; },
              }">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="space-y-6 lg:col-span-2 lg:col-start-1 lg:row-start-1">

                {{-- Isi penghargaan (dwibahasa) --}}
                <x-admin.form-section icon="trophy" title="Isi Penghargaan" description="Nama dan keterangan yang tampil di kartu dan sorotan halaman Penghargaan.">
                    <div class="flex items-center gap-1 rounded-xl bg-slate-100 p-1" role="tablist" aria-label="Bahasa isi">
                        <button type="button" role="tab" :aria-selected="(lang === 'id').toString()" @click="lang = 'id'"
                                :class="lang === 'id' ? 'bg-white text-[#1B3590] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition">Indonesia (ID)</button>
                        <button type="button" role="tab" :aria-selected="(lang === 'en').toString()" @click="lang = 'en'"
                                :class="lang === 'en' ? 'bg-white text-[#1B3590] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                class="flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition">
                            English (EN)
                        </button>
                    </div>

                    @if ($transColumns)
                        @if ($autoAvailable)
                            <x-admin.toggle name="auto_translate" label="Terjemahkan otomatis bahasa yang kosong"
                                            description="Saat disimpan, kolom ID atau EN yang kosong diisi hasil terjemahan mesin dan ditandai Otomatis (belum ditinjau). Isian yang sudah kamu tulis tidak ditimpa."
                                            :checked="true" />
                        @else
                            <p class="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-inset ring-amber-200">
                                Terjemahan otomatis belum aktif (penyedia belum diatur di <code>.env</code>). Tab EN yang kosong akan tampil sebagai teks Indonesia.
                            </p>
                        @endif
                    @endif

                    <div x-show="lang === 'id'" class="space-y-5" role="tabpanel">
                        <x-admin.input name="title[id]" label="Nama penghargaan" required x-model="title" :value="$val('title', 'id')" />
                        <x-admin.textarea name="description[id]" label="Keterangan" rows="4" :value="$val('description', 'id')"
                                          hint="Alasan penghargaan, misalnya produk atau program yang dinilai. Teks biasa, maksimal 600 karakter." />
                    </div>

                    <div x-show="lang === 'en'" x-cloak class="space-y-5" role="tabpanel">
                        <x-admin.input name="title[en]" label="Award name" :value="$val('title', 'en')" />
                        <x-admin.textarea name="description[en]" label="Description" rows="4" :value="$val('description', 'en')"
                                          hint="Boleh dikosongkan. Bila kosong, pengunjung EN melihat teks Indonesia." />
                    </div>
                </x-admin.form-section>

                {{-- Tahun --}}
                <x-admin.form-section icon="clock" title="Tahun" description="Tahun penghargaan diterima. Daftar di situs diurutkan dari tahun terbaru.">
                    <div class="max-w-[10rem]">
                        <x-admin.input name="year" label="Tahun" type="number" required min="1990" max="{{ now()->year + 1 }}" x-model="year" :value="$editing ? $award->year : now()->year" />
                    </div>
                </x-admin.form-section>

                {{-- Foto / piagam --}}
                <x-admin.form-section icon="photo" title="Foto atau Piagam" description="Tampil di kartu dan sorotan. Rasio 4:3 paling pas. JPG, PNG, atau WebP, maksimal 5 MB; otomatis dikecilkan dan diubah ke WebP.">
                    <input type="hidden" name="remove_image" :value="removeImage ? 1 : 0">

                    <div class="grid gap-5 sm:grid-cols-[14rem_1fr]">
                        <div>
                            <div class="relative aspect-[4/3] overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200">
                                <template x-if="imageUrl"><img :src="imageUrl" alt="Pratinjau foto penghargaan" class="h-full w-full object-cover"></template>
                                <div x-show="!imageUrl" class="flex h-full flex-col items-center justify-center gap-1 text-slate-400">
                                    <x-admin.icon name="photo" class="h-7 w-7" />
                                    <span class="text-xs">Belum ada gambar</span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label for="image" class="block text-sm font-medium text-slate-700">Pilih gambar</label>
                                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" x-ref="file" @change="pick($event)"
                                       class="mt-1.5 block w-full text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-blue-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-[#1B3590] hover:file:bg-blue-100">
                                @error('image')<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                            </div>
                            <button type="button" x-show="imageUrl" @click="clearImage()" class="text-sm font-semibold text-[#E5352E] hover:underline">Hapus gambar</button>
                            <x-admin.media-picker name="image_path" label="" :compact="true" />
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="image_alt[id]" label="Teks alternatif (ID)" :value="$val('image_alt', 'id')" hint="Deskripsi singkat gambar untuk pembaca layar. Kosong = memakai nama penghargaan." />
                        <x-admin.input name="image_alt[en]" label="Alt text (EN)" :value="$val('image_alt', 'en')" />
                    </div>
                </x-admin.form-section>

                {{-- Publikasi --}}
                <x-admin.form-section icon="eye" title="Tampilan di Situs" description="Atur apakah penghargaan tampil, dan mana yang menjadi sorotan besar.">
                    <div class="space-y-4 rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                        <x-admin.toggle name="is_visible" label="Tampilkan di situs"
                                        description="Bila dimatikan, penghargaan disimpan tetapi tidak tampil di halaman Penghargaan maupun beranda."
                                        :checked="$editing ? $award->is_visible : true" />
                        <x-admin.toggle name="is_featured" label="Penghargaan terbaru (sorotan besar)"
                                        description="Hanya satu yang bisa menjadi sorotan; menyalakan ini melepas sorotan penghargaan lain. Bila tidak ada, situs memakai penghargaan pertama dalam urutan."
                                        :checked="$editing ? $award->is_featured : false" />
                    </div>

                    <div class="max-w-xs">
                        <x-admin.select name="translation_status" label="Status terjemahan" required
                                        :options="\App\Models\Award::TRANSLATION" :value="$currentTrans" />
                    </div>
                </x-admin.form-section>
            </div>

            {{-- Panel samping: pratinjau + aksi --}}
            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-span-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="relative aspect-[4/3] bg-gradient-to-br from-[#1B3590] via-[#2644B0] to-[#4A6CF0]">
                        <template x-if="imageUrl"><img :src="imageUrl" alt="" class="h-full w-full object-cover"></template>
                        <div x-show="!imageUrl" class="pointer-events-none absolute inset-0 flex items-center justify-center text-white/50" aria-hidden="true">
                            <x-admin.icon name="trophy" class="h-10 w-10" />
                        </div>
                        <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-[#1B3590] shadow-sm" x-text="year || ''"></span>
                    </div>
                    <div class="px-6 pb-6 pt-5">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                  :class="visible ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-700 ring-slate-200'"
                                  x-text="visible ? 'Tampil' : 'Disembunyikan'"></span>
                            @if ($editing && $award->is_dummy)<x-admin.badge tone="dummy">Dummy</x-admin.badge>@endif
                        </div>
                        <p class="mt-3 line-clamp-3 text-base font-bold leading-snug text-slate-900" x-text="title.trim() || 'Nama penghargaan'"></p>

                        @if ($editing)
                            <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-sm">
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Dibuat oleh</dt>
                                    <dd class="text-right font-medium text-slate-700">{{ $award->author?->name ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Dibuat</dt>
                                    <dd class="font-medium text-slate-700">{{ $award->created_at?->translatedFormat('j M Y') ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah</dt>
                                    <dd class="font-medium text-slate-700">{{ $award->updated_at?->translatedFormat('j M Y, H:i') ?? '-' }}</dd></div>
                            </dl>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">{{ $editing ? 'Simpan Perubahan' : 'Simpan Penghargaan' }}</x-admin.button>
                        @if ($editing && $award->is_visible)
                            <x-admin.button variant="secondary" class="w-full" icon="external" target="_blank" rel="noopener"
                                            :href="lroute('awards', [], 'id')">Lihat Di Situs</x-admin.button>
                        @endif
                        <x-admin.button variant="ghost" class="w-full" :href="route('admin.awards.index')">Batal</x-admin.button>
                    </div>
                </section>

                @if ($editing && $canDelete)
                    <button type="button" @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('admin.awards.destroy', $award) }}' })"
                            class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#E5352E] hover:bg-red-50">
                        <x-admin.icon name="trash" class="h-4 w-4" /> Hapus Penghargaan Ini
                    </button>
                @endif
            </aside>
        </form>
    </div>

    @if ($editing && $canDelete)
        <x-admin.confirm name="hapus" title="Hapus Penghargaan?" method="DELETE" button="Ya, Hapus"
                         message="Penghargaan dihapus permanen dari situs dan admin. Bila hanya ingin menyembunyikan, matikan saklar Tampilkan di situs." />
    @endif
@endsection
