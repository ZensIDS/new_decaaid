@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $page = \App\Support\Content::awardsPage();

    $items = collect($page['items']);
    $featured = $items->firstWhere('featured', true) ?? $items->first();
    $rest = $items->reject(fn ($a) => $featured && $a['slug'] === $featured['slug'])->values();
    $years = $items->pluck('year')->unique()->sortDesc()->values();
@endphp

@section('title', $page['title'].' | '.config('site.name'))
@section('description', $page['lead'])

@section('content')
    <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$page['title']" :text="$page['lead']"
        :crumbs="[[$ui['home'], lroute('home')], [$page['eyebrow'], lroute('portfolio.index')], [$page['title'], null]]" />

    @if ($items->contains(fn ($a) => ! empty($a['machine_translated'])))
        <div class="container pt-8">
            <p class="rounded-xl bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-600 ring-1 ring-inset ring-slate-200" role="note">
                {{ app()->getLocale() === 'en'
                    ? 'Some award descriptions on this page were translated automatically from Indonesian and have not been reviewed yet.'
                    : 'Beberapa keterangan penghargaan di halaman ini diterjemahkan otomatis dari Bahasa Inggris dan belum ditinjau.' }}
            </p>
        </div>
    @endif

    @if ($items->isEmpty())
        <section class="section-y bg-white">
            <div class="container">
                <p class="rounded-2xl bg-slate-50 p-8 text-base ring-1 ring-slate-200/70">{{ $page['empty'] }}</p>
            </div>
        </section>
    @else
        {{-- 1. Sorotan: penghargaan terbaru + ringkasan angka --}}
        <section class="section-y bg-white" aria-labelledby="featured-title">
            <div class="container">
                <x-ui.reveal>
                    <article class="relative isolate overflow-hidden rounded-[2rem] bg-brand-950 text-white shadow-card-hover">
                        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand-800 via-brand-950 to-brand-950"></div>
                        <div class="absolute inset-0 -z-10 opacity-50 [background-image:radial-gradient(rgba(255,255,255,0.14)_1px,transparent_1px)] [background-size:26px_26px] [mask-image:radial-gradient(ellipse_at_80%_20%,#000,transparent_70%)]"></div>
                        <div class="absolute -left-24 -top-24 -z-10 h-80 w-80 rounded-full bg-brand-600/30 blur-3xl"></div>
                        <div class="absolute -bottom-32 right-1/3 -z-10 h-72 w-72 rounded-full bg-accent-500/15 blur-3xl"></div>

                        {{-- Tahun raksasa sebagai dekorasi --}}
                        <span class="pointer-events-none absolute -bottom-10 right-6 -z-10 select-none text-[9rem] font-extrabold leading-none tracking-tighter text-white/[0.04] md:text-[16rem]" aria-hidden="true">{{ $featured['year'] }}</span>

                        <div class="grid items-center gap-10 p-8 md:p-12 lg:grid-cols-12 lg:gap-14 lg:p-16">
                            <div class="lg:col-span-7">
                                <p class="inline-flex items-center gap-2.5 rounded-full bg-white/10 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-accent-400 ring-1 ring-white/15 backdrop-blur">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4zM17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/></svg>
                                    {{ $page['featured_label'] }} · {{ $featured['year'] }}
                                </p>
                                <h2 id="featured-title" class="mt-6 text-3xl font-extrabold leading-tight text-white md:text-5xl">{{ $featured['title'] }}</h2>
                                <p class="mt-5 max-w-xl text-base leading-relaxed text-brand-100 md:text-lg">{{ $featured['text'] }}</p>

                            </div>

                            {{-- Piagam / foto penghargaan, dibingkai miring tipis --}}
                            <div class="lg:col-span-5">
                                <div class="relative mx-auto max-w-md lg:max-w-none">
                                    <div class="absolute inset-0 translate-x-3 translate-y-3 rotate-2 rounded-3xl bg-gradient-to-br from-accent-400/40 to-brand-500/30" aria-hidden="true"></div>
                                    <x-ui.img :src="$featured['image']" :alt="$featured['image_alt'] ?? $featured['title']" :label="$featured['title']" :lazy="false"
                                              class="relative aspect-[4/3] w-full rounded-3xl border-4 border-white/90 object-cover shadow-2xl" />
                                </div>
                            </div>
                        </div>
                    </article>
                </x-ui.reveal>

            </div>
        </section>

        {{-- 2. Semua penghargaan: filter tahun + kartu --}}
        @if ($rest->isNotEmpty())
            <section class="section-y bg-slate-50" aria-labelledby="all-title" x-data="{ year: 'all' }">
                <div class="container">
                    <x-ui.reveal>
                        <div class="flex flex-wrap items-end justify-between gap-6">
                            <x-ui.section-title id="all-title" :eyebrow="$page['eyebrow']" :title="$page['all_title']" :text="$page['all_text']" />

                            <div class="flex flex-wrap gap-2" role="group" aria-label="{{ $page['all_title'] }}">
                                <button type="button" @click="year = 'all'"
                                        :class="year === 'all' ? 'bg-brand-700 text-white border-brand-700' : 'bg-white text-slate-600 border-slate-200 hover:border-brand-300 hover:text-brand-700'"
                                        class="rounded-full border px-4 py-1.5 text-sm font-semibold transition">{{ $page['filter_all'] }}</button>
                                @foreach ($years as $y)
                                    <button type="button" @click="year = '{{ $y }}'"
                                            :class="year === '{{ $y }}' ? 'bg-brand-700 text-white border-brand-700' : 'bg-white text-slate-600 border-slate-200 hover:border-brand-300 hover:text-brand-700'"
                                            class="rounded-full border px-4 py-1.5 text-sm font-semibold transition">{{ $y }}</button>
                                @endforeach
                            </div>
                        </div>
                    </x-ui.reveal>

                    <ul class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($rest as $award)
                            <li x-show="year === 'all' || year === '{{ $award['year'] }}'" x-transition.opacity.duration.200ms>
                                <x-ui.reveal :delay="($loop->index % 3) * 100" class="h-full">
                                    <article class="group relative flex h-full flex-col overflow-hidden rounded-3xl bg-white shadow-card ring-1 ring-slate-200/70 transition duration-500 hover:-translate-y-1.5 hover:shadow-card-hover">
                                        <span class="pointer-events-none absolute inset-x-0 top-0 z-10 h-1 origin-left scale-x-0 bg-gradient-to-r from-brand-600 to-accent-500 transition duration-500 group-hover:scale-x-100" aria-hidden="true"></span>

                                        <div class="relative overflow-hidden">
                                            <x-ui.img :src="$award['image']" :alt="$award['image_alt'] ?? $award['title']" :label="$award['title']"
                                                      class="aspect-[4/3] w-full object-cover transition duration-700 group-hover:scale-105" />
                                            <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-brand-800 shadow-sm backdrop-blur">{{ $award['year'] }}</span>
                                        </div>

                                        <div class="relative flex flex-1 flex-col p-6 pt-9">
                                            <span class="absolute right-5 top-0 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 text-accent-400 shadow-lg shadow-brand-700/30 ring-4 ring-white transition duration-300 group-hover:rotate-6" aria-hidden="true">
                                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4zM17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/></svg>
                                            </span>
                                            <h3 class="text-lg leading-snug transition group-hover:text-brand-700">{{ $award['title'] }}</h3>
                                            <p class="mt-2 flex-1 text-sm leading-relaxed">{{ $award['text'] }}</p>
                                        </div>
                                    </article>
                                </x-ui.reveal>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif
    @endif

    <x-ui.cta-band />
@endsection