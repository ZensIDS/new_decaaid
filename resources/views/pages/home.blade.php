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
    $awards = \App\Support\Content::awardsPage();
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
                @if (! empty($home['hero']['eyebrow']))
                    <p class="animate-fade-up inline-flex items-center gap-2.5 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-brand-100 backdrop-blur">
                        <span class="h-1.5 w-1.5 rounded-full bg-accent-500"></span>
                        {{ $home['hero']['eyebrow'] }}
                    </p>
                @endif
                <h1 id="hero-title"
                    class="animate-fade-up capitalize @if (! empty($home['hero']['eyebrow'])) mt-7 @endif text-4xl font-extrabold leading-[1.08] text-white sm:text-5xl lg:text-[3.5rem]"
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

            {{-- Semua brand dari semua pilar, 5 per baris (desktop). Logo yang belum ada tampil placeholder. --}}
            <ul class="mt-10 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5 lg:gap-5">
                @foreach ($units as $unit)
                    @foreach ($unit['brands'] as $brand)
                        <li>
                            <x-ui.reveal :delay="($loop->parent->index + $loop->index) % 5 * 70" class="h-full">
                                <a href="{{ lroute('units.brand', ['slug' => $unit['slug'], 'brand' => $brand['slug']]) }}"
                                   class="group flex h-full flex-col items-center gap-4 rounded-2xl bg-white p-5 text-center shadow-card ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover hover:ring-brand-200">
                                    <x-ui.img :src="$brand['logo']" :alt="$brand['name']" :label="$brand['name']"
                                              class="h-20 w-full rounded-lg object-contain" />
                                    <span class="text-sm font-semibold leading-snug text-ink transition group-hover:text-brand-700">{{ $brand['name'] }}</span>
                                </a>
                            </x-ui.reveal>
                        </li>
                    @endforeach
                @endforeach
            </ul>

            <x-ui.reveal class="mt-10 flex justify-center">
                <x-ui.button :href="lroute('units.index')" variant="outline" icon="arrow-right">{{ t('nav.all_units') }}</x-ui.button>
            </x-ui.reveal>
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

    {{-- 7b. PENGHARGAAN: carousel kartu (4 per tampilan di desktop, 2 di tablet, 1 di ponsel). Data: Content::awardsPage() --}}
    @if (! empty($awards['items']))
        <section class="relative isolate overflow-hidden bg-brand-950 py-12 text-white md:py-16" aria-labelledby="awards-title"
                 x-data="{ i: 0, pv: 4, n: {{ count($awards['items']) }}, timer: null,
                           get max() { return Math.max(0, this.n - this.pv) },
                           calc() { this.pv = window.innerWidth >= 1024 ? 4 : (window.innerWidth >= 640 ? 2 : 1); if (this.i > this.max) this.i = this.max },
                           next() { this.i = this.i >= this.max ? 0 : this.i + 1 },
                           prev() { this.i = this.i <= 0 ? this.max : this.i - 1 },
                           start() { if (this.max > 0 && ! window.matchMedia('(prefers-reduced-motion: reduce)').matches) { this.stop(); this.timer = setInterval(() => this.next(), 4500) } },
                           stop() { clearInterval(this.timer) } }"
                 x-init="calc(); start()" @resize.window.debounce.150ms="calc()"
                 @mouseenter="stop()" @mouseleave="start()" @focusin="stop()" @focusout="start()">
            <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand-900 via-brand-950 to-brand-950"></div>
            <div class="absolute inset-0 -z-10 opacity-50 [background-image:radial-gradient(rgba(255,255,255,0.14)_1px,transparent_1px)] [background-size:26px_26px] [mask-image:radial-gradient(ellipse_at_85%_15%,#000,transparent_70%)]"></div>
            <div class="absolute -left-28 top-0 -z-10 h-72 w-72 rounded-full bg-brand-600/30 blur-3xl"></div>
            <div class="absolute -bottom-28 right-1/4 -z-10 h-64 w-64 rounded-full bg-accent-500/10 blur-3xl"></div>

            <div class="container">
                <x-ui.reveal>
                    <div class="flex flex-wrap items-end justify-between gap-5">
                        <div class="max-w-xl">
                            <p class="eyebrow inline-flex items-center gap-3 text-accent-400">
                                <span class="h-0.5 w-8 rounded-full bg-accent-500"></span>{{ $awards['eyebrow'] }}
                            </p>
                            <h2 id="awards-title" class="mt-3 text-2xl leading-tight text-white md:text-3xl">{{ $awards['title'] }}</h2>
                        </div>

                        <div class="flex items-center gap-3">
                            <x-ui.button :href="lroute('awards')" variant="outline-light" size="sm" icon="arrow-right" class="hidden sm:inline-flex">{{ $awards['view_all'] }}</x-ui.button>
                            <button type="button" @click="prev()" aria-label="{{ $awards['slide_prev'] }}"
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white ring-1 ring-white/20 transition hover:bg-white hover:text-brand-800">
                                <x-ui.icon name="chevron-left" class="h-5 w-5" />
                            </button>
                            <button type="button" @click="next()" aria-label="{{ $awards['slide_next'] }}"
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white ring-1 ring-white/20 transition hover:bg-white hover:text-brand-800">
                                <x-ui.icon name="chevron-right" class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </x-ui.reveal>

                <x-ui.reveal :delay="100">
                    {{-- Jendela geser: -mx/px menjaga bayangan dan jarak antar kartu --}}
                    <div class="-mx-2.5 mt-8 overflow-hidden py-2">
                        <ul class="flex transition-transform duration-700 ease-out" :style="`transform: translateX(-${i * 100 / pv}%)`">
                            @foreach ($awards['items'] as $award)
                                <li class="shrink-0 px-2.5" :style="`width: ${100 / pv}%`">
                                    <a href="{{ lroute('awards') }}"
                                       class="group flex h-full flex-col overflow-hidden rounded-2xl bg-white/[0.06] ring-1 ring-white/10 backdrop-blur transition duration-300 hover:-translate-y-1 hover:bg-white/[0.1] hover:ring-white/25">
                                        <div class="relative overflow-hidden">
                                            <x-ui.img :src="$award['image']" :alt="$award['title']" :label="$award['title']"
                                                      class="aspect-[4/3] w-full object-cover transition duration-700 group-hover:scale-105" />
                                            <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white/90 px-2.5 py-1 text-xs font-bold text-brand-800 shadow-sm backdrop-blur">
                                                <svg class="h-3.5 w-3.5 text-accent-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4zM17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/></svg>
                                                {{ $award['year'] }}
                                            </span>
                                        </div>
                                        <div class="flex flex-1 flex-col p-4">
                                            <h3 class="line-clamp-2 text-base font-bold leading-snug text-white">{{ $award['title'] }}</h3>
                                            <p class="mt-1.5 line-clamp-2 text-sm leading-relaxed text-brand-100">{{ $award['text'] }}</p>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </x-ui.reveal>

                <div class="mt-5 flex items-center justify-between gap-4" x-show="max > 0">
                    {{-- Titik penunjuk posisi --}}
                    <div class="flex items-center gap-2">
                        <template x-for="d in max + 1" :key="d">
                            <button type="button" @click="i = d - 1" :aria-label="`${d}`"
                                    class="h-2 rounded-full transition-all duration-300"
                                    :class="i === d - 1 ? 'w-7 bg-accent-500' : 'w-2 bg-white/30 hover:bg-white/60'"></button>
                        </template>
                    </div>
                    <x-ui.button :href="lroute('awards')" variant="link" size="sm" icon="arrow-right" class="text-white sm:hidden">{{ $awards['view_all'] }}</x-ui.button>
                </div>
            </div>
        </section>
    @endif

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
                                <x-ui.img :src="$post['image']" :alt="$post['image_alt']" label="Foto berita"
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