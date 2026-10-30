@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $comm = \App\Support\Content::community();
    $page = \App\Support\Content::communityPage();
    $company = \App\Support\Content::company();

    // Ikon per kartu "Fokus kami" (urut sesuai data)
    $focusIcons = ['book-open', 'computer', 'users'];
    $programIcons = ['academic-cap', 'sparkles', 'heart', 'users'];
@endphp

@section('title', $comm['name'].' | '.config('site.name'))
@section('description', $comm['summary'])

@section('content')
    <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$comm['name']" :text="$comm['summary']"
        :crumbs="[[$ui['home'], lroute('home')], [$comm['name'], null]]" />

    {{-- 1. TENTANG KOMUNITAS ---------------------------------------------- --}}
    <section class="section-y bg-white" aria-labelledby="about-comm">
        <div class="container grid items-center gap-14 lg:grid-cols-12 lg:gap-16">
            <x-ui.reveal class="lg:col-span-6">
                <p class="eyebrow">{{ $page['eyebrow'] }}</p>
                <h2 id="about-comm" class="mt-3 text-3xl leading-tight md:text-4xl">{{ $page['about_title'] }}</h2>

                <p class="mt-6 text-base leading-relaxed md:text-lg">{{ $page['about'][0] }}</p>

                @if (isset($page['about'][1]))
                    <blockquote class="mt-6 border-l-4 border-accent-500 bg-slate-50 py-4 pl-5 pr-4 text-base font-medium leading-relaxed text-ink md:text-lg">
                        {{ $page['about'][1] }}
                    </blockquote>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    <x-ui.button :href="lroute('contact').'?subject=community'" icon="arrow-right">{{ $page['join_cta'] }}</x-ui.button>
                    <x-ui.button :href="lroute('news.category', ['slug' => 'community'])" variant="outline">{{ $page['news_cta'] }}</x-ui.button>
                </div>
            </x-ui.reveal>

            {{-- Komposisi foto + logo. DUMMY: ganti foto di public/img/dummy/community-1.jpg dan community-2.jpg --}}
            <x-ui.reveal :delay="150" class="lg:col-span-6">
                <div class="relative mx-auto w-full max-w-xl pb-16 pl-4 pt-4 sm:pl-8 lg:max-w-none">
                    <div class="absolute left-0 top-0 h-[88%] w-[82%] rounded-[2rem] bg-brand-50 ring-1 ring-brand-100"></div>
                    <div class="absolute -left-1 bottom-10 h-24 w-24 text-brand-200 [background-image:radial-gradient(currentColor_1.5px,transparent_1.5px)] [background-size:14px_14px]"></div>

                    <div class="relative ml-auto aspect-[4/3] w-[88%] overflow-hidden rounded-[2rem] shadow-card-hover">
                        <x-ui.img src="img/dummy/community-1.jpg" :alt="$comm['name']" label="Foto kegiatan komunitas (dummy)"
                                  class="h-full w-full object-cover" />
                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-brand-950/40 via-transparent to-transparent"></div>
                    </div>

                    {{-- Kartu logo mengambang --}}
                    <div class="animate-float absolute bottom-0 left-0 flex w-[56%] max-w-[17rem] items-center justify-center rounded-3xl bg-white p-5 shadow-card-hover ring-1 ring-slate-200/70 sm:p-6">
                        <x-ui.img :src="$comm['logo']" :alt="$comm['name']" :label="$comm['name']"
                                  class="max-h-16 w-auto max-w-full object-contain sm:max-h-20" />
                    </div>

                    <div class="absolute right-4 top-0 hidden items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-bold uppercase tracking-widest text-brand-700 shadow-card ring-1 ring-slate-200/70 sm:inline-flex">
                        <x-ui.icon name="heart" class="h-4 w-4 text-accent-500" />
                        CSR &middot; {{ $company['name'] }}
                    </div>
                </div>
            </x-ui.reveal>
        </div>
    </section>

    {{-- 2. FOKUS KAMI ----------------------------------------------------- --}}
    <section class="section-y bg-slate-50" aria-labelledby="pillars-title">
        <div class="container">
            <x-ui.reveal><x-ui.section-title id="pillars-title" :title="$page['pillars_title']" align="center" /></x-ui.reveal>

            <div class="mt-12 grid gap-6 md:grid-cols-3">
                @foreach ($page['pillars'] as $item)
                    <x-ui.reveal :delay="$loop->index * 100" class="h-full">
                        <article class="group relative isolate flex h-full flex-col overflow-hidden rounded-3xl bg-white p-8 shadow-card ring-1 ring-slate-200/60 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover">
                            <span class="absolute -right-3 -top-6 -z-10 select-none text-[8rem] font-extrabold leading-none text-slate-100 transition duration-500 group-hover:text-brand-100"
                                  aria-hidden="true">{{ $loop->iteration }}</span>

                            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 text-white shadow-lg shadow-brand-700/25 transition duration-300 group-hover:scale-105">
                                <x-ui.icon :name="$focusIcons[$loop->index] ?? 'check'" class="h-7 w-7" />
                            </span>
                            <h3 class="mt-7 text-xl">{{ $item['title'] }}</h3>
                            <p class="mt-3 flex-1 text-base leading-relaxed">{{ $item['text'] }}</p>

                            <span class="mt-7 block h-1 w-12 rounded-full bg-accent-500 transition-all duration-500 group-hover:w-24"></span>
                        </article>
                    </x-ui.reveal>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 3. PROGRAM (DUMMY: ganti dengan program asli) ---------------------- --}}
    <section class="relative isolate overflow-hidden bg-brand-950 section-y text-white" aria-labelledby="programs-title">
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand-900 via-brand-950 to-brand-950"></div>
        <div class="absolute inset-0 -z-10 opacity-60 [background-image:radial-gradient(rgba(255,255,255,0.14)_1px,transparent_1px)] [background-size:26px_26px] [mask-image:radial-gradient(ellipse_at_80%_20%,#000,transparent_70%)]"></div>
        <div class="absolute -left-32 top-1/3 -z-10 h-96 w-96 rounded-full bg-brand-600/30 blur-3xl"></div>
        <div class="absolute -bottom-40 right-1/4 -z-10 h-80 w-80 rounded-full bg-accent-500/10 blur-3xl"></div>

        <div class="container">
            <x-ui.reveal>
                <x-ui.section-title id="programs-title" :title="$page['programs_title']" :light="true" />
            </x-ui.reveal>

            <div class="mt-12 grid gap-5 sm:grid-cols-2">
                @foreach ($page['programs'] as $item)
                    <x-ui.reveal :delay="($loop->index % 2) * 100" class="h-full">
                        <article class="group flex h-full gap-5 rounded-3xl bg-white/[0.06] p-6 ring-1 ring-white/10 backdrop-blur transition duration-300 hover:-translate-y-0.5 hover:bg-white/[0.1] hover:ring-white/25 sm:p-8">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-accent-400 ring-1 ring-white/15 transition duration-300 group-hover:bg-accent-500 group-hover:text-white">
                                <x-ui.icon :name="$programIcons[$loop->index] ?? 'check'" class="h-6 w-6" />
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest text-brand-200">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                <h3 class="mt-1.5 text-lg text-white md:text-xl">{{ $item['title'] }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-brand-100 md:text-base">{{ $item['text'] }}</p>
                            </div>
                        </article>
                    </x-ui.reveal>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 4. AJAKAN --------------------------------------------------------- --}}
    <section class="section-y bg-white" aria-labelledby="join-title">
        <div class="container">
            <x-ui.reveal>
                <div class="relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 px-6 py-14 text-center shadow-card-hover md:px-12 md:py-20">
                    <div class="absolute -right-20 -top-24 -z-10 h-80 w-80 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -bottom-28 -left-16 -z-10 h-72 w-72 rounded-full bg-accent-500/25 blur-3xl"></div>
                    <div class="absolute inset-0 -z-10 opacity-40 [background-image:radial-gradient(rgba(255,255,255,0.18)_1px,transparent_1px)] [background-size:24px_24px] [mask-image:radial-gradient(ellipse_at_center,#000,transparent_75%)]"></div>

                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-white ring-1 ring-white/20">
                        <x-ui.icon name="heart" class="h-7 w-7" />
                    </span>
                    <h2 id="join-title" class="mx-auto mt-6 max-w-2xl text-3xl leading-tight text-white md:text-4xl">{{ $page['join_title'] }}</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-brand-100 md:text-lg">{{ $page['join_text'] }}</p>
                    <div class="mt-9 flex flex-wrap justify-center gap-3">
                        <x-ui.button :href="lroute('contact').'?subject=community'" variant="light" size="lg" icon="arrow-right">{{ $page['join_cta'] }}</x-ui.button>
                        <x-ui.button :href="lroute('news.category', ['slug' => 'community'])" variant="outline-light" size="lg">{{ $page['news_cta'] }}</x-ui.button>
                    </div>
                </div>
            </x-ui.reveal>
        </div>
    </section>
@endsection