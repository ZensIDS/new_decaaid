@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $company = \App\Support\Content::company();
    $p = \App\Support\Content::about()['profile'];
    $units = \App\Support\Content::units();
    $offices = $company['offices'];
    $chair = $p['chairman'];

    // Foto kantor pusat: img/offices/jakarta.jpg, bila belum ada pakai foto kantor yang sama dengan hero (img/building.*)
    $hqPhoto = collect(['img/offices/jakarta.jpg', 'img/offices/jakarta.webp', 'img/building.png', 'img/building.jpg', 'img/building.jpeg', 'img/building.webp'])
        ->first(fn ($path) => file_exists(public_path($path)));
@endphp

@section('title', $p['title'].' | '.config('site.name'))
@section('description', $p['lead'])

@section('content')
    <x-ui.page-header :eyebrow="$p['eyebrow']" :title="$p['title']" :text="$p['lead']"
        :crumbs="[[$ui['home'], lroute('home')], [$p['eyebrow'], lroute('about.profile')], [$p['title'], null]]" />

    @include('partials.about-nav')

    {{-- Siapa kami ---------------------------------------------------------- --}}
    <section class="section-y bg-white" aria-labelledby="story-title">
        <div class="container grid items-center gap-14 lg:grid-cols-12 lg:gap-16">
            <x-ui.reveal class="lg:col-span-6">
                <h2 id="story-title" class="text-3xl leading-tight md:text-5xl">{{ $p['story_title'] }}</h2>
                <div class="mt-6 space-y-5">
                    @foreach ($p['story'] as $paragraph)
                        <p class="text-base leading-relaxed md:text-lg">{{ $paragraph }}</p>
                    @endforeach
                </div>
            </x-ui.reveal>

            {{-- Komposisi dua foto bertumpuk --}}
            <x-ui.reveal :delay="150" class="lg:col-span-6">
                <div class="relative mx-auto max-w-xl pb-10 pl-6 lg:max-w-none">
                    <div class="absolute bottom-0 left-0 top-10 w-3/5 rounded-3xl bg-brand-50" aria-hidden="true"></div>
                    <div class="absolute -right-3 top-0 h-24 w-24 rounded-full border border-brand-200" aria-hidden="true"></div>

                    {{-- DUMMY: foto asli di public/img/dummy/about-1.jpg dan about-2.jpg --}}
                    <x-ui.img src="img/dummy/about-1.jpg" :alt="$company['name']" label="Foto perusahaan (dummy)"
                              class="relative aspect-[4/3] w-[88%] rounded-3xl object-cover shadow-card-hover" />
                    <x-ui.img src="img/dummy/about-2.jpg" :alt="$company['name']" label="Foto kegiatan (dummy)"
                              class="absolute -bottom-0 right-0 aspect-square w-[46%] rounded-3xl border-4 border-white object-cover shadow-card-hover" />
                </div>
            </x-ui.reveal>
        </div>
    </section>

    {{-- Chairman Message ----------------------------------------------------- --}}
    {{-- DUMMY: foto asli di public/img/chairman.jpg (potret 4:5). Teks diatur di Content::about()['profile']['chairman'] --}}
    <section class="section-y bg-slate-50" aria-labelledby="chairman-title">
        <div class="container grid items-center gap-12 lg:grid-cols-12 lg:gap-16">
            <x-ui.reveal class="lg:col-span-5">
                <div class="relative mx-auto max-w-sm pb-6 pr-6 lg:max-w-none">
                    <div class="absolute bottom-0 right-0 top-8 w-4/5 rounded-3xl bg-brand-700" aria-hidden="true"></div>
                    <x-ui.img :src="$chair['photo']" :alt="$chair['name']" :label="$chair['name']"
                              class="relative aspect-[4/5] w-full rounded-3xl object-cover object-top shadow-card-hover" />
                </div>
            </x-ui.reveal>

            <x-ui.reveal :delay="150" class="lg:col-span-7">
                <h2 id="chairman-title" class="text-3xl leading-tight md:text-4xl">{{ $chair['title'] }}</h2>
                <span class="mt-5 block h-1 w-14 rounded-full bg-accent-500" aria-hidden="true"></span>

                <p class="mt-7 text-xl font-semibold leading-snug text-brand-800 md:text-2xl">{{ $chair['headline'] }}</p>
                <div class="mt-5 space-y-4">
                    @foreach ($chair['message'] as $paragraph)
                        <p class="text-base leading-relaxed md:text-lg">{{ $paragraph }}</p>
                    @endforeach
                </div>

                <div class="mt-8 border-l-2 border-accent-500 pl-4">
                    <p class="text-lg font-bold text-ink">{{ $chair['name'] }}</p>
                    <p class="text-sm font-semibold text-brand-700">{{ $chair['role'] }}</p>
                </div>
            </x-ui.reveal>
        </div>
    </section>

    {{-- Unit bisnis: ubin logo ------------------------------------------------- --}}
    <section class="section-y bg-white" aria-labelledby="units-title">
        <div class="container">
            <x-ui.reveal>
                <x-ui.section-title id="units-title" :title="\App\Support\Content::home()['units']['title']" />
            </x-ui.reveal>

            <ul class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($units as $unit)
                    <li>
                        <x-ui.reveal :delay="($loop->index % 4) * 80" class="h-full">
                            <a href="{{ lroute('units.show', ['slug' => $unit['slug']]) }}"
                               class="group flex h-full min-h-[10rem] flex-col items-center justify-center gap-4 rounded-2xl border border-slate-200 bg-white p-6 text-center transition duration-300 hover:border-brand-200 hover:bg-brand-50/60 hover:shadow-card-hover">
                                <x-ui.img :src="$unit['logo']" :alt="$unit['name']" :label="$unit['name']" class="h-12 w-full object-contain" />
                                <span class="text-sm font-semibold text-ink transition group-hover:text-brand-700">{{ $unit['name'] }}</span>
                            </a>
                        </x-ui.reveal>
                    </li>
                @endforeach

                <li>
                    <x-ui.reveal :delay="(count($units) % 4) * 80" class="h-full">
                        <a href="{{ lroute('units.index') }}"
                           class="group relative flex h-full min-h-[10rem] flex-col items-center justify-center gap-3 overflow-hidden rounded-2xl bg-brand-700 p-6 text-center text-white transition duration-300 hover:bg-brand-800">
                            <span class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-brand-600/50" aria-hidden="true"></span>
                            <span class="relative text-sm font-semibold capitalize">{{ $ui['view_all_units'] }}</span>
                            <span class="relative flex h-9 w-9 items-center justify-center rounded-full bg-white/15 transition group-hover:translate-x-1">
                                <x-ui.icon name="arrow-right" class="h-4 w-4" />
                            </span>
                        </a>
                    </x-ui.reveal>
                </li>
            </ul>
        </div>
    </section>

    {{-- Kantor: pusat menonjol, cabang pendamping ------------------------------ --}}
    <section class="section-y bg-brand-50" aria-labelledby="offices-title">
        <div class="container">
            <x-ui.reveal>
                <x-ui.section-title id="offices-title" :title="$ui['our_offices']" />
            </x-ui.reveal>

            <div class="mt-10 grid gap-6 lg:grid-cols-5">
                @foreach ($offices as $office)
                    @if ($loop->first)
                        <x-ui.reveal class="lg:col-span-3 lg:row-span-2">
                            <div class="relative isolate flex h-full min-h-[20rem] flex-col justify-between overflow-hidden rounded-3xl bg-brand-950 p-8 text-white md:p-10">
                                <div class="absolute inset-0 -z-20 bg-gradient-to-br from-brand-900 via-brand-950 to-brand-950" aria-hidden="true"></div>
                                @if ($hqPhoto)
                                    <img src="{{ asset($hqPhoto) }}" alt="" loading="lazy" decoding="async"
                                         class="pointer-events-none absolute inset-0 -z-10 h-full w-full select-none object-cover object-center opacity-50 mix-blend-luminosity [mask-image:linear-gradient(to_top_right,transparent_25%,#000)]">
                                @endif
                                <span class="absolute -right-16 -top-16 -z-10 h-64 w-64 rounded-full bg-brand-600/35 blur-3xl" aria-hidden="true"></span>
                                <span class="absolute inset-x-0 bottom-0 -z-10 h-1/2 bg-gradient-to-t from-brand-950/80 to-transparent" aria-hidden="true"></span>

                                <x-ui.badge tone="accent" class="relative w-fit">{{ $office['type'] }}</x-ui.badge>
                                <div class="relative mt-16">
                                    <h3 class="text-4xl text-white md:text-5xl">{{ $office['city'] }}</h3>
                                    <p class="mt-4 flex max-w-md gap-2 text-base leading-relaxed text-brand-100">
                                        <x-ui.icon name="map-pin" class="mt-1 h-4 w-4 shrink-0 text-accent-400" />
                                        <span>{{ $office['address'] }}</span>
                                    </p>
                                </div>
                            </div>
                        </x-ui.reveal>
                    @else
                        <x-ui.reveal :delay="$loop->index * 100" class="lg:col-span-2">
                            <div class="flex h-full flex-col justify-between rounded-3xl bg-white p-7 shadow-card">
                                <x-ui.badge tone="neutral" class="w-fit">{{ $office['type'] }}</x-ui.badge>
                                <div class="mt-6">
                                    <h3 class="text-2xl">{{ $office['city'] }}</h3>
                                    <p class="mt-2 flex gap-2 text-base leading-relaxed">
                                        <x-ui.icon name="map-pin" class="mt-1 h-4 w-4 shrink-0 text-brand-700" />
                                        <span>{{ $office['address'] }}</span>
                                    </p>
                                </div>
                            </div>
                        </x-ui.reveal>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <x-ui.cta-band />
@endsection