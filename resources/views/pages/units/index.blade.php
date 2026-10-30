@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $page = \App\Support\Content::unitsPage();
    $units = \App\Support\Content::units();

    // Susunan bento: lebar kartu per baris (lg). Kartu gelap dipilih diagonal agar seimbang.
    $spans = [
        'lg:col-span-7', 'lg:col-span-5',
        'lg:col-span-4', 'lg:col-span-4', 'lg:col-span-4',
        'lg:col-span-5', 'md:col-span-2 lg:col-span-7',
    ];
    $dark = [0, 6];
@endphp

@section('title', $page['eyebrow'].' | '.config('site.name'))
@section('description', $page['lead'])

@section('content')
    <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$page['title']" :text="$page['lead']"
        :crumbs="[[$ui['home'], lroute('home')], [$page['eyebrow'], null]]" />

    {{-- Grid bento pilar ------------------------------------------------------- --}}
    <section class="section-y bg-white" aria-label="{{ $page['eyebrow'] }}">
        <div class="container">
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-12 lg:gap-6">
                @foreach ($units as $unit)
                    @php $isDark = in_array($loop->index, $dark, true); @endphp
                    <x-ui.reveal :delay="($loop->index % 3) * 100" class="h-full {{ $spans[$loop->index] ?? 'lg:col-span-4' }}">
                        <a href="{{ lroute('units.show', ['slug' => $unit['slug']]) }}"
                           class="group relative flex h-full min-h-[19rem] flex-col overflow-hidden rounded-3xl p-7 transition duration-500 hover:-translate-y-1 sm:p-9
                                  {{ $isDark
                                      ? 'bg-gradient-to-br from-brand-800 via-brand-900 to-brand-950 text-brand-100 shadow-card-hover'
                                      : 'bg-white shadow-card ring-1 ring-slate-200/70 hover:shadow-card-hover' }}">

                            {{-- Dekorasi --}}
                            @if ($isDark)
                                <span class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-brand-600/30 blur-3xl" aria-hidden="true"></span>
                                <span class="pointer-events-none absolute -bottom-24 -left-10 h-56 w-56 rounded-full border border-white/10" aria-hidden="true"></span>
                            @else
                                <span class="pointer-events-none absolute inset-x-0 top-0 h-1 origin-left scale-x-0 bg-gradient-to-r from-brand-600 to-accent-500 transition duration-500 group-hover:scale-x-100" aria-hidden="true"></span>
                                <span class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-brand-50 opacity-0 transition duration-500 group-hover:opacity-100" aria-hidden="true"></span>
                            @endif

                            {{-- Nomor besar --}}
                            <span class="pointer-events-none absolute right-6 top-4 select-none text-7xl font-extrabold leading-none tracking-tighter {{ $isDark ? 'text-white/10' : 'text-slate-100' }}" aria-hidden="true">
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>

                            {{-- Logo --}}
                            <div class="relative flex h-20 w-44 items-center justify-center rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200/70">
                                <x-ui.img :src="$unit['logo']" :alt="$unit['name']" :label="$unit['name']" class="h-full w-full object-contain" />
                            </div>

                            {{-- Teks --}}
                            <div class="relative mt-7 flex-1">
                                <h2 class="text-2xl leading-tight {{ $isDark ? 'text-white' : '' }}">{{ $unit['name'] }}</h2>
                                @if ($unit['subtitle'])
                                    <p class="mt-2 inline-flex items-center gap-2 text-sm font-semibold {{ $isDark ? 'text-accent-400' : 'text-brand-700' }}">
                                        <span class="h-0.5 w-5 rounded-full {{ $isDark ? 'bg-accent-400' : 'bg-accent-500' }}"></span>
                                        {{ $unit['subtitle'] }}
                                    </p>
                                @endif
                                <p class="mt-4 max-w-md text-base leading-relaxed {{ $isDark ? 'text-brand-100' : '' }}">{{ $unit['summary'] }}</p>
                            </div>

                            {{-- Ajakan --}}
                            <div class="relative mt-8 flex items-center justify-between border-t pt-5 {{ $isDark ? 'border-white/15' : 'border-slate-200/80' }}">
                                <span class="text-sm font-semibold capitalize {{ $isDark ? 'text-white' : 'text-brand-700' }}">{{ $ui['learn_more'] }}</span>
                                <span class="flex h-10 w-10 items-center justify-center rounded-full transition duration-300 group-hover:translate-x-1
                                             {{ $isDark ? 'bg-white/10 text-white group-hover:bg-white group-hover:text-brand-800' : 'bg-brand-50 text-brand-700 group-hover:bg-brand-700 group-hover:text-white' }}">
                                    <x-ui.icon name="arrow-right" class="h-4 w-4" />
                                </span>
                            </div>
                        </a>
                    </x-ui.reveal>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Satu pintu (DUMMY: teks ada di Labels.php, kunci units.band_*) ---------- --}}
    <section class="relative isolate overflow-hidden bg-brand-950 py-16 text-white md:py-24" aria-labelledby="band-title">
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand-900 via-brand-950 to-brand-950"></div>
        <div class="absolute inset-0 -z-10 opacity-50 [background-image:radial-gradient(rgba(255,255,255,0.14)_1px,transparent_1px)] [background-size:26px_26px] [mask-image:radial-gradient(ellipse_at_80%_20%,#000,transparent_70%)]"></div>
        <div class="absolute -left-32 top-0 -z-10 h-80 w-80 rounded-full bg-brand-600/30 blur-3xl"></div>

        <div class="container">
            <x-ui.reveal>
                <p class="eyebrow inline-flex items-center gap-3 text-accent-400">
                    <span class="h-0.5 w-8 rounded-full bg-accent-500"></span>
                    {{ t('units.band_eyebrow') }}
                </p>
                <h2 id="band-title" class="mt-4 max-w-2xl text-3xl font-extrabold leading-tight text-white md:text-4xl">{{ t('units.band_title') }}</h2>
            </x-ui.reveal>

            <div class="mt-12 grid gap-5 md:grid-cols-3">
                @foreach ([1, 2, 3] as $n)
                    <x-ui.reveal :delay="($n - 1) * 100" class="h-full">
                        <div class="h-full rounded-2xl border border-white/10 bg-white/5 p-7 backdrop-blur transition duration-300 hover:border-white/25 hover:bg-white/10">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-700 text-sm font-bold text-white ring-1 ring-white/20">
                                {{ str_pad((string) $n, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <h3 class="mt-6 text-xl text-white">{{ t('units.band_'.$n.'_title') }}</h3>
                            <p class="mt-3 text-sm leading-relaxed text-brand-100">{{ t('units.band_'.$n.'_text') }}</p>
                        </div>
                    </x-ui.reveal>
                @endforeach
            </div>
        </div>
    </section>

    <x-ui.cta-band />
@endsection