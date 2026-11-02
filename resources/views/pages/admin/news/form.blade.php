@extends('layouts.admin')

@php
    $editing = (bool) $news;
    $canDelete = auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_ADMIN);
    $revisions = $revisions ?? collect();

    $val = fn (string $field, string $lang) => $editing ? ($news->{$field}[$lang] ?? '') : '';

    $currentStatus = old('status', $editing ? $news->status : \App\Models\News::STATUS_DRAFT);
    $currentTrans = old('translation_status', $editing ? $news->translation_status : 'manual');
    $autoAvailable = \App\Support\Translator::enabled();
    $publishedAt = old('published_at', $editing && $news->published_at ? $news->published_at->format('Y-m-d\TH:i') : '');

    $imageUrl = $editing && $news->image && file_exists(public_path($news->image)) ? asset($news->image) : null;

    // Buka tab EN otomatis bila galat hanya ada di kolom EN.
    $enErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.en'));
    $idErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.id'));
    $startLang = $enErrors && ! $idErrors ? 'en' : 'id';

    $statusInfo = [
        \App\Models\News::STATUS_DRAFT => ['Belum tampil di situs. Cocok untuk tulisan yang masih dikerjakan.', 'document'],
        \App\Models\News::STATUS_PUBLISHED => ['Langsung tampil di situs. Tanggal tayang boleh diisi untuk berita lama.', 'check-circle'],
        \App\Models\News::STATUS_SCHEDULED => ['Tampil otomatis pada tanggal dan jam yang dipilih (tanpa perlu cron).', 'clock'],
    ];
    $slugHint = $editing
        ? 'Mengubah slug mengubah alamat berita; tautan lama berhenti bekerja (redirect otomatis menyusul di B3n).'
        : 'Kosongkan agar dibuat otomatis dari judul. Hanya huruf kecil, angka, dan tanda hubung.';
@endphp

@section('title', $editing ? 'Ubah Berita' : 'Tambah Berita')

@section('content')
    <x-admin.page-header :title="$editing ? 'Ubah Berita' : 'Tambah Berita'"
                         :description="$editing ? 'Perbarui isi, status, atau gambar berita ini. Versi sebelumnya otomatis tersimpan di Riwayat Revisi.' : 'Cukup isi salah satu bahasa (ID atau EN). Bahasa yang kosong diterjemahkan otomatis saat disimpan.'"
                         :breadcrumbs="[['label' => 'Berita', 'url' => route('admin.news.index')], ['label' => $editing ? 'Ubah' : 'Tambah']]" />

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate enctype="multipart/form-data" class="contents"
              action="{{ $editing ? route('admin.news.update', $news) : route('admin.news.store') }}"
              x-data="{
                  lang: @js($startLang),
                  title: @js(old('title.id', $val('title', 'id'))),
                  titleEn: @js(old('title.en', $val('title', 'en'))),
                  status: @js($currentStatus),
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
              }">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="space-y-6 lg:col-span-2 lg:col-start-1 lg:row-start-1">

                {{-- Isi berita (dwibahasa) --}}
                <x-admin.form-section icon="newspaper" title="Isi Berita" description="Isi salah satu bahasa saja; sisanya diterjemahkan otomatis.">
                    <div class="flex items-center gap-1 rounded-xl bg-slate-100 p-1" role="tablist" aria-label="Bahasa isi">
                        <button type="button" role="tab" :aria-selected="(lang === 'id').toString()" @click="lang = 'id'"
                                :class="lang === 'id' ? 'bg-white text-[#1B3590] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition">Indonesia (ID)</button>
                        <button type="button" role="tab" :aria-selected="(lang === 'en').toString()" @click="lang = 'en'"
                                :class="lang === 'en' ? 'bg-white text-[#1B3590] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                class="flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition">
                            English (EN)
                            <span class="h-2 w-2 rounded-full" :class="titleEn.trim() ? 'bg-emerald-500' : 'bg-slate-300'" aria-hidden="true"></span>
                        </button>
                    </div>

                    @if ($autoAvailable)
                        <x-admin.toggle name="auto_translate" label="Terjemahkan otomatis bahasa yang kosong"
                                        description="Saat disimpan, kolom ID atau EN yang kosong diisi hasil terjemahan mesin dan ditandai Otomatis (belum ditinjau). Isian yang sudah kamu tulis tidak ditimpa."
                                        :checked="true" />
                    @else
                        <p class="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-inset ring-amber-200">
                            Terjemahan otomatis belum aktif (penyedia belum diatur di <code>.env</code>). Tab EN yang kosong akan tampil sebagai teks Indonesia.
                        </p>
                    @endif

                    <div x-show="lang === 'id'" class="space-y-5" role="tabpanel">
                        <x-admin.input name="title[id]" label="Judul" x-model="title" :value="$val('title', 'id')" />
                        <x-admin.textarea name="excerpt[id]" label="Ringkasan" rows="3" :value="$val('excerpt', 'id')"
                                          hint="Tampil di kartu berita dan hasil pencarian. Maksimal 400 karakter." />
                        <x-admin.editor name="body[id]" label="Isi berita" :value="$val('body', 'id')" :upload="route('admin.news.upload')"
                                        hint="Tempelan dari Word atau web otomatis jadi teks polos. Gambar maksimal 5 MB." />
                    </div>

                    <div x-show="lang === 'en'" x-cloak class="space-y-5" role="tabpanel">
                        <x-admin.input name="title[en]" label="Title" x-model="titleEn" :value="$val('title', 'en')" />
                        <x-admin.textarea name="excerpt[en]" label="Excerpt" rows="3" :value="$val('excerpt', 'en')" />
                        <x-admin.editor name="body[en]" label="Body" :value="$val('body', 'en')" :upload="route('admin.news.upload')"
                                        hint="Boleh dikosongkan bila terjemahan otomatis aktif. Bila dimatikan, pengunjung EN melihat teks Indonesia." />
                    </div>
                </x-admin.form-section>

                {{-- Gambar utama --}}
                <x-admin.form-section icon="photo" title="Gambar Utama" description="Tampil di kartu dan bagian atas berita. JPG, PNG, atau WebP, maksimal 5 MB; otomatis dikecilkan dan diubah ke WebP.">
                    <input type="hidden" name="remove_image" :value="removeImage ? 1 : 0">

                    <div class="grid gap-5 sm:grid-cols-[14rem_1fr]">
                        <div>
                            <div class="relative aspect-[16/10] overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200">
                                <template x-if="imageUrl"><img :src="imageUrl" alt="Pratinjau gambar utama" class="h-full w-full object-cover"></template>
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
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="image_alt[id]" label="Teks alternatif (ID)" :value="$val('image_alt', 'id')" hint="Deskripsi singkat gambar untuk pembaca layar dan SEO." />
                        <x-admin.input name="image_alt[en]" label="Alt text (EN)" :value="$val('image_alt', 'en')" />
                    </div>
                </x-admin.form-section>

                {{-- Publikasi --}}
                <x-admin.form-section icon="clock" title="Publikasi" description="Status, kategori, dan alamat berita.">
                    <fieldset>
                        <legend class="sr-only">Status</legend>
                        <div class="grid gap-3 sm:grid-cols-3">
                            @foreach (\App\Models\News::STATUSES as $value => $label)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="status" value="{{ $value }}" class="peer sr-only" x-model="status" @checked($currentStatus === $value)>
                                    <span class="flex h-full flex-col rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200 transition hover:bg-white hover:shadow-md peer-checked:bg-blue-50 peer-checked:ring-2 peer-checked:ring-[#1B3590] peer-checked:[&_.st-icon]:bg-[#1B3590] peer-checked:[&_.st-icon]:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-[#1B3590]">
                                        <span class="st-icon flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-500 shadow-sm transition">
                                            <x-admin.icon :name="$statusInfo[$value][1]" class="h-5 w-5" />
                                        </span>
                                        <span class="mt-3 text-sm font-bold text-slate-900">{{ $label }}</span>
                                        <span class="mt-1 text-xs leading-relaxed text-slate-500">{{ $statusInfo[$value][0] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('status')<p class="mt-2 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                    </fieldset>

                    <div x-show="status !== 'draft'" x-cloak>
                        <x-admin.input name="published_at" type="datetime-local" label="Tanggal dan jam tayang" :value="$publishedAt"
                                       hint="Wajib untuk Terjadwal. Untuk Terbit, kosongkan agar tayang sekarang." />
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.select name="news_category_id" label="Kategori" required placeholder="Pilih kategori"
                                        :options="$categories->mapWithKeys(fn ($c) => [$c->id => $c->label('id')])->all()"
                                        :value="$editing ? $news->news_category_id : null" />
                        <x-admin.select name="translation_status" label="Status terjemahan" required
                                        :options="\App\Models\News::TRANSLATION" :value="$currentTrans" />
                    </div>

                    <x-admin.input name="slug" label="Slug (alamat berita)" :value="$editing ? $news->slug : null" autocomplete="off" :hint="$slugHint" />
                </x-admin.form-section>

                {{-- SEO --}}
                <x-admin.form-section icon="search" title="SEO" description="Opsional. Bila kosong, situs memakai judul dan ringkasan berita.">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="space-y-5">
                            <x-admin.input name="seo_title[id]" label="Judul SEO (ID)" :value="$val('seo_title', 'id')" hint="Maksimal 120 karakter." />
                            <x-admin.textarea name="seo_description[id]" label="Deskripsi SEO (ID)" rows="3" :value="$val('seo_description', 'id')" hint="Maksimal 300 karakter." />
                        </div>
                        <div class="space-y-5">
                            <x-admin.input name="seo_title[en]" label="SEO title (EN)" :value="$val('seo_title', 'en')" />
                            <x-admin.textarea name="seo_description[en]" label="SEO description (EN)" rows="3" :value="$val('seo_description', 'en')" />
                        </div>
                    </div>
                </x-admin.form-section>
            </div>

            {{-- Panel samping: pratinjau + aksi --}}
            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-span-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="relative aspect-[16/10] bg-gradient-to-br from-[#1B3590] via-[#2644B0] to-[#4A6CF0]">
                        <template x-if="imageUrl"><img :src="imageUrl" alt="" class="h-full w-full object-cover"></template>
                        <div x-show="!imageUrl" class="pointer-events-none absolute inset-0 flex items-center justify-center text-white/50" aria-hidden="true">
                            <x-admin.icon name="newspaper" class="h-10 w-10" />
                        </div>
                    </div>
                    <div class="px-6 pb-6 pt-5">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                  :class="{ 'bg-slate-100 text-slate-700 ring-slate-200': status === 'draft', 'bg-emerald-50 text-emerald-700 ring-emerald-200': status === 'published', 'bg-amber-50 text-amber-800 ring-amber-200': status === 'scheduled' }"
                                  x-text="{ draft: 'Draf', published: 'Terbit', scheduled: 'Terjadwal' }[status]"></span>
                            @if ($editing && $news->is_dummy)<x-admin.badge tone="dummy">Dummy</x-admin.badge>@endif
                        </div>
                        <p class="mt-3 line-clamp-3 text-base font-bold leading-snug text-slate-900" x-text="title.trim() || 'Judul berita'"></p>

                        @if ($editing)
                            <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-sm">
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Dibuat oleh</dt>
                                    <dd class="text-right font-medium text-slate-700">{{ $news->author?->name ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Dibuat</dt>
                                    <dd class="font-medium text-slate-700">{{ $news->created_at?->translatedFormat('j M Y') ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah</dt>
                                    <dd class="font-medium text-slate-700">{{ $news->updated_at?->translatedFormat('j M Y, H:i') ?? '-' }}</dd></div>
                            </dl>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">{{ $editing ? 'Simpan Perubahan' : 'Simpan Berita' }}</x-admin.button>
                        @if ($editing && $news->isVisible())
                            <x-admin.button variant="secondary" class="w-full" icon="external" target="_blank" rel="noopener"
                                            :href="lroute('news.show', ['slug' => $news->slug], 'id')">Lihat Di Situs</x-admin.button>
                        @endif
                        <x-admin.button variant="ghost" class="w-full" :href="route('admin.news.index')">Batal</x-admin.button>
                    </div>
                </section>

                @if ($editing && $canDelete)
                    <button type="button" @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('admin.news.destroy', $news) }}' })"
                            class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#E5352E] hover:bg-red-50">
                        <x-admin.icon name="trash" class="h-4 w-4" /> Hapus Berita Ini
                    </button>
                @endif
            </aside>
        </form>

        {{-- Riwayat revisi: form kembalikan ada di luar form utama --}}
        @if ($editing)
            <div class="lg:col-span-2 lg:col-start-1 lg:row-start-2">
                <x-admin.form-section icon="clock" title="Riwayat Revisi" :description="'Salinan sebelum setiap perubahan; ' . \App\Models\NewsRevision::KEEP . ' terakhir disimpan. Status dan slug tidak ikut dikembalikan.'">
                    @if ($revisions->isEmpty())
                        <p class="text-sm text-slate-500">Belum ada revisi. Riwayat muncul setelah berita ini disimpan ulang.</p>
                    @else
                        <ul class="divide-y divide-slate-100 rounded-xl ring-1 ring-slate-200">
                            @foreach ($revisions as $rev)
                                <li class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-800">{{ \App\Models\News::pick($rev->snapshot['title'] ?? '', 'id') ?: '(tanpa judul)' }}</p>
                                        <p class="text-xs text-slate-500">{{ $rev->created_at->translatedFormat('j M Y, H:i') }} &middot; {{ $rev->user_name ?: 'Sistem' }}</p>
                                    </div>
                                    <button type="button"
                                            @click="$dispatch('open-modal', { name: 'kembalikan', action: '{{ route('admin.news.restore', [$news, $rev]) }}' })"
                                            class="rounded-xl bg-white px-3.5 py-2 text-xs font-semibold capitalize text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-slate-50">Kembalikan</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-admin.form-section>
            </div>
        @endif
    </div>

    @if ($editing)
        <x-admin.confirm name="kembalikan" title="Kembalikan ke Versi Ini?" method="POST" button="Ya, Kembalikan" tone="primary"
                         message="Isi berita diganti dengan versi yang dipilih. Versi saat ini tetap tersimpan di Riwayat Revisi, jadi bisa dikembalikan lagi." />
        @if ($canDelete)
            <x-admin.confirm name="hapus" title="Hapus Berita?" method="DELETE" button="Ya, Hapus"
                             message="Berita dan seluruh riwayat revisinya dihapus permanen. Bila hanya ingin menyembunyikan dari situs, ubah statusnya menjadi Draf." />
        @endif
    @endif
@endsection