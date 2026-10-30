@extends('layouts.app')

@php
    $project = $project ?? null;
    abort_if(! $project, 404);

    $ui = \App\Support\Content::ui();
    $page = \App\Support\Content::portfolioPage();
    $others = collect(\App\Support\Content::portfolio())->where('slug', '!=', $project['slug'])
        ->sortByDesc(fn ($p) => $p['unit'] === $project['unit'])->take(3)->values();
@endphp

@section('title', $project['title'].' | '.$page['eyebrow'].' | '.config('site.name'))
@section('description', $project['summary'])

@section('content')
    <x-ui.page-header :eyebrow="$project['unit_name']" :title="$project['title']" :text="$project['summary']"
        :crumbs="[[$ui['home'], lroute('home')], [$page['eyebrow'], lroute('portfolio.index')], [$project['title'], null]]" />

    <section class="section-y bg-white" aria-labelledby="overview-title">
        <div class="container grid gap-12 lg:grid-cols-12 lg:gap-16">
            <div class="lg:col-span-8">
                <x-ui.reveal>
                    {{-- DUMMY: ganti dengan foto proyek asli --}}
                    <x-ui.img :src="$project['image']" :alt="$project['title']" :label="$project['title']" :lazy="false"
                              class="aspect-[16/9] w-full rounded-2xl object-cover shadow-card" />
                </x-ui.reveal>

                <x-ui.reveal class="mt-10">
                    <h2 id="overview-title" class="text-2xl md:text-3xl">{{ $page['overview_title'] }}</h2>
                    <p class="mt-4 text-base leading-relaxed md:text-lg">{{ $project['description'] }}</p>

                    <h2 class="mt-10 text-2xl md:text-3xl">{{ $page['scope_title'] }}</h2>
                    <ul class="mt-4 space-y-3">
                        @foreach ($project['scope'] as $item)
                            <li class="flex gap-3 text-base leading-relaxed">
                                <x-ui.icon name="check" class="mt-1 h-5 w-5 shrink-0 text-brand-700" />
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.reveal>
            </div>

            <aside class="lg:col-span-4">
                <x-ui.reveal :delay="150">
                    <dl class="divide-y divide-slate-200 rounded-xl bg-slate-50 p-6">
                        <div class="pb-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $page['client'] }}</dt>
                            <dd class="mt-1 font-semibold text-ink">{{ $project['client'] }}</dd>
                        </div>
                        <div class="py-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $page['year'] }}</dt>
                            <dd class="mt-1 font-semibold text-ink">{{ $project['year'] }}</dd>
                        </div>
                        <div class="pt-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $page['unit'] }}</dt>
                            <dd class="mt-1">
                                <a href="{{ lroute('units.show', ['slug' => $project['unit']]) }}" class="font-semibold text-brand-700 hover:underline">{{ $project['unit_name'] }}</a>
                            </dd>
                        </div>
                    </dl>
                    <div class="mt-6">
                        <x-ui.button :href="lroute('portfolio.index')" variant="outline" icon="arrow-right">{{ $page['back'] }}</x-ui.button>
                    </div>
                </x-ui.reveal>
            </aside>
        </div>
    </section>

    <section class="section-y bg-slate-50" aria-labelledby="more-title">
        <div class="container">
            <x-ui.reveal>
                <x-ui.section-title id="more-title" :title="$page['more_title']" />
            </x-ui.reveal>

            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ($others as $other)
                    <x-ui.reveal :delay="$loop->index * 100" class="h-full">
                        <a href="{{ lroute('portfolio.show', ['slug' => $other['slug']]) }}"
                           class="block h-full overflow-hidden rounded-xl bg-white shadow-card transition hover:-translate-y-0.5 hover:shadow-card-hover">
                            <x-ui.img :src="$other['image']" :alt="$other['title']" :label="$other['title']" class="aspect-[16/10] w-full object-cover" />
                            <div class="p-6">
                                <p class="text-xs font-semibold text-slate-500">{{ $other['client'] }} · {{ $other['year'] }}</p>
                                <h3 class="mt-2 text-lg">{{ $other['title'] }}</h3>
                            </div>
                        </a>
                    </x-ui.reveal>
                @endforeach
            </div>
        </div>
    </section>

    <x-ui.cta-band />
@endsection