@extends('layouts.admin')

@section('title', 'SEO Halaman')

@php
    $enErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.en'));
    $idErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.id'));
    $startLang = $enErrors && ! $idErrors ? 'en' : 'id';

    $field = 'block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]';
    $label = 'block text-sm font-medium text-slate-700';

    $filled = collect($pages)->filter(fn ($p) => trim($p['title']['id'].$p['title']['en'].$p['description']['id'].$p['description']['en'].$p['image']) !== '')->count();
@endphp

@section('content')
    <x-admin.page-header title="SEO Halaman"
                         description="Judul, deskripsi, dan gambar share (WhatsApp, Facebook, LinkedIn, X) untuk halaman utama situs. Yang dikosongkan memakai bawaan halaman."
                         :breadcrumbs="[['label' => 'Situs'], ['label' => 'SEO Halaman']]" />

    @unless ($available)
        <p class="mb-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-inset ring-amber-200">
            Tabel penyimpanan belum ada. Jalankan <code>php artisan migrate</code> dulu; sebelum itu perubahan belum bisa disimpan.
        </p>
    @endunless

    @if ($errors->any())
        <p class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-inset ring-red-200">
            Ada isian yang belum benar. Periksa pesan di bawah, termasuk di tab bahasa lain.
        </p>
    @endif

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate action="{{ route('admin.seo.update') }}" class="contents" x-data="{ lang: @js($startLang) }">
            @csrf @method('PUT')

            <div class="space-y-6 lg:col-span-2 lg:col-start-1 lg:row-start-1">

                <div class="flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm ring-1 ring-slate-900/5" role="tablist" aria-label="Bahasa isi">
                    <button type="button" role="tab" :aria-selected="(lang === 'id').toString()" @click="lang = 'id'"
                            :class="lang === 'id' ? 'bg-blue-50 text-[#1B3590] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                            class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition">Indonesia (ID)</button>
                    <button type="button" role="tab" :aria-selected="(lang === 'en').toString()" @click="lang = 'en'"
                            :class="lang === 'en' ? 'bg-blue-50 text-[#1B3590] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                            class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition">English (EN)</button>
                </div>
                <p class="-mt-3 px-1 text-xs text-slate-500">Tab bahasa berlaku untuk judul dan deskripsi. Bahasa Inggris boleh kosong; pengunjung EN akan melihat teks Indonesia.</p>

                {{-- Gambar share bawaan --}}
                <x-admin.form-section icon="photo" title="Gambar Share Bawaan" description="Dipakai bila sebuah halaman tidak punya gambar sendiri. Ukuran ideal 1200 x 630 px (rasio sekitar 1,9 banding 1).">
                    <x-admin.media-picker name="default_image" :value="$defaultImage" label="Gambar share seluruh situs" ratio="aspect-[1200/630]"
                                          hint="Berita dan Proyek otomatis memakai gambar utamanya sendiri bila ada." />
                    @error('default_image')<p class="text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                </x-admin.form-section>

                {{-- Per halaman --}}
                <x-admin.form-section icon="search" title="Halaman" description="Buka sebuah halaman untuk mengisi judul, deskripsi, dan gambar share-nya. Judul otomatis diberi akhiran | {{ config('site.name') }}.">
                    <div class="space-y-3">
                        @foreach ($pages as $key => $p)
                            @php
                                $f = $p['field'];
                                $has = trim($p['title']['id'].$p['title']['en'].$p['description']['id'].$p['description']['en'].$p['image']) !== ''
                                    || $errors->has('pages.'.$f.'.*');
                            @endphp
                            <details class="group rounded-2xl bg-slate-50 ring-1 ring-inset ring-slate-200 open:bg-white open:shadow-sm" @if ($has) open @endif>
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4">
                                    <span class="flex items-center gap-3">
                                        <span class="font-semibold text-slate-900">{{ $p['label'] }}</span>
                                        @if ($has && ! $errors->has('pages.'.$f.'.*'))
                                            <x-admin.badge tone="success">Diatur</x-admin.badge>
                                        @elseif ($errors->has('pages.'.$f.'.*'))
                                            <x-admin.badge tone="warning">Periksa</x-admin.badge>
                                        @else
                                            <span class="text-xs text-slate-400">Bawaan</span>
                                        @endif
                                    </span>
                                    <span class="flex items-center gap-3">
                                        @if ($p['url'])
                                            <a href="{{ $p['url'] }}" target="_blank" rel="noopener" @click.stop class="text-xs font-semibold text-[#1B3590] hover:underline">Lihat halaman</a>
                                        @endif
                                        <x-admin.icon name="chevron-down" class="h-4 w-4 text-slate-400 transition group-open:rotate-180" />
                                    </span>
                                </summary>

                                <div class="space-y-5 border-t border-slate-100 px-5 py-5">
                                    @foreach (['id', 'en'] as $l)
                                        <div x-show="lang === '{{ $l }}'" @if ($l === 'en') x-cloak @endif class="space-y-5">
                                            <div x-data="{ v: @js((string) old('pages.'.$f.'.title.'.$l, $p['title'][$l])) }">
                                                <div class="flex items-end justify-between gap-3">
                                                    <label for="t-{{ $f }}-{{ $l }}" class="{{ $label }}">Judul {{ $l === 'id' ? '(ID)' : '(EN)' }}</label>
                                                    <span class="text-xs tabular-nums" :class="v.length > 60 ? 'text-amber-600' : 'text-slate-400'" x-text="v.length + '/60'"></span>
                                                </div>
                                                <input id="t-{{ $f }}-{{ $l }}" name="pages[{{ $f }}][title][{{ $l }}]" x-model="v" maxlength="{{ $titleMax }}"
                                                       placeholder="Kosong = judul bawaan halaman" class="mt-1.5 {{ $field }}">
                                                @error('pages.'.$f.'.title.'.$l)<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                                            </div>
                                            <div x-data="{ v: @js((string) old('pages.'.$f.'.description.'.$l, $p['description'][$l])) }">
                                                <div class="flex items-end justify-between gap-3">
                                                    <label for="d-{{ $f }}-{{ $l }}" class="{{ $label }}">Deskripsi {{ $l === 'id' ? '(ID)' : '(EN)' }}</label>
                                                    <span class="text-xs tabular-nums" :class="v.length > 160 ? 'text-amber-600' : 'text-slate-400'" x-text="v.length + '/160'"></span>
                                                </div>
                                                <textarea id="d-{{ $f }}-{{ $l }}" name="pages[{{ $f }}][description][{{ $l }}]" x-model="v" rows="3" maxlength="{{ $descMax }}"
                                                          placeholder="Kosong = deskripsi bawaan halaman" class="mt-1.5 {{ $field }}"></textarea>
                                                @error('pages.'.$f.'.description.'.$l)<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                                            </div>
                                        </div>
                                    @endforeach

                                    <x-admin.media-picker :name="'pages['.$f.'][image]'" :value="$p['image']" label="Gambar share halaman ini (opsional)" ratio="aspect-[1200/630]"
                                                          hint="Kosong = gambar share bawaan situs." />
                                </div>
                            </details>
                        @endforeach
                    </div>
                </x-admin.form-section>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="px-6 pb-5 pt-6">
                        <p class="text-sm font-bold text-slate-900">{{ $filled }} dari {{ count($pages) }} halaman diatur</p>
                        <ul class="mt-3 space-y-2 text-xs leading-relaxed text-slate-500">
                            <li>Judul ideal sampai 60 karakter, deskripsi sampai 160 karakter; lebih dari itu terpotong di hasil pencarian.</li>
                            <li>Setiap halaman sebaiknya punya judul dan deskripsi yang berbeda.</li>
                            <li>Gambar share tampil saat tautan dibagikan; cache WhatsApp dan Facebook bisa butuh beberapa jam untuk menyegarkan.</li>
                        </ul>
                        @if ($row?->updated_at)
                            <p class="mt-4 border-t border-slate-100 pt-3 text-xs text-slate-500">Terakhir disimpan {{ $row->updated_at->translatedFormat('j M Y, H:i') }}@if ($row->editor) oleh {{ $row->editor->name }}@endif</p>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">Simpan</x-admin.button>
                    </div>
                </section>

                @if ($row)
                    <button type="button" @click="$dispatch('open-modal', { name: 'reset', action: '{{ route('admin.seo.reset') }}' })"
                            class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-500 hover:bg-slate-100 hover:text-slate-800">
                        Kosongkan Semua Isian
                    </button>
                @endif
            </aside>
        </form>
    </div>

    @if ($row)
        <x-admin.confirm name="reset" title="Kosongkan SEO Halaman?" method="DELETE" button="Ya, Kosongkan" tone="primary"
                         message="Semua judul, deskripsi, dan gambar share di sini dihapus; setiap halaman kembali memakai bawaannya. Berita, Proyek, dan Halaman Kustom tidak terpengaruh." />
    @endif
@endsection
