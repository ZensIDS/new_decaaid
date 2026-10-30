@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $t = \App\Support\Content::about()['team'];
    $ceo = $t['members'][0];
    $others = array_slice($t['members'], 1, null, true); // kunci asli dipertahankan untuk nomor foto
@endphp

@section('title', $t['title'].' | '.config('site.name'))
@section('description', $t['lead'])

@section('content')
    <x-ui.page-header :eyebrow="$t['eyebrow']" :title="$t['title']" :text="$t['lead']"
        :crumbs="[[$ui['home'], lroute('home')], [$t['eyebrow'], lroute('about.profile')], [$t['title'], null]]" />

    @include('partials.about-nav')

    {{-- DUMMY: nama dan foto contoh. Foto asli: public/img/team/{nomor}.jpg (potret 4:5, latar terang).
         Anggota pertama = CEO (kartu besar sendiri). Jabatan hanya tampil bila diisi. --}}

    {{-- CEO --}}
    <section class="section-y bg-white" aria-label="{{ $ceo['role'] }}">
        <div class="container">
            <x-ui.reveal>
                <article class="grid overflow-hidden rounded-3xl bg-slate-50 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <x-ui.img src="img/team/1.jpg" :alt="$ceo['name']" :label="$ceo['name']"
                                  class="aspect-[4/5] h-full w-full object-cover object-top lg:aspect-auto lg:min-h-[28rem]" />
                    </div>
                    <div class="relative flex flex-col justify-center p-8 md:p-12 lg:col-span-7 lg:p-16">
                        <span class="absolute right-8 top-8 h-24 w-24 rounded-full border border-brand-200" aria-hidden="true"></span>
                        <x-ui.badge class="w-fit">{{ $ceo['role'] }}</x-ui.badge>
                        <h2 class="mt-5 text-3xl md:text-5xl">{{ $ceo['name'] }}</h2>
                        <span class="mt-6 block h-1 w-14 rounded-full bg-accent-500" aria-hidden="true"></span>
                        @if (! empty($ceo['bio']))
                            <p class="mt-6 max-w-xl text-base leading-relaxed md:text-lg">{{ $ceo['bio'] }}</p>
                        @endif
                    </div>
                </article>
            </x-ui.reveal>
        </div>
    </section>

    {{-- Board of Executive --}}
    <section class="bg-white pb-16 md:pb-24" aria-labelledby="board-title">
        <div class="container">
            <x-ui.reveal>
                <h2 id="board-title" class="text-center text-3xl leading-tight md:text-5xl">{{ $t['members_title'] }}</h2>
            </x-ui.reveal>

            {{-- flex + justify-center: baris terakhir yang tidak penuh tetap rata tengah --}}
            <div class="mx-auto mt-12 flex max-w-5xl flex-wrap justify-center gap-8 lg:gap-10">
                @foreach ($others as $index => $member)
                    <x-ui.reveal :delay="($loop->index % 3) * 100" class="w-full sm:w-[calc(50%-1rem)] lg:w-[calc(33.333%-1.67rem)]">
                        <article class="group relative aspect-[4/5] overflow-hidden rounded-2xl bg-slate-100 shadow-card">
                            <x-ui.img :src="'img/team/'.($index + 1).'.jpg'" :alt="$member['name']" :label="$member['name']"
                                      class="h-full w-full object-cover object-top transition duration-500 group-hover:scale-105" />

                            {{-- Gradasi gelap di bagian bawah agar nama terbaca di foto berlatar terang --}}
                            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-2/5 bg-gradient-to-t from-slate-800/90 via-slate-700/60 to-transparent" aria-hidden="true"></div>

                            <div class="absolute inset-x-0 bottom-0 p-6 text-center">
                                <h3 class="text-xl font-semibold leading-snug text-white md:text-2xl">{{ $member['name'] }}</h3>
                                @if (! empty($member['role']))
                                    <p class="mt-2 inline-block rounded-full bg-white/20 px-3 py-0.5 text-sm font-semibold text-white backdrop-blur-sm">{{ $member['role'] }}</p>
                                @endif
                            </div>
                        </article>
                    </x-ui.reveal>
                @endforeach
            </div>
        </div>
    </section>

    <x-ui.cta-band />
@endsection