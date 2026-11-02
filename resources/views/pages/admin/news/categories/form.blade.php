@extends('layouts.admin')

@php
    $editing = (bool) $category;
    $nameId = old('name.id', $editing ? ($category->name['id'] ?? '') : '');
    $nameEn = old('name.en', $editing ? ($category->name['en'] ?? '') : '');
    $slugVal = old('slug', $editing ? $category->slug : '');
@endphp

@section('title', $editing ? 'Ubah Kategori' : 'Tambah Kategori')

@section('content')
    <x-admin.page-header :title="$editing ? 'Ubah Kategori' : 'Tambah Kategori'"
                         description="Nama Inggris boleh kosong; pengunjung EN akan melihat nama Indonesia."
                         :breadcrumbs="[['label' => 'Berita', 'url' => route('admin.news.index')], ['label' => 'Kategori', 'url' => route('admin.newscat.index')], ['label' => $editing ? 'Ubah' : 'Tambah']]" />

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate class="contents"
              action="{{ $editing ? route('admin.newscat.update', $category) : route('admin.newscat.store') }}"
              x-data="{ name: @js($nameId), nameEn: @js($nameEn), slug: @js($slugVal) }">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="space-y-6 lg:col-span-2 lg:col-start-1 lg:row-start-1">
                <x-admin.form-section icon="tag" title="Informasi Kategori" description="Nama tampil di chip kategori halaman Berita.">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="name[id]" label="Nama (ID)" required x-model="name" :value="$nameId" />
                        <x-admin.input name="name[en]" label="Name (EN)" x-model="nameEn" :value="$nameEn" />
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="slug" label="Slug" x-model="slug" :value="$slugVal" autocomplete="off"
                                       :hint="$editing ? 'Dipakai di alamat /berita/kategori/slug. Mengubahnya mengubah alamat halaman kategori.' : 'Kosongkan agar dibuat dari nama. Huruf kecil, angka, dan tanda hubung.'" />
                        <x-admin.input name="sort_order" type="number" min="0" max="999" label="Urutan" :value="$editing ? $category->sort_order : 0"
                                       hint="Angka kecil tampil lebih dulu." />
                    </div>
                </x-admin.form-section>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="relative h-24 bg-gradient-to-br from-[#1B3590] via-[#2644B0] to-[#4A6CF0]">
                        <div class="pointer-events-none absolute inset-0 opacity-[0.12]" aria-hidden="true"
                             style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;"></div>
                    </div>
                    <div class="px-6 pb-6 text-center">
                        <span class="relative -mt-8 mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-[#1B3590] shadow-xl ring-4 ring-white">
                            <x-admin.icon name="tag" class="h-7 w-7" />
                        </span>
                        <p class="mt-3 truncate text-base font-bold text-slate-900" x-text="name.trim() || 'Nama kategori'"></p>
                        <p class="truncate text-sm text-slate-500" x-text="nameEn.trim() ? 'EN: ' + nameEn.trim() : 'EN: sama dengan ID'"></p>
                        <p class="mt-1 truncate text-xs text-slate-400" x-text="'/berita/kategori/' + (slug.trim() || '...')"></p>
                        @if ($editing)
                            <p class="mt-4 border-t border-slate-100 pt-4 text-sm text-slate-600">Dipakai <strong>{{ $category->news_count }}</strong> berita</p>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">{{ $editing ? 'Simpan Perubahan' : 'Buat Kategori' }}</x-admin.button>
                        <x-admin.button variant="ghost" class="w-full" :href="route('admin.newscat.index')">Batal</x-admin.button>
                    </div>
                </section>
            </aside>
        </form>
    </div>
@endsection
