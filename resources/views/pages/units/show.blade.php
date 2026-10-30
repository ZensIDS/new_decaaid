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

@section('title', $detail['name'].' | '.$page['eyebrow'].' | '.config('site.name'))
@section('description', $detail['summary'])

@section('content')
    <x-ui.page-header :eyebrow="$detail['subtitle'] ?: $page['eyebrow']" :title="$detail['name']" :text="$detail['summary']"
        :crumbs="[[$ui['home'], lroute('home')], [$page['eyebrow'], lroute('units.index')], [$detail['name'], null]]" />

    {{-- 1. Tentang pilar ------------------------------------------------------- --}}
    <section class="section-y bg-white" aria-labelledby="about-unit">
        <div class="container grid items-center gap-12 lg:grid-cols-12 lg:gap-16">
            <x-ui.reveal class="lg:col-span-7">
                <p class="eyebrow inline-flex items-center gap-3">
                    <span class="h-0.5 w-8 rounded-full bg-accent-500"></span>
                    {{ $page['about_title'] }}
                </p>
                <h2 id="about-unit" class="mt-4 text-3xl leading-tight md:text-4xl">{{ $detail['name'] }}</h2>
                <p class="mt-5 text-base leading-relaxed md:text-lg">{{ $detail['description'] }}</p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <x-ui.button :href="lroute('contact')" icon="arrow-right">{{ t('nav.contact_us') }}</x-ui.button>
                    <x-ui.button :href="lroute('units.index')" variant="outline">{{ $page['eyebrow'] }}</x-ui.button>
                </div>
            </x-ui.reveal>

            {{-- Panel logo --}}
            <x-ui.reveal :delay="150" class="lg:col-span-5">
                <div class="relative isolate flex aspect-[4/3] items-center justify-center overflow-hidden rounded-3xl bg-gradient-to-br from-brand-50 via-white to-brand-100 p-8 ring-1 ring-brand-100">
                    <div class="absolute inset-0 -z-10 opacity-70 [background-image:radial-gradient(rgba(27,53,144,0.14)_1px,transparent_1px)] [background-size:22px_22px] [mask-image:radial-gradient(ellipse_at_center,#000,transparent_75%)]"></div>
                    <div class="absolute -right-12 -top-12 -z-10 h-48 w-48 rounded-full border border-brand-200"></div>
                    <div class="absolute -bottom-16 -left-10 -z-10 h-52 w-52 rounded-full bg-brand-200/50 blur-2xl"></div>

                    <div class="flex h-3/5 w-4/5 items-center justify-center rounded-2xl bg-white p-6 shadow-card-hover ring-1 ring-slate-200/70">
                        <x-ui.img :src="$detail['logo']" :alt="$detail['name']" :label="$detail['name']" class="h-full w-full object-contain" />
                    </div>
                </div>
            </x-ui.reveal>
        </div>
    </section>

    {{-- 2. Layanan -------------------------------------------------------------- --}}
    <section class="section-y bg-slate-50" aria-labelledby="services-title">
        <div class="container grid gap-10 lg:grid-cols-12 lg:gap-14">
            <x-ui.reveal class="lg:col-span-4">
                <div class="lg:sticky lg:top-28">
                    <p class="eyebrow inline-flex items-center gap-3">
                        <span class="h-0.5 w-8 rounded-full bg-accent-500"></span>
                        {{ $detail['name'] }}
                    </p>
                    <h2 id="services-title" class="mt-4 text-3xl leading-tight md:text-4xl">{{ $page['services_title'] }}</h2>
                    <p class="mt-4 text-base leading-relaxed">{{ t('units.services_lead') }}</p>
                    <div class="mt-7">
                        <x-ui.button :href="lroute('contact')" variant="secondary" icon="arrow-right">{{ t('nav.contact_us') }}</x-ui.button>
                    </div>
                </div>
            </x-ui.reveal>

            <div class="grid gap-5 sm:grid-cols-2 lg:col-span-8">
                @foreach ($detail['services'] as $service)
                    <x-ui.reveal :delay="($loop->index % 2) * 100" class="h-full">
                        <div class="group relative h-full overflow-hidden rounded-2xl bg-white p-7 shadow-card ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover">
                            <span class="absolute inset-x-0 top-0 h-1 origin-left scale-x-0 bg-gradient-to-r from-brand-600 to-accent-500 transition duration-500 group-hover:scale-x-100" aria-hidden="true"></span>
                            <div class="flex items-start justify-between">
                                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-700 transition duration-300 group-hover:bg-brand-700 group-hover:text-white">
                                    <x-ui.icon name="check" class="h-5 w-5" />
                                </span>
                                <span class="text-4xl font-extrabold leading-none tracking-tighter text-slate-100" aria-hidden="true">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>
                            <h3 class="mt-6 text-lg leading-snug">{{ $service['title'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed">{{ $service['text'] }}</p>
                        </div>
                    </x-ui.reveal>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 3. Cara kerja ----------------------------------------------------------- --}}
    <section class="relative isolate overflow-hidden bg-brand-950 py-16 text-white md:py-24" aria-labelledby="process-title">
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand-900 via-brand-950 to-brand-950"></div>
        <div class="absolute inset-0 -z-10 opacity-50 [background-image:radial-gradient(rgba(255,255,255,0.14)_1px,transparent_1px)] [background-size:26px_26px] [mask-image:radial-gradient(ellipse_at_15%_20%,#000,transparent_70%)]"></div>
        <div class="absolute -right-32 bottom-0 -z-10 h-80 w-80 rounded-full bg-brand-600/25 blur-3xl"></div>

        <div class="container">
            <x-ui.reveal>
                <p class="eyebrow inline-flex items-center gap-3 text-accent-400">
                    <span class="h-0.5 w-8 rounded-full bg-accent-500"></span>
                    {{ t('units.process_eyebrow') }}
                </p>
                <h2 id="process-title" class="mt-4 max-w-2xl text-3xl font-extrabold leading-tight text-white md:text-4xl">{{ $page['process_title'] }}</h2>
                <p class="mt-4 max-w-2xl text-base leading-relaxed text-brand-100 md:text-lg">{{ t('units.process_lead') }}</p>
            </x-ui.reveal>

            <ol class="mt-12 grid gap-10 md:grid-cols-4 md:gap-6">
                @foreach ($detail['steps'] as $step)
                    <li class="relative {{ $loop->last ? '' : 'md:after:absolute md:after:left-[3.5rem] md:after:-right-6 md:after:top-5 md:after:h-px md:after:bg-gradient-to-r md:after:from-white/30 md:after:to-white/5' }}">
                        <x-ui.reveal :delay="$loop->index * 100">
                            <span class="relative z-10 flex h-10 w-10 items-center justify-center rounded-full bg-brand-700 text-sm font-bold text-white ring-4 ring-brand-950">
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <h3 class="mt-6 text-lg text-white">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-brand-100">{{ $step['text'] }}</p>
                        </x-ui.reveal>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- 4. Proyek terkait ------------------------------------------------------- --}}
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

    {{-- 5. Pilar lainnya -------------------------------------------------------- --}}
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

            <ul class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($others as $other)
                    <li>
                        <x-ui.reveal :delay="$loop->index * 60" class="h-full">
                            <a href="{{ lroute('units.show', ['slug' => $other['slug']]) }}"
                               class="group flex h-full flex-col items-center gap-4 rounded-2xl bg-white p-5 text-center shadow-card ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover hover:ring-brand-200">
                                <span class="flex h-16 w-full items-center justify-center">
                                    <x-ui.img :src="$other['logo']" :alt="$other['name']" :label="$other['name']"
                                              class="h-full w-full object-contain transition duration-300 group-hover:scale-105" />
                                </span>
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