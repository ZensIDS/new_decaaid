@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $page = \App\Support\Content::unitsPage();
    $units = \App\Support\Content::units();
@endphp

@section('title', $page['eyebrow'].' | '.config('site.name'))
@section('description', $page['lead'])

@section('content')
    <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$page['title']" :text="$page['lead']"
        :crumbs="[[$ui['home'], lroute('home')], [$page['eyebrow'], null]]" />

    {{-- Daftar pilar = JENIS BISNIS; brand di dalamnya tampil sebagai label (revisi PDF butir 2 dan 5) --}}
    <section class="section-y bg-white" aria-label="{{ $page['eyebrow'] }}">
        <div class="container">
            <ul class="grid gap-5 md:grid-cols-2 lg:grid-cols-4 lg:gap-6">
                @foreach ($units as $unit)
                    <li>
                        <x-ui.reveal :delay="($loop->index % 4) * 100" class="h-full">
                            <a href="{{ lroute('units.show', ['slug' => $unit['slug']]) }}"
                               class="group relative flex h-full min-h-[20rem] flex-col overflow-hidden rounded-3xl bg-white p-7 shadow-card ring-1 ring-slate-200/70 transition duration-500 hover:-translate-y-1 hover:shadow-card-hover">
                                <span class="pointer-events-none absolute inset-x-0 top-0 h-1 origin-left scale-x-0 bg-gradient-to-r from-brand-600 to-accent-500 transition duration-500 group-hover:scale-x-100" aria-hidden="true"></span>
                                <span class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-brand-50 opacity-0 transition duration-500 group-hover:opacity-100" aria-hidden="true"></span>

                                {{-- Nomor besar --}}
                                <span class="pointer-events-none absolute right-6 top-4 select-none text-6xl font-extrabold leading-none tracking-tighter text-slate-100" aria-hidden="true">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>

                                <x-ui.pillar-mark :unit="$unit" class="relative h-16 w-16" />

                                <div class="relative mt-7 flex-1">
                                    <h2 class="text-xl leading-tight">{{ $unit['name'] }}</h2>
                                    @if ($unit['brands'])
                                        <ul class="mt-4 flex flex-wrap gap-2">
                                            @foreach ($unit['brands'] as $brand)
                                                <li class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-800">{{ $brand['name'] }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                <div class="relative mt-8 flex items-center justify-between border-t border-slate-200/80 pt-5">
                                    <span class="text-sm font-semibold capitalize text-brand-700">{{ $ui['learn_more'] }}</span>
                                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-700 transition duration-300 group-hover:translate-x-1 group-hover:bg-brand-700 group-hover:text-white">
                                        <x-ui.icon name="arrow-right" class="h-4 w-4" />
                                    </span>
                                </div>
                            </a>
                        </x-ui.reveal>
                    </li>
                @endforeach
            </ul>
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