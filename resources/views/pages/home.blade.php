@extends('layouts.app')

@section('title', config('site.name').' | '.\App\Support\Content::company()['tagline'])

@php
    $company = \App\Support\Content::company();
    $home = \App\Support\Content::home();
    $units = \App\Support\Content::units();
    $projects = \App\Support\Content::projects();
    $clients = \App\Support\Content::clients(24);
    $posts = \App\Support\Content::posts();
    $community = \App\Support\Content::community();
@endphp

@section('content')

    {{-- 1. HERO ------------------------------------------------------------ --}}
    <section class="relative isolate overflow-hidden bg-brand-950 text-white" aria-labelledby="hero-title">
        {{-- Latar berlapis: gradasi, pola titik, cahaya lembut --}}
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand-900 via-brand-950 to-brand-950"></div>
        <div class="absolute inset-0 -z-10 opacity-70 [background-image:radial-gradient(rgba(255,255,255,0.16)_1px,transparent_1px)] [background-size:28px_28px] [mask-image:radial-gradient(ellipse_at_25%_45%,#000,transparent_72%)]"></div>
        <div class="absolute -right-40 -top-40 -z-10 h-[38rem] w-[38rem] rounded-full bg-brand-600/35 blur-3xl"></div>
        <div class="absolute -bottom-48 left-1/4 -z-10 h-96 w-96 rounded-full bg-accent-500/10 blur-3xl"></div>

        <div class="container grid items-center gap-14 py-20 sm:py-24 lg:min-h-[40rem] lg:grid-cols-12 lg:gap-10 lg:py-28">
            <div class="lg:col-span-6">
                <p class="animate-fade-up inline-flex items-center gap-2.5 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-brand-100 backdrop-blur">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-400"></span>
                    {{ $home['hero']['eyebrow'] }}
                </p>
                <h1 id="hero-title"
                    class="animate-fade-up mt-7 text-4xl font-extrabold leading-[1.08] text-white sm:text-5xl lg:text-[3.5rem]"
                    style="animation-delay: 100ms">
                    {{ $home['hero']['title'] }}
                </h1>
                <div class="animate-fade-up mt-8 h-1 w-16 rounded-full bg-accent-500" style="animation-delay: 180ms"></div>
                <p class="animate-fade-up mt-8 max-w-xl text-lg leading-relaxed text-brand-100" style="animation-delay: 240ms">
                    {{ $home['hero']['text'] }}
                </p>
            </div>

            {{-- Kolase foto. DUMMY: ganti di public/img/dummy/hero.jpg, hero-2.jpg, hero-3.jpg --}}
            <div class="animate-fade-up relative mx-auto h-[24rem] w-full max-w-xl sm:h-[30rem] lg:col-span-6 lg:h-[34rem] lg:max-w-none"
                 style="animation-delay: 250ms">
                <div class="absolute -left-2 top-10 h-28 w-28 rounded-full border border-white/15"></div>
                <div class="absolute bottom-16 right-0 h-3 w-3 rounded-full bg-accent-500"></div>

                <div class="absolute right-0 top-0 h-[78%] w-[64%] overflow-hidden rounded-[2rem] shadow-card-hover ring-1 ring-white/20">
                    <x-ui.img src="img/dummy/hero.jpg" :alt="$company['name']" label="Foto hero (dummy)" :lazy="false"
                              class="h-full w-full object-cover" />
                </div>

                <div class="animate-float absolute bottom-[6%] left-0 h-[50%] w-[44%] overflow-hidden rounded-[2rem] border-4 border-brand-950 shadow-card-hover">
                    <x-ui.img src="img/dummy/hero-2.jpg" alt="" label="Foto kegiatan (dummy)"
                              class="h-full w-full object-cover" />
                </div>

                <div class="absolute bottom-0 right-[8%] h-[28%] w-[30%] overflow-hidden rounded-3xl border-4 border-brand-950 shadow-card-hover">
                    <x-ui.img src="img/dummy/hero-3.jpg" alt="" label="Foto tim (dummy)"
                              class="h-full w-full object-cover" />
                </div>
            </div>
        </div>
    </section>

    {{-- 2. RINGKASAN + ANGKA ------------------------------------------------ --}}
    <section class="section-y bg-white" aria-labelledby="about-title">
        <div class="container grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <x-ui.reveal>
                <p class="eyebrow">{{ $home['about']['eyebrow'] }}</p>
                <h2 id="about-title" class="mt-3 text-3xl leading-tight md:text-4xl">{{ $home['about']['title'] }}</h2>
                <p class="mt-5 text-base leading-relaxed md:text-lg">{{ $home['about']['text'] }}</p>
                <p class="mt-4 text-base leading-relaxed md:text-lg">{{ $home['about']['text2'] }}</p>
                <div class="mt-7">
                    <x-ui.button :href="lroute('about.profile')" variant="outline" icon="arrow-right">
                        {{ t('home.about_more') }}
                    </x-ui.button>
                </div>
            </x-ui.reveal>

            {{-- Komposisi foto. DUMMY: ganti di public/img/dummy/about-1.jpg dan about-2.jpg --}}
            <x-ui.reveal :delay="150">
                <div class="relative mx-auto w-full max-w-xl pb-12 pr-4 pt-4 sm:pr-8 lg:max-w-none">
                    <div class="absolute right-0 top-0 h-[88%] w-[82%] rounded-[2rem] bg-brand-50 ring-1 ring-brand-100"></div>
                    <div class="absolute -right-1 bottom-6 h-24 w-24 text-brand-200 [background-image:radial-gradient(currentColor_1.5px,transparent_1.5px)] [background-size:14px_14px]"></div>

                    <div class="relative ml-auto aspect-[4/3] w-[86%] overflow-hidden rounded-[2rem] shadow-card-hover">
                        <x-ui.img src="img/dummy/about-1.jpg" :alt="$company['name']" label="Foto kantor (dummy)"
                                  class="h-full w-full object-cover" />
                    </div>

                    <div class="absolute bottom-0 left-0 aspect-square w-[42%] overflow-hidden rounded-3xl border-[6px] border-white shadow-card-hover">
                        <x-ui.img src="img/dummy/about-2.jpg" alt="" label="Foto tim (dummy)"
                                  class="h-full w-full object-cover" />
                    </div>
                </div>
            </x-ui.reveal>
        </div>
    </section>

    {{-- 3. UNIT BISNIS ------------------------------------------------------ --}}
    <section class="section-y bg-slate-50" aria-labelledby="units-title">
        <div class="container">
            <x-ui.reveal>
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div class="max-w-2xl">
                        <p class="eyebrow">{{ $home['units']['eyebrow'] }}</p>
                        <h2 id="units-title" class="mt-3 text-3xl leading-tight md:text-4xl">{{ $home['units']['title'] }}</h2>
                        <p class="mt-4 text-base leading-relaxed md:text-lg">{{ $home['units']['text'] }}</p>
                    </div>
                </div>
            </x-ui.reveal>

            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($units as $unit)
                    <x-ui.reveal :delay="($loop->index % 4) * 80" class="h-full">
                        <x-ui.card :href="lroute('units.show', ['slug' => $unit['slug']])" class="group flex flex-col">
                            <x-ui.img :src="$unit['logo']" :alt="$unit['name']" :label="$unit['name']"
                                      class="h-14 w-32 rounded-lg object-contain object-left" />
                            <h3 class="mt-5 text-lg">{{ $unit['name'] }}</h3>
                            <p class="mt-2 flex-1 text-sm leading-relaxed">{{ $unit['summary'] }}</p>
                            <span class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold capitalize text-brand-700">
                                {{ t('ui.read_more') }}
                                <x-ui.icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1" />
                            </span>
                        </x-ui.card>
                    </x-ui.reveal>
                @endforeach

                {{-- Kartu ke-8: ajakan melihat semua unit (melengkapi grid 4x2) --}}
                <x-ui.reveal :delay="240" class="h-full">
                    <a href="{{ lroute('units.index') }}"
                       class="group relative isolate flex h-full min-h-[14rem] flex-col justify-between overflow-hidden rounded-xl bg-gradient-to-br from-brand-700 to-brand-900 p-6 text-white shadow-card transition duration-300 hover:-translate-y-0.5 hover:shadow-card-hover">
                        <span class="absolute -right-10 -top-10 -z-10 h-40 w-40 rounded-full bg-white/10 transition duration-500 group-hover:scale-125"></span>
                        <span class="absolute -bottom-16 -left-10 -z-10 h-40 w-40 rounded-full bg-accent-500/20 blur-2xl"></span>

                        <span class="text-xs font-semibold uppercase tracking-widest text-brand-200">{{ $home['units']['eyebrow'] }}</span>

                        <span class="mt-8 inline-flex items-center justify-between gap-3 rounded-full bg-white py-2 pl-5 pr-2 text-sm font-bold capitalize text-brand-800 shadow-sm">
                            {{ t('nav.all_units') }}
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-700 text-white transition group-hover:translate-x-0.5 group-hover:bg-accent-500">
                                <x-ui.icon name="arrow-right" class="h-4 w-4" />
                            </span>
                        </span>
                    </a>
                </x-ui.reveal>
            </div>
        </div>
    </section>

    {{-- 4. PORTOFOLIO UNGGULAN ---------------------------------------------- --}}
    <section class="section-y bg-white" aria-labelledby="projects-title">
        <div class="container">
            <x-ui.reveal>
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div class="max-w-2xl">
                        <p class="eyebrow">{{ $home['projects']['eyebrow'] }}</p>
                        <h2 id="projects-title" class="mt-3 text-3xl leading-tight md:text-4xl">{{ $home['projects']['title'] }}</h2>
                        <p class="mt-4 text-base leading-relaxed md:text-lg">{{ $home['projects']['text'] }}</p>
                    </div>
                    <x-ui.button :href="lroute('portfolio.index')" variant="outline" icon="arrow-right" class="hidden md:inline-flex">
                        {{ t('home.all_projects') }}
                    </x-ui.button>
                </div>
            </x-ui.reveal>

            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ($projects as $project)
                    <x-ui.reveal :delay="$loop->index * 100" class="h-full">
                        <a href="{{ lroute('portfolio.show', ['slug' => $project['slug']]) }}"
                           class="group block h-full overflow-hidden rounded-xl bg-white shadow-card transition duration-300 hover:-translate-y-0.5 hover:shadow-card-hover">
                            <div class="overflow-hidden">
                                <x-ui.img :src="$project['image']" :alt="$project['title']" label="Foto proyek (dummy)"
                                          class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-105" />
                            </div>
                            <div class="p-6">
                                <x-ui.badge>{{ $project['unit_name'] }}</x-ui.badge>
                                <h3 class="mt-4 text-lg leading-snug">{{ $project['title'] }}</h3>
                                <p class="mt-2 text-sm">{{ $project['client'] }} &middot; {{ $project['year'] }}</p>
                            </div>
                        </a>
                    </x-ui.reveal>
                @endforeach
            </div>

            <div class="mt-8 md:hidden">
                <x-ui.button :href="lroute('portfolio.index')" variant="outline" icon="arrow-right" class="w-full">
                    {{ t('home.all_projects') }}
                </x-ui.button>
            </div>
        </div>
    </section>

    {{-- 5. KLIEN & MITRA ---------------------------------------------------- --}}
    <section class="section-y bg-white" aria-labelledby="clients-title">
        <div class="container">
            <div class="relative isolate overflow-hidden rounded-[2rem] bg-slate-50 px-6 py-12 ring-1 ring-slate-200/70 md:px-12 md:py-16">
                <div class="absolute -left-24 -top-24 -z-10 h-72 w-72 rounded-full bg-brand-100/70 blur-3xl"></div>

                <div class="grid items-center gap-12 lg:grid-cols-12 lg:gap-14">
                    <x-ui.reveal class="lg:col-span-4">
                        <p class="eyebrow">{{ $home['clients']['eyebrow'] }}</p>
                        <h2 id="clients-title" class="mt-3 text-3xl leading-tight md:text-4xl">{{ $home['clients']['title'] }}</h2>

                        <div class="mt-8">
                            <x-ui.button :href="lroute('clients')" variant="outline" icon="arrow-right">
                                {{ t('home.all_clients') }}
                            </x-ui.button>
                        </div>
                    </x-ui.reveal>

                    {{-- DUMMY: nama dan logo klien. Dua baris berjalan berlawanan arah, berhenti saat disorot, statis bila animasi dimatikan --}}
                    @php $rows = array_chunk($clients, (int) ceil(count($clients) / 2)); @endphp
                    <div class="group space-y-4 overflow-hidden [mask-image:linear-gradient(to_right,transparent,#000_10%,#000_90%,transparent)] motion-reduce:overflow-visible lg:col-span-8">
                        @foreach ($rows as $r => $row)
                            <div class="flex w-max animate-marquee group-hover:[animation-play-state:paused] motion-reduce:w-auto motion-reduce:animate-none motion-reduce:justify-center {{ $r % 2 ? '[animation-direction:reverse]' : '' }}">
                                @foreach ([0, 1] as $copy)
                                    <ul class="flex shrink-0 items-center gap-4 pr-4 motion-reduce:flex-wrap motion-reduce:justify-center {{ $copy ? 'motion-reduce:hidden' : '' }}"
                                        @if ($copy) aria-hidden="true" @endif>
                                        @foreach ($row as $client)
                                            <li class="flex h-20 w-44 shrink-0 items-center justify-center rounded-xl bg-white p-3 opacity-70 shadow-sm ring-1 ring-slate-200/80 grayscale transition duration-300 hover:opacity-100 hover:shadow-card hover:grayscale-0">
                                                <x-ui.img :src="$client['logo']" :alt="$copy ? '' : $client['name']" :label="$client['name']"
                                                          class="h-full w-full rounded-lg object-contain" />
                                            </li>
                                        @endforeach
                                    </ul>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 6. KEUNGGULAN / NILAI ------------------------------------------------- --}}
    <section class="section-y bg-brand-900" aria-labelledby="values-title">
        <div class="container">
            <x-ui.reveal>
                <p class="eyebrow text-accent-400">{{ $home['values']['eyebrow'] }}</p>
                <h2 id="values-title" class="mt-3 max-w-2xl text-3xl leading-tight text-white md:text-4xl">{{ $home['values']['title'] }}</h2>
            </x-ui.reveal>

            <div class="mt-12 grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($home['values']['items'] as $item)
                    <x-ui.reveal :delay="$loop->index * 100">
                        <div class="border-t border-white/20 pt-6">
                            <p class="text-sm font-bold tracking-widest text-accent-400">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                            <h3 class="mt-3 text-lg text-white">{{ $item['title'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-brand-200">{{ $item['text'] }}</p>
                        </div>
                    </x-ui.reveal>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 7. KEBERLANJUTAN: KOMUNITAS PEDULI DIGITAL (CSR) ----------------------------------- --}}
    <section class="section-y bg-white" aria-labelledby="community-title">
        <div class="container grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <x-ui.reveal>
                {{-- DUMMY: ganti foto kegiatan di public/img/dummy/community.jpg --}}
                <x-ui.img src="img/dummy/community.jpg" :alt="$community['name']" label="Foto kegiatan komunitas (dummy)"
                          class="aspect-[4/3] w-full rounded-2xl object-cover shadow-card" />
            </x-ui.reveal>

            <x-ui.reveal :delay="120">
                <x-ui.img :src="$community['logo']" :alt="$community['name']" :label="$community['name']"
                          class="h-14 w-auto max-w-[18rem] rounded-lg object-contain object-left sm:h-16" />
                <p class="eyebrow mt-6">{{ t('home.sustainability') }}</p>
                <h2 id="community-title" class="mt-3 text-3xl leading-tight md:text-4xl">{{ $community['name'] }}</h2>
                <p class="mt-4 text-base leading-relaxed md:text-lg">{{ $community['summary'] }}</p>

                <ul class="mt-6 space-y-3">
                    @foreach ($home['community']['points'] as $point)
                        <li class="flex items-start gap-3 text-sm font-semibold text-ink">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700">
                                <x-ui.icon name="check" class="h-3.5 w-3.5" />
                            </span>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>

                <div class="mt-8">
                    <x-ui.button :href="lroute('community')" variant="outline" icon="arrow-right">
                        {{ t('home.community_more') }}
                    </x-ui.button>
                </div>
            </x-ui.reveal>
        </div>
    </section>

    {{-- 8. BERITA TERBARU --------------------------------------------------- --}}
    <section class="section-y bg-slate-50" aria-labelledby="news-title">
        <div class="container">
            <x-ui.reveal>
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div class="max-w-2xl">
                        <p class="eyebrow">{{ $home['news']['eyebrow'] }}</p>
                        <h2 id="news-title" class="mt-3 text-3xl leading-tight md:text-4xl">{{ $home['news']['title'] }}</h2>
                        <p class="mt-4 text-base leading-relaxed md:text-lg">{{ $home['news']['text'] }}</p>
                    </div>
                    <x-ui.button :href="lroute('news.index')" variant="outline" icon="arrow-right" class="hidden md:inline-flex">
                        {{ t('home.all_news') }}
                    </x-ui.button>
                </div>
            </x-ui.reveal>

            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ($posts as $post)
                    <x-ui.reveal :delay="$loop->index * 100" class="h-full">
                        <a href="{{ lroute('news.show', ['slug' => $post['slug']]) }}"
                           class="group block h-full overflow-hidden rounded-xl bg-white shadow-card transition duration-300 hover:-translate-y-0.5 hover:shadow-card-hover">
                            <div class="overflow-hidden">
                                <x-ui.img :src="$post['image']" :alt="$post['title']" label="Foto berita (dummy)"
                                          class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-105" />
                            </div>
                            <div class="p-6">
                                <div class="flex items-center gap-3 text-xs">
                                    <x-ui.badge tone="accent">{{ $post['category'] }}</x-ui.badge>
                                    <time datetime="{{ $post['date'] }}" class="font-semibold text-slate-500">
                                        {{ \Carbon\Carbon::parse($post['date'])->translatedFormat('d F Y') }}
                                    </time>
                                </div>
                                <h3 class="mt-4 text-lg leading-snug">{{ $post['title'] }}</h3>
                                <p class="mt-2 text-sm leading-relaxed">{{ $post['excerpt'] }}</p>
                            </div>
                        </a>
                    </x-ui.reveal>
                @endforeach
            </div>

            <div class="mt-8 md:hidden">
                <x-ui.button :href="lroute('news.index')" variant="outline" icon="arrow-right" class="w-full">
                    {{ t('home.all_news') }}
                </x-ui.button>
            </div>
        </div>
    </section>

    {{-- 9. CTA HUBUNGI KAMI ------------------------------------------------- --}}
    <section class="relative isolate overflow-hidden bg-brand-700 py-16 md:py-20" aria-labelledby="cta-title">
        <div class="absolute -right-24 -top-24 -z-10 h-80 w-80 rounded-full bg-brand-500/40 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-16 -z-10 h-72 w-72 rounded-full bg-brand-900/60 blur-3xl"></div>

        <div class="container">
            <x-ui.reveal>
                <div class="mx-auto max-w-3xl text-center">
                    <h2 id="cta-title" class="text-3xl leading-tight text-white md:text-4xl">{{ $home['cta']['title'] }}</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-brand-100 md:text-lg">{{ $home['cta']['text'] }}</p>
                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                        <x-ui.button :href="lroute('contact')" variant="light" size="lg" icon="arrow-right">
                            {{ t('nav.contact_us') }}
                        </x-ui.button>
                        <x-ui.button :href="'mailto:'.$company['email']['general']" variant="outline-light" size="lg">
                            {{ t('home.cta_email') }}
                        </x-ui.button>
                    </div>
                </div>
            </x-ui.reveal>
        </div>
    </section>
@endsection