@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $page = \App\Support\Content::clientsPage();
    $categories = \App\Support\Content::clientCategories();
    $portfolio = \App\Support\Content::portfolioPage();

    // Kelompokkan logo per kategori (urutan relatif 1, 2, 3, ... tetap)
    $groups = [];
    foreach (\App\Support\Content::clients() as $client) {
        $groups[$client['category']][] = $client;
    }
    $groups = array_filter(array_replace(array_fill_keys(array_keys($categories), []), $groups));
@endphp

@section('title', $page['title'].' | '.config('site.name'))
@section('description', $page['lead'])

@section('content')
    <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$page['title']" :text="$page['lead']"
        :crumbs="[[$ui['home'], lroute('home')], [$page['eyebrow'], lroute('portfolio.index')], [$page['title'], null]]" />

    <section class="relative isolate overflow-hidden bg-white pb-16 pt-14 md:pb-24 md:pt-20" aria-labelledby="clients-intro"
             x-data="{ cat: 'all' }">
        {{-- Latar lembut --}}
        <div class="absolute inset-x-0 top-0 -z-10 h-96 bg-gradient-to-b from-brand-50/80 to-transparent"></div>
        <div class="absolute -left-32 top-24 -z-10 h-80 w-80 rounded-full bg-brand-100/60 blur-3xl"></div>
        <div class="absolute -right-32 top-[28rem] -z-10 h-80 w-80 rounded-full bg-accent-500/5 blur-3xl"></div>

        <div class="container">
            {{-- Pengantar --}}
            <x-ui.reveal>
                <div class="mx-auto max-w-2xl text-center">
                    <p class="eyebrow inline-flex items-center gap-3">
                        <span class="h-0.5 w-8 rounded-full bg-accent-500"></span>
                        {{ $page['intro_eyebrow'] }}
                        <span class="h-0.5 w-8 rounded-full bg-accent-500"></span>
                    </p>
                    <h2 id="clients-intro" class="mt-4 text-3xl leading-tight md:text-4xl">{{ $page['intro_title'] }}</h2>
                    <p class="mt-4 text-base leading-relaxed md:text-lg">{{ $page['intro_text'] }}</p>
                </div>
            </x-ui.reveal>

            {{-- Filter kategori (menempel di bawah header saat di-scroll) --}}
            <div class="sticky top-20 z-30 -mx-4 mt-10 px-4 py-3 sm:mx-0 sm:px-0">
                <div class="mx-auto w-fit max-w-full">
                    <div class="flex max-w-full gap-1.5 overflow-x-auto rounded-full bg-white/90 p-1.5 shadow-card ring-1 ring-slate-200/70 backdrop-blur [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                         role="group" aria-label="{{ $page['filter_label'] }}">
                        @foreach (['all' => $page['all']] + $categories as $key => $label)
                            <button type="button" @click="cat = '{{ $key }}'" :aria-pressed="cat === '{{ $key }}'"
                                    :class="cat === '{{ $key }}' ? 'bg-brand-700 text-white shadow-sm' : 'text-slate-600 hover:bg-brand-50 hover:text-brand-700'"
                                    class="shrink-0 whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold capitalize transition duration-200 sm:px-5">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Kelompok logo --}}
            <div class="mt-8 space-y-10 md:space-y-14">
                @foreach ($groups as $key => $items)
                    <div x-show="cat === 'all' || cat === '{{ $key }}'"
                         x-transition:enter="transition duration-300 ease-out"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         aria-labelledby="group-{{ $key }}" role="region">
                        <x-ui.reveal>
                            <div class="mb-5 flex items-center gap-4">
                                <span class="h-8 w-1 rounded-full bg-gradient-to-b from-brand-600 to-accent-500"></span>
                                <h3 id="group-{{ $key }}" class="text-xl md:text-2xl">{{ $categories[$key] }}</h3>
                                <span class="h-px flex-1 bg-gradient-to-r from-slate-200 to-transparent"></span>
                            </div>

                            <ul class="grid grid-cols-2 gap-3 rounded-3xl bg-slate-50 p-3 ring-1 ring-slate-200/60 sm:grid-cols-3 sm:gap-4 sm:p-5 md:grid-cols-4 xl:grid-cols-5">
                                @foreach ($items as $client)
                                    <li>
                                        <div class="group flex aspect-[3/2] items-center justify-center rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover hover:ring-brand-200">
                                            <x-ui.img :src="$client['logo']" :alt="$client['name']" :label="$client['name']"
                                                      class="h-full w-full object-contain transition duration-300 [@media(hover:hover)]:opacity-75 [@media(hover:hover)]:grayscale group-hover:opacity-100 group-hover:grayscale-0" />
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </x-ui.reveal>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 flex flex-col items-center gap-6 text-center">
                <p class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-4 py-2 text-sm text-slate-500 ring-1 ring-slate-200/70">
                    <x-ui.icon name="check" class="h-4 w-4 text-brand-700" />
                    {{ $page['note'] }}
                </p>
                <x-ui.button :href="lroute('portfolio.index')" variant="outline" icon="arrow-right">{{ $portfolio['back'] }}</x-ui.button>
            </div>
        </div>
    </section>

    <x-ui.cta-band />
@endsection