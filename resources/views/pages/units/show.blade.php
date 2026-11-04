@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $page = \App\Support\Content::unitsPage();
    $detail = \App\Support\Content::unitDetail($unit['slug']) ?? abort(404);
    $all = \App\Support\Content::units();

    $others = array_values(array_filter($all, fn ($u) => $u['slug'] !== $detail['slug']));

    // Posisi pilar ini + tetangga (berputar) untuk tombol sebelumnya / berikutnya
    $index = (int) array_search($detail['slug'], array_column($all, 'slug'), true);
    $prev = $all[($index - 1 + count($all)) % count($all)];
    $next = $all[($index + 1) % count($all)];
@endphp

@section('title', (($detail['seo_title'] ?? '') !== '' ? $detail['seo_title'] : $detail['name'].' | '.$page['eyebrow']).' | '.config('site.name'))
@section('description', ($detail['seo_description'] ?? '') !== '' ? $detail['seo_description'] : $detail['summary'])

@section('content')
    <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$detail['name']" :text="$detail['summary']"
        :crumbs="[[$ui['home'], lroute('home')], [$page['eyebrow'], lroute('units.index')], [$detail['name'], null]]" />

    {{-- 1. Perusahaan/brand di dalam pilar. Tiap perusahaan punya halaman sendiri (units.brand). ------------- --}}
    <section class="section-y bg-white" aria-labelledby="brands-title">
        <div class="container">
            <x-ui.reveal>
                <p class="eyebrow inline-flex items-center gap-3">
                    <span class="h-0.5 w-8 rounded-full bg-accent-500"></span>
                    {{ $detail['name'] }}
                </p>
                <h2 id="brands-title" class="mt-4 text-3xl leading-tight md:text-4xl">{{ $page['brands_title'] }}</h2>
            </x-ui.reveal>

            @if ($detail['brands'])
                <ul class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 lg:gap-6">
                    @foreach ($detail['brands'] as $brand)
                        <li>
                            <x-ui.reveal :delay="($loop->index % 3) * 100" class="h-full">
                                <a href="{{ lroute('units.brand', ['slug' => $detail['slug'], 'brand' => $brand['slug']]) }}"
                                   class="group flex h-full flex-col overflow-hidden rounded-3xl bg-white p-6 shadow-card ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover hover:ring-brand-200">
                                    <div class="flex aspect-[4/3] items-center justify-center rounded-2xl bg-slate-50 p-6 ring-1 ring-slate-200/70">
                                        <x-ui.img :src="$brand['logo']" :alt="$brand['name']" :label="$brand['name']" class="h-full w-full object-contain" />
                                    </div>
                                    <h3 class="mt-6 text-xl leading-tight transition group-hover:text-brand-700">{{ $brand['name'] }}</h3>
                                    @if ($brand['description'])
                                        <p class="mt-3 line-clamp-3 flex-1 text-sm leading-relaxed">{{ $brand['description'] }}</p>
                                    @else
                                        <span class="flex-1"></span>
                                    @endif
                                    <span class="mt-5 inline-flex items-center gap-2 text-sm font-semibold capitalize text-brand-700">
                                        {{ $page['brand_view'] }}
                                        <x-ui.icon name="arrow-right" class="h-4 w-4 transition duration-300 group-hover:translate-x-1" />
                                    </span>
                                </a>
                            </x-ui.reveal>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-8 rounded-2xl bg-slate-50 p-8 text-base ring-1 ring-slate-200/70">{{ $page['brands_empty'] }}</p>
            @endif
        </div>
    </section>

    {{-- 2. Proyek terkait ------------------------------------------------------- --}}
    <section class="section-y bg-white" aria-labelledby="projects-title">
        <div class="container">
            <x-ui.reveal>
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div>
                        <p class="eyebrow inline-flex items-center gap-3">
                            <span class="h-0.5 w-8 rounded-full bg-accent-500"></span>
                            {{ t('units.projects_eyebrow') }}
                        </p>
                        <h2 id="projects-title" class="mt-4 text-3xl leading-tight md:text-4xl">{{ $page['projects_title'] }}</h2>
                    </div>
                    @if ($detail['projects'])
                        <x-ui.button :href="lroute('portfolio.index')" variant="outline" icon="arrow-right">{{ t('home.all_projects') }}</x-ui.button>
                    @endif
                </div>
            </x-ui.reveal>

            @if ($detail['projects'])
                <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($detail['projects'] as $project)
                        <x-ui.reveal :delay="$loop->index * 100" class="h-full">
                            <a href="{{ lroute('portfolio.show', ['slug' => $project['slug']]) }}"
                               class="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover">
                                <div class="relative overflow-hidden">
                                    <x-ui.img :src="$project['image']" :alt="$project['title']" :label="$project['title']"
                                              class="aspect-[16/10] w-full object-cover transition duration-700 group-hover:scale-105" />
                                    <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-brand-800 shadow-sm backdrop-blur">{{ $project['year'] }}</span>
                                </div>
                                <div class="flex flex-1 flex-col p-6">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $project['client'] }}</p>
                                    <h3 class="mt-2 text-lg leading-snug transition group-hover:text-brand-700">{{ $project['title'] }}</h3>
                                    <p class="mt-2 flex-1 text-sm leading-relaxed">{{ $project['summary'] }}</p>
                                    <span class="mt-5 inline-flex items-center gap-2 text-sm font-semibold capitalize text-brand-700">
                                        {{ t('units.view_project') }}
                                        <x-ui.icon name="arrow-right" class="h-4 w-4 transition duration-300 group-hover:translate-x-1" />
                                    </span>
                                </div>
                            </a>
                        </x-ui.reveal>
                    @endforeach
                </div>
            @else
                <p class="mt-8 rounded-2xl bg-slate-50 p-8 text-base ring-1 ring-slate-200/70">{{ $page['projects_empty'] }}</p>
            @endif
        </div>
    </section>

    {{-- 3. Pilar lainnya -------------------------------------------------------- --}}
    <section class="section-y bg-slate-50" aria-labelledby="others-title">
        <div class="container">
            <x-ui.reveal>
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div>
                        <p class="eyebrow inline-flex items-center gap-3">
                            <span class="h-0.5 w-8 rounded-full bg-accent-500"></span>
                            {{ $page['eyebrow'] }}
                        </p>
                        <h2 id="others-title" class="mt-4 text-3xl leading-tight md:text-4xl">{{ $page['others_title'] }}</h2>
                    </div>

                    {{-- Sebelumnya / berikutnya --}}
                    <div class="flex gap-3">
                        <a href="{{ lroute('units.show', ['slug' => $prev['slug']]) }}"
                           class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-brand-700 shadow-card ring-1 ring-slate-200/70 transition hover:bg-brand-700 hover:text-white"
                           aria-label="{{ t('units.prev') }}: {{ $prev['name'] }}" title="{{ $prev['name'] }}">
                            <x-ui.icon name="chevron-left" class="h-5 w-5" />
                        </a>
                        <a href="{{ lroute('units.show', ['slug' => $next['slug']]) }}"
                           class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-brand-700 shadow-card ring-1 ring-slate-200/70 transition hover:bg-brand-700 hover:text-white"
                           aria-label="{{ t('units.next') }}: {{ $next['name'] }}" title="{{ $next['name'] }}">
                            <x-ui.icon name="chevron-right" class="h-5 w-5" />
                        </a>
                    </div>
                </div>
            </x-ui.reveal>

            <ul class="mt-10 grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ($others as $other)
                    <li>
                        <x-ui.reveal :delay="($loop->index % 4) * 60" class="h-full">
                            <a href="{{ lroute('units.show', ['slug' => $other['slug']]) }}"
                               class="group flex h-full flex-col items-center gap-4 rounded-2xl bg-white p-5 text-center shadow-card ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover hover:ring-brand-200">
                                <x-ui.pillar-mark :unit="$other" class="h-14 w-14" />
                                <span class="text-sm font-semibold leading-snug text-ink transition group-hover:text-brand-700">{{ $other['name'] }}</span>
                            </a>
                        </x-ui.reveal>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <x-ui.cta-band />
@endsection