@extends('layouts.app')

@section('title', config('site.name').' | '.\App\Support\Content::company()['tagline'])

@php
    $company = \App\Support\Content::company();
    $home = \App\Support\Content::home();
    $units = \App\Support\Content::units();
    $projects = \App\Support\Content::projects();
    $clients = \App\Support\Content::homeClients(24);
    $posts = \App\Support\Content::posts();
    $community = \App\Support\Content::community();
    $awards = \App\Support\Content::awardsPage();
    // v106: siluet samar di kanan latar hero (di belakang kolase): pilihan admin (hero_bg), cadangan foto gedung seperti header halaman dalam.
    $heroSilhouette = \App\Support\Content::homeImage('hero_bg');
    if ($heroSilhouette === '') {
        $heroSilhouette = collect(['png', 'jpg', 'jpeg', 'webp'])->map(fn ($ext) => 'img/building.'.$ext)
            ->first(fn ($path) => file_exists(public_path($path))) ?? '';
    }
@endphp

@section('content')

    {{-- 1. HERO ------------------------------------------------------------ --}}
    <section class="no-justify relative isolate overflow-hidden bg-brand-950 text-white" aria-labelledby="hero-title">
        {{-- Latar berlapis: gradasi, pola titik, cahaya lembut --}}
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand-900 via-brand-950 to-brand-950"></div>
        <div class="absolute inset-0 -z-10 opacity-70 [background-image:radial-gradient(rgba(255,255,255,0.16)_1px,transparent_1px)] [background-size:28px_28px] [mask-image:radial-gradient(ellipse_at_25%_45%,#000,transparent_72%)]"></div>
        <div class="absolute -right-40 -top-40 -z-10 h-[38rem] w-[38rem] rounded-full bg-brand-600/35 blur-3xl"></div>
        <div class="absolute -bottom-48 left-1/4 -z-10 h-96 w-96 rounded-full bg-accent-500/10 blur-3xl"></div>

        {{-- Siluet foto di kanan (sama seperti header halaman dalam, mis. Pilar Bisnis): menyatu dengan latar biru lewat blend
             dan memudar ke kiri. Tampil di belakang kolase foto. Ganti dari /admin/beranda > Foto Beranda (Hero: siluet latar). --}}
        @if ($heroSilhouette !== '' && is_file(public_path($heroSilhouette)))
            <div class="pointer-events-none absolute inset-y-0 right-0 -z-10 w-full lg:w-3/5" aria-hidden="true">
                <x-ui.img :src="$heroSilhouette" alt="" :lazy="false" sizes="(min-width: 1024px) 60vw, 100vw"
                          class="h-full w-full select-none object-cover object-center opacity-25 mix-blend-luminosity sm:opacity-30 lg:opacity-60 [mask-image:linear-gradient(to_right,transparent,#000_65%)]" />
            </div>
        @endif

        <div class="container grid items-center gap-14 py-20 sm:py-24 lg:min-h-[40rem] lg:grid-cols-12 lg:gap-10 lg:py-28">
            <div class="lg:col-span-6">
                @if (! empty($home['hero']['eyebrow']))
                    <p class="animate-fade-up inline-flex items-center gap-2.5 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-brand-100 backdrop-blur">
                        <span class="h-1.5 w-1.5 rounded-full bg-accent-500"></span>
                        {{ $home['hero']['eyebrow'] }}
                    </p>
                @endif
                <h1 id="hero-title"
                    class="animate-fade-up capitalize @if (! empty($home['hero']['eyebrow'])) mt-7 @endif text-4xl font-extrabold leading-[1.08] text-white sm:text-5xl lg:text-[3.5rem]"
                    style="animation-delay: 100ms">
                    {{ $home['hero']['title'] }}
                </h1>
                <div class="animate-fade-up mt-8 h-1 w-16 rounded-full bg-accent-500" style="animation-delay: 180ms"></div>
                <p class="animate-fade-up mt-8 max-w-xl text-lg leading-relaxed text-brand-100" style="animation-delay: 240ms">
                    {{ $home['hero']['text'] }}
                </p>
            </div>
            {{-- Kolase foto. DUMMY: ganti di public/img/dummy/hero.jpg, hero-2.jpg, hero-3.jpg --}}
            <div class="animate-fade-up relative mx-auto h-[24rem] w-full max-w-xl sm:h-[30rem] lg:col-span-6 lg:h-[34rem] lg:max-w-none"
                 style="animation-delay: 250ms">
                <div class="absolute -left-2 top-10 h-28 w-28 rounded-full border border-white/15"></div>
                <div class="absolute bottom-16 right-0 h-3 w-3 rounded-full bg-accent-500"></div>

                <div class="absolute right-0 top-0 h-[78%] w-[64%] overflow-hidden rounded-[2rem] shadow-card-hover ring-1 ring-white/20">
                    <x-ui.img :src="\App\Support\Content::homeImage('hero_1')" :alt="$company['name']" label="Foto hero (dummy)" :lazy="false"
                              class="h-full w-full object-cover" />
                </div>

                <div class="animate-float absolute bottom-[6%] left-0 h-[50%] w-[44%] overflow-hidden rounded-[2rem] border-4 border-brand-950 shadow-card-hover">
                    <x-ui.img :src="\App\Support\Content::homeImage('hero_2')" alt="" label="Foto kegiatan (dummy)"
                              class="h-full w-full object-cover" />
                </div>

                <div class="absolute bottom-0 right-[8%] h-[28%] w-[30%] overflow-hidden rounded-3xl border-4 border-brand-950 shadow-card-hover">
                    <x-ui.img :src="\App\Support\Content::homeImage('hero_3')" alt="" label="Foto tim (dummy)"
                              class="h-full w-full object-cover" />
                </div>
            </div>
        </div>
    </section>

    {{-- 2-8. SECTION YANG BISA DIURUT DAN DISEMBUNYIKAN DARI ADMIN (B3e). Berkas: partials/home/{kunci}.blade.php --}}
    @foreach (\App\Support\Content::homeSections() as $sectionKey)
        @include('partials.home.'.$sectionKey)
    @endforeach

    {{-- 9. CTA HUBUNGI KAMI ------------------------------------------------- --}}
    <section class="relative isolate overflow-hidden bg-brand-700 py-16 md:py-20" aria-labelledby="cta-title">
        <div class="absolute -right-24 -top-24 -z-10 h-80 w-80 rounded-full bg-brand-500/40 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-16 -z-10 h-72 w-72 rounded-full bg-brand-900/60 blur-3xl"></div>

        <div class="container">
            <x-ui.reveal>
                <div class="mx-auto max-w-3xl text-center">
                    <h2 id="cta-title" class="text-3xl leading-tight text-white md:text-4xl">{{ $home['cta']['title'] }}</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-brand-100 md:text-lg">{{ $home['cta']['text'] }}</p>
                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                        <x-ui.button :href="lroute('contact')" variant="light" size="lg" icon="arrow-right">
                            {{ t('nav.contact_us') }}
                        </x-ui.button>
                        <x-ui.button :href="'mailto:'.$company['email']['general']" variant="outline-light" size="lg">
                            {{ t('home.cta_email') }}
                        </x-ui.button>
                    </div>
                </div>
            </x-ui.reveal>
        </div>
    </section>
@endsection
