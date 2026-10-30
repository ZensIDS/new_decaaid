@extends('layouts.app')

@section('title', 'Style Guide | '.config('site.name'))

{{-- HALAMAN INTERNAL SEMENTARA (A1). Hanya aktif di environment local. Dihapus sebelum staging. --}}
@php
    $brand = ['bg-brand-50' => '50', 'bg-brand-100' => '100', 'bg-brand-200' => '200', 'bg-brand-300' => '300', 'bg-brand-400' => '400', 'bg-brand-500' => '500', 'bg-brand-600' => '600', 'bg-brand-700' => '700', 'bg-brand-800' => '800', 'bg-brand-900' => '900', 'bg-brand-950' => '950'];
    $accent = ['bg-accent-50' => '50', 'bg-accent-100' => '100', 'bg-accent-300' => '300', 'bg-accent-500' => '500', 'bg-accent-600' => '600', 'bg-accent-700' => '700'];
    $icons = ['arrow-right', 'search', 'menu', 'close', 'chevron-down', 'chevron-left', 'chevron-right', 'check', 'mail', 'phone', 'map-pin'];
@endphp

@section('content')
    <section class="bg-brand-900 py-14 text-white">
        <div class="container">
            <x-ui.breadcrumb light :items="[['Beranda', route('id.home')], ['Style Guide', null]]" />
            <h1 class="mt-6 text-4xl text-white md:text-5xl">Style Guide</h1>
            <p class="mt-3 max-w-2xl text-brand-100">Halaman internal untuk memeriksa token dan komponen. Tidak ikut tampil di situs publik.</p>
        </div>
    </section>

    <div class="container space-y-16 py-14">

        {{-- Warna --}}
        <section>
            <h2 class="text-2xl">Warna</h2>
            <p class="mt-2 text-sm">Brand (utama = 700), Accent (aksen kecil), Ink (judul), netral slate.</p>
            <div class="mt-5 grid grid-cols-4 gap-3 sm:grid-cols-6 lg:grid-cols-11">
                @foreach ($brand as $cls => $n)
                    <div><div class="h-14 rounded-lg shadow-card {{ $cls }}"></div><p class="mt-1.5 text-xs">brand-{{ $n }}</p></div>
                @endforeach
            </div>
            <div class="mt-4 grid grid-cols-4 gap-3 sm:grid-cols-6 lg:grid-cols-11">
                @foreach ($accent as $cls => $n)
                    <div><div class="h-14 rounded-lg shadow-card {{ $cls }}"></div><p class="mt-1.5 text-xs">accent-{{ $n }}</p></div>
                @endforeach
                <div><div class="h-14 rounded-lg bg-ink shadow-card"></div><p class="mt-1.5 text-xs">ink</p></div>
            </div>
        </section>

        {{-- Tipografi --}}
        <section>
            <h2 class="text-2xl">Tipografi (Plus Jakarta Sans)</h2>
            <div class="mt-5 space-y-3">
                <p class="eyebrow">Eyebrow / label kecil</p>
                <h1 class="text-4xl md:text-6xl">Heading 1 — Solusi teknologi terpercaya</h1>
                <h2 class="text-3xl md:text-4xl">Heading 2 — Unit bisnis kami</h2>
                <h3 class="text-2xl">Heading 3 — Layanan unggulan</h3>
                <h4 class="text-xl">Heading 4 — Judul kartu</h4>
                <p class="max-w-2xl text-lg leading-relaxed">Paragraf besar (lead). DECAA.ID menghadirkan layanan teknologi dan bisnis yang terintegrasi untuk instansi dan perusahaan.</p>
                <p class="max-w-2xl">Paragraf normal. Teks isi memakai warna slate-600 agar nyaman dibaca di latar putih.</p>
                <p class="text-sm">Teks kecil / keterangan. <a href="#" class="text-brand-700 underline underline-offset-4">Tautan teks</a></p>
                <p class="text-sm">
                    <span class="font-normal">400</span> · <span class="font-medium">500</span> ·
                    <span class="font-semibold">600</span> · <span class="font-bold">700</span> · <span class="font-extrabold">800</span>
                </p>
            </div>
        </section>

        {{-- Tombol --}}
        <section>
            <h2 class="text-2xl">Tombol</h2>
            <div class="mt-5 flex flex-wrap items-center gap-3">
                <x-ui.button href="#" icon="arrow-right">Primer</x-ui.button>
                <x-ui.button href="#" variant="secondary">Sekunder</x-ui.button>
                <x-ui.button href="#" variant="outline">Outline</x-ui.button>
                <x-ui.button href="#" variant="accent">Aksen</x-ui.button>
                <x-ui.button href="#" variant="link" icon="arrow-right">Tautan</x-ui.button>
                <x-ui.button variant="primary" disabled>Nonaktif</x-ui.button>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <x-ui.button size="sm">Kecil</x-ui.button>
                <x-ui.button size="md">Sedang</x-ui.button>
                <x-ui.button size="lg">Besar</x-ui.button>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-3 rounded-xl bg-brand-900 p-6">
                <x-ui.button href="#" variant="light">Terang</x-ui.button>
                <x-ui.button href="#" variant="outline-light">Outline terang</x-ui.button>
            </div>
        </section>

        {{-- Judul bagian --}}
        <section>
            <h2 class="text-2xl">Judul Bagian</h2>
            <div class="mt-5 space-y-10">
                <x-ui.section-title eyebrow="Unit Bisnis" title="Tujuh unit, satu ekosistem" text="Contoh judul bagian rata kiri dengan paragraf pengantar." />
                <x-ui.section-title align="center" eyebrow="Berita" title="Kabar terbaru DECAA.ID" text="Contoh judul bagian rata tengah." />
            </div>
        </section>

        {{-- Kartu & badge --}}
        <section>
            <h2 class="text-2xl">Kartu & Badge</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-3">
                <x-ui.card>
                    <x-ui.badge>Software</x-ui.badge>
                    <h3 class="mt-3 text-lg">Kartu statis</h3>
                    <p class="mt-2 text-sm">Tanpa tautan, tanpa efek hover.</p>
                </x-ui.card>
                <x-ui.card href="#">
                    <x-ui.badge tone="accent">Baru</x-ui.badge>
                    <h3 class="mt-3 text-lg">Kartu bertautan</h3>
                    <p class="mt-2 text-sm">Seluruh kartu bisa diklik; naik sedikit saat disorot.</p>
                </x-ui.card>
                <x-ui.card href="#" class="bg-slate-50">
                    <div class="flex gap-2"><x-ui.badge tone="neutral">Netral</x-ui.badge><x-ui.badge tone="dark">Gelap</x-ui.badge></div>
                    <h3 class="mt-3 text-lg">Kartu abu muda</h3>
                    <p class="mt-2 text-sm">Gaya untuk grid unit dan klien.</p>
                </x-ui.card>
            </div>
        </section>

        {{-- Form --}}
        <section>
            <h2 class="text-2xl">Form Field</h2>
            <div class="mt-5 grid max-w-3xl gap-5 md:grid-cols-2">
                <x-ui.field label="Nama lengkap" name="sg_name" required placeholder="Nama Anda" />
                <x-ui.field label="Email" name="sg_email" type="email" hint="Kami tidak membagikan email Anda." placeholder="nama@perusahaan.com" />
                <x-ui.field label="Telepon (contoh galat)" name="sg_phone" error="Nomor telepon tidak valid." value="abc" />
                <div class="md:col-span-2"><x-ui.field label="Pesan" name="sg_msg" type="textarea" placeholder="Tulis pesan Anda" /></div>
            </div>
        </section>

        {{-- Breadcrumb, paginasi, ikon --}}
        <section>
            <h2 class="text-2xl">Breadcrumb, Paginasi, Ikon</h2>
            <x-ui.breadcrumb class="mt-5" :items="[['Beranda', '#'], ['Berita', '#'], ['Judul artikel', null]]" />
            <x-ui.pagination class="mt-8" :current="5" :last="12" pattern="#:page" />
            <div class="mt-8 flex flex-wrap gap-5 text-brand-700">
                @foreach ($icons as $ic)
                    <div class="flex w-24 flex-col items-center gap-2 text-xs text-slate-500">
                        <x-ui.icon :name="$ic" class="h-6 w-6 text-brand-700" />{{ $ic }}
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Animasi --}}
        <section>
            <h2 class="text-2xl">Animasi muncul saat scroll</h2>
            <p class="mt-2 text-sm">Scroll ke bawah; kartu muncul berurutan (delay 0, 150, 300 ms). Mati otomatis bila pengguna memilih "kurangi gerakan".</p>
            <div class="mt-5 grid gap-5 pb-24 md:grid-cols-3">
                @foreach ([0, 150, 300] as $d)
                    <x-ui.reveal :delay="$d">
                        <x-ui.card><h3 class="text-lg">Reveal {{ $d }} ms</h3><p class="mt-2 text-sm">Muncul sekali saat masuk layar.</p></x-ui.card>
                    </x-ui.reveal>
                @endforeach
            </div>
        </section>
    </div>
@endsection