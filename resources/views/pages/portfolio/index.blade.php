@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $page = \App\Support\Content::portfolioPage();
    $projects = \App\Support\Content::portfolio();
    $units = collect(\App\Support\Content::units())->whereIn('slug', collect($projects)->pluck('unit')->unique())->values();
    $years = collect($projects)->pluck('year')->unique()->sortDesc()->values();
@endphp

@section('title', $page['eyebrow'].' | '.config('site.name'))
@section('description', $page['lead'])

@section('content')
    <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$page['title']" :text="$page['lead']"
        :crumbs="[[$ui['home'], lroute('home')], [$page['eyebrow'], null]]" />

    <section class="section-y bg-white" aria-label="{{ $page['eyebrow'] }}"
             x-data="{ unit: 'all', year: 'all', total: {{ count($projects) }},
                       get shown() { return $refs.grid ? [...$refs.grid.children].filter(el => el.style.display !== 'none').length : this.total } }">
        <div class="container">
            {{-- Filter --}}
            <div class="flex flex-wrap items-end gap-4 rounded-xl bg-slate-50 p-5">
                <div>
                    <label for="f-unit" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $page['filter_unit'] }}</label>
                    <select id="f-unit" x-model="unit"
                            class="mt-1 w-full min-w-[14rem] rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink focus:border-brand-700 focus:ring-brand-700">
                        <option value="all">{{ $page['all_units'] }}</option>
                        @foreach ($units as $u)
                            <option value="{{ $u['slug'] }}">{{ $u['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="f-year" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $page['filter_year'] }}</label>
                    <select id="f-year" x-model="year"
                            class="mt-1 w-full min-w-[10rem] rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink focus:border-brand-700 focus:ring-brand-700">
                        <option value="all">{{ $page['all_years'] }}</option>
                        @foreach ($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" x-show="unit !== 'all' || year !== 'all'" x-cloak @click="unit = 'all'; year = 'all'"
                        class="pb-2.5 text-sm font-semibold text-brand-700 underline-offset-4 hover:underline">
                    {{ $page['reset'] }}
                </button>
            </div>

            {{-- Daftar --}}
            <div x-ref="grid" class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    <div x-show="(unit === 'all' || unit === '{{ $project['unit'] }}') && (year === 'all' || year === '{{ $project['year'] }}')"
                         class="h-full">
                        <a href="{{ lroute('portfolio.show', ['slug' => $project['slug']]) }}"
                           class="block h-full overflow-hidden rounded-xl bg-white shadow-card transition duration-300 hover:-translate-y-0.5 hover:shadow-card-hover">
                            <x-ui.img :src="$project['image']" :alt="$project['title']" :label="$project['title']" class="aspect-[16/10] w-full object-cover" />
                            <div class="p-6">
                                <x-ui.badge>{{ $project['unit_name'] }}</x-ui.badge>
                                <h2 class="mt-3 text-lg">{{ $project['title'] }}</h2>
                                <p class="mt-1 text-xs font-semibold text-slate-500">{{ $project['client'] }} · {{ $project['year'] }}</p>
                                <p class="mt-3 text-sm leading-relaxed">{{ $project['summary'] }}</p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <p x-show="$refs.grid && [...$refs.grid.children].every(el => el.style.display === 'none')" x-cloak
               class="mt-8 rounded-xl bg-slate-50 p-8 text-center text-base">{{ $page['empty'] }}</p>
        </div>
    </section>

    <x-ui.cta-band />
@endsection