{{--
    Hero untuk halaman dalam (dipakai A4 dst.).
    <x-ui.page-header eyebrow="Tentang Kami" title="Profil Perusahaan" text="..." :crumbs="[['Beranda', lroute('home')], ['Profil', null]]" />

    Foto kantor (opsional): taruh di public/img/building.png (atau .jpg / .jpeg / .webp).
    Foto biasa (tanpa perlu transparan) ditampilkan di sisi kanan, menyatu dengan latar biru lewat
    blend dan pudar ke kiri supaya teks tetap terbaca. Bila file belum ada, bagian foto tidak tampil.
--}}
@props(['eyebrow' => null, 'title', 'text' => null, 'crumbs' => []])

@php
    $building = collect(['png', 'jpg', 'jpeg', 'webp'])
        ->map(fn ($ext) => 'img/building.'.$ext)
        ->first(fn ($path) => file_exists(public_path($path)));
@endphp

<section class="relative isolate overflow-hidden bg-brand-950 text-white" aria-labelledby="page-title">
    {{-- Latar berlapis --}}
    <div class="absolute inset-0 -z-20 bg-gradient-to-br from-brand-900 via-brand-950 to-brand-950"></div>
    <div class="absolute inset-0 -z-20 opacity-60 [background-image:radial-gradient(rgba(255,255,255,0.15)_1px,transparent_1px)] [background-size:26px_26px] [mask-image:radial-gradient(ellipse_at_20%_30%,#000,transparent_70%)]"></div>
    <div class="absolute -right-28 -top-32 -z-20 h-96 w-96 rounded-full bg-brand-600/35 blur-3xl"></div>
    <div class="absolute -bottom-40 left-1/3 -z-20 h-72 w-72 rounded-full bg-accent-500/10 blur-3xl"></div>
    <div class="absolute right-[18%] top-10 -z-20 hidden h-40 w-40 rounded-full border border-white/10 lg:block"></div>

    {{-- Foto kantor (kanan, memenuhi tinggi hero) --}}
    @if ($building)
        <div class="pointer-events-none absolute inset-y-0 right-0 -z-10 w-full lg:w-3/5" aria-hidden="true">
            <img src="{{ asset($building) }}" alt="" decoding="async"
                 class="h-full w-full select-none object-cover object-center opacity-25 mix-blend-luminosity sm:opacity-30 lg:opacity-60 [mask-image:linear-gradient(to_right,transparent,#000_65%)]">
        </div>
    @endif

    {{-- Garis dasar halus di tepi bawah --}}
    <div class="absolute inset-x-0 bottom-0 -z-10 h-px bg-gradient-to-r from-transparent via-white/25 to-transparent"></div>
    <div class="absolute inset-x-0 bottom-0 -z-10 h-24 bg-gradient-to-t from-brand-950/70 to-transparent"></div>

    <div class="container relative py-14 md:py-20 lg:min-h-[22rem] lg:py-24">
        @if ($crumbs)
            <x-ui.breadcrumb :items="$crumbs" :light="true" class="animate-fade-up" />
        @endif

        @if ($eyebrow)
            <p class="eyebrow animate-fade-up mt-8 inline-flex items-center gap-3 text-accent-400" style="animation-delay: 80ms">
                <span class="h-0.5 w-8 rounded-full bg-accent-500"></span>
                {{ $eyebrow }}
            </p>
        @endif

        <h1 id="page-title" class="animate-fade-up mt-4 max-w-3xl text-4xl font-extrabold leading-tight text-white md:text-5xl"
            style="animation-delay: 160ms">{{ $title }}</h1>

        @if ($text)
            <p class="animate-fade-up mt-5 max-w-2xl text-lg leading-relaxed text-brand-100" style="animation-delay: 240ms">{{ $text }}</p>
        @endif
    </div>
</section>