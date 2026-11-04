@extends('layouts.admin')

@php
    $editing = (bool) $page;
    $revisions = $revisions ?? collect();

    $val = fn (string $field, string $lang) => $editing ? ($page->{$field}[$lang] ?? '') : '';

    $published = (bool) old('is_published', $editing ? $page->is_published : false);
    $inFooter  = (bool) old('show_in_footer', $editing ? $page->show_in_footer : false);

    // Buka tab EN otomatis bila galat hanya ada di kolom EN.
    $enErrors  = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.en'));
    $idErrors  = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.id'));
    $startLang = $enErrors && ! $idErrors ? 'en' : 'id';

    $slugHint = $editing
        ? 'Mengubah slug mengubah alamat halaman; tautan lama otomatis dialihkan (301) ke alamat baru.'
        : 'Kosongkan agar dibuat otomatis dari judul. Hanya huruf kecil, angka, dan tanda hubung.';
@endphp

@section('title', $editing ? 'Ubah Halaman Kustom' : 'Tambah Halaman Kustom')

@section('content')
    <x-admin.page-header :title="$editing ? 'Ubah Halaman Kustom' : 'Tambah Halaman Kustom'"
                         description="Isi bahasa Indonesia wajib. Bila isi EN dikosongkan, pengunjung EN melihat teks Indonesia."
                         :breadcrumbs="[['label' => 'Halaman Kustom', 'url' => route('admin.custompages.index')], ['label' => $editing ? 'Ubah' : 'Tambah']]" />

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate class="contents"
              action="{{ $editing ? route('admin.custompages.update', $page) : route('admin.custompages.store') }}"
              x-data="{
                  lang: @js($startLang),
                  title: @js(old('title.id', $val('title', 'id'))),
                  titleEn: @js(old('title.en', $val('title', 'en'))),
                  slug: @js(old('slug', $editing ? $page->slug : '')),
                  published: @js($published),
                  footer: @js($inFooter),
              }">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="space-y-6 lg:col-span-2 lg:col-start-1 lg:row-start-1">

                {{-- Isi halaman (dwibahasa) --}}
                <x-admin.form-section icon="document" title="Isi Halaman" description="Judul dan isi per bahasa.">
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

                    <div x-show="lang === 'id'" class="space-y-5" role="tabpanel">
                        <x-admin.input name="title[id]" label="Judul" x-model="title" :value="$val('title', 'id')" required />
                        <x-admin.editor name="body[id]" label="Isi halaman" :value="$val('body', 'id')" :upload="route('admin.news.upload')"
                                        hint="Subjudul, daftar, tebal, miring, kutipan, tautan, dan gambar. Tempelan dari Word atau web otomatis jadi teks polos." />
                    </div>

                    <div x-show="lang === 'en'" x-cloak class="space-y-5" role="tabpanel">
                        <x-admin.input name="title[en]" label="Title" x-model="titleEn" :value="$val('title', 'en')" />
                        <x-admin.editor name="body[en]" label="Body" :value="$val('body', 'en')" :upload="route('admin.news.upload')"
                                        hint="Boleh dikosongkan; pengunjung EN akan melihat teks Indonesia." />
                    </div>
                </x-admin.form-section>

                {{-- Publikasi --}}
                <x-admin.form-section icon="clock" title="Publikasi" description="Status, alamat, dan tampilan di footer.">
                    <input type="hidden" name="is_published" :value="published ? 1 : 0">

                    <fieldset>
                        <legend class="sr-only">Status</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ([[false, 'Draf', 'Belum tampil di situs. Cocok untuk halaman yang masih dikerjakan.', 'document'], [true, 'Terbit', 'Langsung bisa dibuka pengunjung di alamat halaman ini.', 'check-circle']] as [$value, $label, $info, $icon])
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="status_ui" class="peer sr-only" @click="published = {{ $value ? 'true' : 'false' }}" @checked($published === $value)>
                                    <span class="flex h-full flex-col rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200 transition hover:bg-white hover:shadow-md peer-checked:bg-blue-50 peer-checked:ring-2 peer-checked:ring-[#1B3590] peer-checked:[&_.st-icon]:bg-[#1B3590] peer-checked:[&_.st-icon]:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-[#1B3590]">
                                        <span class="st-icon flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-500 shadow-sm transition">
                                            <x-admin.icon :name="$icon" class="h-5 w-5" />
                                        </span>
                                        <span class="mt-3 text-sm font-bold text-slate-900">{{ $label }}</span>
                                        <span class="mt-1 text-xs leading-relaxed text-slate-500">{{ $info }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('is_published')<p class="mt-2 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                    </fieldset>

                    <x-admin.input name="slug" label="Slug (alamat halaman)" x-model="slug" :value="$editing ? $page->slug : null" autocomplete="off" :hint="$slugHint" />

                    <div class="space-y-4 rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                        <input type="hidden" name="show_in_footer" :value="footer ? 1 : 0">
                        <div class="flex items-start gap-3">
                            <button type="button" role="switch" :aria-checked="footer.toString()" aria-labelledby="footer-label" @click="footer = ! footer"
                                    :class="footer ? 'bg-[#1B3590]' : 'bg-slate-300'"
                                    class="relative mt-0.5 inline-flex h-6 w-11 shrink-0 rounded-full transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1B3590] focus-visible:ring-offset-2">
                                <span :class="footer ? 'translate-x-5' : 'translate-x-0.5'" class="mt-0.5 inline-block h-5 w-5 rounded-full bg-white shadow transition"></span>
                            </button>
                            <div>
                                <span id="footer-label" class="block text-sm font-medium text-slate-700">Tampilkan di footer</span>
                                <span class="block text-xs text-slate-500">Tautan muncul di kolom Perusahaan pada footer situs. Hanya berlaku bila statusnya Terbit.</span>
                            </div>
                        </div>
                        <div x-show="footer" x-cloak class="max-w-[12rem]">
                            <x-admin.input name="sort_order" type="number" label="Urutan di footer" :value="$editing ? $page->sort_order : 0" hint="Angka kecil tampil lebih dulu (0-999)." />
                        </div>
                    </div>
                </x-admin.form-section>

                {{-- SEO --}}
                <x-admin.form-section icon="search" title="SEO" description="Opsional. Bila kosong, situs memakai judul halaman.">
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
                    <div class="relative h-24 bg-gradient-to-br from-[#1B3590] via-[#2644B0] to-[#4A6CF0]">
                        <div class="pointer-events-none absolute inset-0 flex items-center justify-center text-white/50" aria-hidden="true">
                            <x-admin.icon name="document" class="h-9 w-9" />
                        </div>
                    </div>
                    <div class="px-6 pb-6 pt-5">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                  :class="published ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-700 ring-slate-200'"
                                  x-text="published ? 'Terbit' : 'Draf'"></span>
                            <span x-show="footer" x-cloak class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-[#1B3590] ring-1 ring-inset ring-blue-200">Di footer</span>
                        </div>
                        <p class="mt-3 line-clamp-3 text-base font-bold leading-snug text-slate-900" x-text="title.trim() || 'Judul halaman'"></p>
                        <p class="mt-1 truncate text-xs text-slate-500" x-text="'/id/' + (slug.trim() || 'alamat-halaman')"></p>

                        @if ($editing)
                            <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-sm">
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Dibuat oleh</dt>
                                    <dd class="text-right font-medium text-slate-700">{{ $page->author?->name ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Dibuat</dt>
                                    <dd class="font-medium text-slate-700">{{ $page->created_at?->translatedFormat('j M Y') ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah</dt>
                                    <dd class="text-right font-medium text-slate-700">{{ $page->updated_at?->translatedFormat('j M Y, H:i') ?? '-' }}@if ($page->editor) oleh {{ $page->editor->name }}@endif</dd></div>
                            </dl>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">{{ $editing ? 'Simpan Perubahan' : 'Simpan Halaman' }}</x-admin.button>
                        @if ($editing && $page->is_published)
                            <x-admin.button variant="secondary" class="w-full" icon="external" target="_blank" rel="noopener"
                                            :href="lroute('custom', ['slug' => $page->slug], 'id')">Lihat Di Situs</x-admin.button>
                        @endif
                        <x-admin.button variant="ghost" class="w-full" :href="route('admin.custompages.index')">Batal</x-admin.button>
                    </div>
                </section>

                @if ($editing)
                    <button type="button" @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('admin.custompages.destroy', $page) }}' })"
                            class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#E5352E] hover:bg-red-50">
                        <x-admin.icon name="trash" class="h-4 w-4" /> Hapus Halaman Ini
                    </button>
                @endif
            </aside>
        </form>

        {{-- Riwayat revisi: form kembalikan ada di luar form utama --}}
        @if ($editing)
            <div class="lg:col-span-2 lg:col-start-1 lg:row-start-2">
                <x-admin.form-section icon="clock" title="Riwayat Revisi" :description="'Salinan sebelum setiap perubahan; ' . \App\Models\CustomPageRevision::KEEP . ' terakhir disimpan. Slug, status, dan pilihan footer tidak ikut dikembalikan.'">
                    @if ($revisions->isEmpty())
                        <p class="text-sm text-slate-500">Belum ada revisi. Riwayat muncul setelah halaman ini disimpan ulang.</p>
                    @else
                        <ul class="divide-y divide-slate-100 rounded-xl ring-1 ring-slate-200">
                            @foreach ($revisions as $rev)
                                <li class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-800">{{ \App\Models\News::pick($rev->snapshot['title'] ?? '', 'id') ?: '(tanpa judul)' }}</p>
                                        <p class="text-xs text-slate-500">{{ $rev->created_at->translatedFormat('j M Y, H:i') }} &middot; {{ $rev->user_name ?: 'Sistem' }}</p>
                                    </div>
                                    <button type="button"
                                            @click="$dispatch('open-modal', { name: 'kembalikan', action: '{{ route('admin.custompages.restore', [$page, $rev]) }}' })"
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
                         message="Judul, isi, dan SEO halaman diganti dengan versi yang dipilih. Versi saat ini tetap tersimpan di Riwayat Revisi, jadi bisa dikembalikan lagi." />
        <x-admin.confirm name="hapus" title="Hapus Halaman?" method="DELETE" button="Ya, Hapus"
                         message="Halaman dan seluruh riwayat revisinya dihapus permanen, dan alamatnya tidak bisa dibuka lagi. Bila hanya ingin menyembunyikan dari situs, ubah statusnya menjadi Draf." />
    @endif
@endsection
