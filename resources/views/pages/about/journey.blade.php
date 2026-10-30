@extends('layouts.app')

{{--
    Perjalanan Kami (template siap isi).
    Semua isi diatur di App\Support\Content::about()['journey'] (lihat petunjuk di sana).
    Tambah / hapus baris 'milestones' dan tampilan menyesuaikan otomatis.
    Foto: public/img/journey/{tahun}.jpg (sesuai kolom 'image').
--}}

@php
    $ui = \App\Support\Content::ui();
    $j = \App\Support\Content::about()['journey'];
    $items = $j['milestones'];
@endphp

@section('title', $j['title'].' | '.config('site.name'))
@section('description', $j['lead'])

@section('content')
    <x-ui.page-header :eyebrow="$j['eyebrow']" :title="$j['title']" :text="$j['lead']"
        :crumbs="[[$ui['home'], lroute('home')], [$j['eyebrow'], lroute('about.profile')], [$j['title'], null]]" />

    @include('partials.about-nav')

    {{-- Pengantar + lompat ke tahun ----------------------------------------------- --}}
    <section class="bg-white pb-6 pt-16 md:pt-24" aria-labelledby="journey-intro">
        <div class="container">
            <x-ui.reveal>
                <div class="mx-auto max-w-3xl text-center">
                    <h2 id="journey-intro" class="text-3xl leading-tight md:text-4xl">{{ $j['intro_title'] }}</h2>
                    <p class="mx-auto mt-5 max-w-2xl text-base leading-relaxed md:text-lg">{{ $j['intro_text'] }}</p>
                </div>
            </x-ui.reveal>

            @if (count($items) > 2)
                <nav aria-label="{{ $ui['jump_to_year'] }}" class="mt-10">
                    <ul class="flex flex-wrap justify-center gap-2">
                        @foreach ($items as $m)
                            <li>
                                <a href="#y{{ \Illuminate\Support\Str::slug($m['year']) }}"
                                   class="inline-flex rounded-full border border-slate-200 px-4 py-1.5 text-sm font-semibold text-slate-600 transition hover:border-brand-700 hover:bg-brand-700 hover:text-white">
                                    {{ $m['year'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif
        </div>
    </section>

    {{-- Lini waktu ---------------------------------------------------------------- --}}
    <section class="section-y bg-white" aria-label="{{ $j['title'] }}">
        <div class="container">
            <ol class="relative mx-auto max-w-5xl">
                {{-- Garis tengah --}}
                <li class="pointer-events-none absolute bottom-0 left-4 top-0 w-px bg-gradient-to-b from-brand-100 via-brand-700 to-accent-500 md:left-1/2" aria-hidden="true"></li>

                @foreach ($items as $m)
                    @php $right = $loop->odd; @endphp
                    <li id="y{{ \Illuminate\Support\Str::slug($m['year']) }}"
                        class="relative pb-14 pl-12 last:pb-0 md:grid md:grid-cols-2 md:gap-x-20 md:pl-0">
                        {{-- Titik pada garis --}}
                        <span class="absolute left-4 top-3 h-4 w-4 -translate-x-1/2 rounded-full bg-white ring-4 ring-brand-700 md:left-1/2" aria-hidden="true"></span>

                        <x-ui.reveal class="{{ $right ? 'md:col-start-1' : 'md:col-start-2' }}">
                            <article class="overflow-hidden rounded-3xl bg-white shadow-card-hover ring-1 ring-slate-100">
                                <x-ui.img :src="$m['image']" :alt="$m['title']" :label="$m['year']"
                                          class="aspect-[16/10] w-full object-cover" />
                                <div class="p-6 md:p-8">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <p class="text-4xl font-extrabold leading-none text-brand-700 md:text-5xl">{{ $m['year'] }}</p>
                                        @if (! empty($m['tag']))
                                            <x-ui.badge tone="accent">{{ $m['tag'] }}</x-ui.badge>
                                        @endif
                                    </div>
                                    <h3 class="mt-5 text-xl md:text-2xl">{{ $m['title'] }}</h3>
                                    <p class="mt-3 text-base leading-relaxed">{{ $m['text'] }}</p>
                                </div>
                            </article>
                        </x-ui.reveal>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Penutup: perjalanan berlanjut --------------------------------------------- --}}
    <section class="bg-white pb-16 md:pb-24" aria-labelledby="ahead-title">
        <div class="container">
            <x-ui.reveal>
                <div class="relative isolate overflow-hidden rounded-3xl bg-brand-900 px-8 py-14 text-center text-white md:px-16 md:py-20">
                    <div class="absolute -left-16 -top-20 -z-10 h-72 w-72 rounded-full bg-brand-700/60 blur-3xl" aria-hidden="true"></div>
                    <div class="absolute -bottom-24 -right-10 -z-10 h-72 w-72 rounded-full border border-white/10" aria-hidden="true"></div>

                    <h2 id="ahead-title" class="mx-auto max-w-2xl text-3xl leading-tight text-white md:text-4xl">{{ $j['ahead_title'] }}</h2>
                    <p class="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-brand-100 md:text-lg">{{ $j['ahead_text'] }}</p>
                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                        <x-ui.button :href="lroute('about.vision')" variant="light" size="lg" icon="arrow-right">{{ $ui['view_vision'] }}</x-ui.button>
                    </div>
                </div>
            </x-ui.reveal>
        </div>
    </section>

    <x-ui.cta-band />
@endsection