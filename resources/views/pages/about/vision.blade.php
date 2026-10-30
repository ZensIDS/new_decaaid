@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $v = \App\Support\Content::about()['vision'];
@endphp

@section('title', $v['title'].' | '.config('site.name'))
@section('description', $v['vision'])

@section('content')
    <x-ui.page-header :eyebrow="$v['eyebrow']" :title="$v['title']" :text="$v['lead']"
        :crumbs="[[$ui['home'], lroute('home')], [$v['eyebrow'], lroute('about.profile')], [$v['title'], null]]" />

    @include('partials.about-nav')

    {{-- Visi ------------------------------------------------------------------ --}}
    <section class="section-y bg-white" aria-labelledby="vision-title">
        <div class="container">
            <x-ui.reveal>
                <div class="relative isolate overflow-hidden rounded-3xl bg-brand-900 px-8 py-14 text-white md:px-16 md:py-20">
                    <div class="absolute -right-20 -top-24 -z-10 h-80 w-80 rounded-full bg-brand-700/60 blur-3xl" aria-hidden="true"></div>
                    <div class="absolute -bottom-32 left-1/4 -z-10 h-64 w-64 rounded-full bg-accent-500/10 blur-3xl" aria-hidden="true"></div>
                    <div class="absolute -bottom-24 -right-10 -z-10 h-72 w-72 rounded-full border border-white/10" aria-hidden="true"></div>
                    <div class="absolute -bottom-10 right-10 -z-10 h-40 w-40 rounded-full border border-white/10" aria-hidden="true"></div>

                    <p class="inline-flex items-center gap-3 text-lg font-semibold text-accent-400">
                        <span class="h-0.5 w-8 rounded-full bg-accent-500" aria-hidden="true"></span>
                        {{ $v['vision_label'] }}
                    </p>
                    <h2 id="vision-title" class="mt-5 max-w-4xl text-2xl font-bold leading-snug text-white md:text-4xl md:leading-snug">
                        {{ $v['vision'] }}
                    </h2>
                </div>
            </x-ui.reveal>
        </div>
    </section>

    {{-- Misi ------------------------------------------------------------------ --}}
    <section class="bg-white pb-16 md:pb-24" aria-labelledby="mission-title">
        <div class="container grid gap-10 lg:grid-cols-12 lg:gap-16">
            <x-ui.reveal class="lg:col-span-4">
                <div class="lg:sticky lg:top-28">
                    <h2 id="mission-title" class="text-3xl leading-tight md:text-4xl">{{ $v['mission_label'] }} DECAA.ID</h2>
                    <span class="mt-6 block h-1 w-14 rounded-full bg-accent-500" aria-hidden="true"></span>
                </div>
            </x-ui.reveal>

            <ul class="lg:col-span-8">
                @foreach ($v['missions'] as $mission)
                    <li>
                        <x-ui.reveal :delay="$loop->index * 80">
                            <div class="flex items-start gap-5 border-t border-slate-200 py-6 {{ $loop->last ? 'border-b' : '' }}">
                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-700">
                                    <x-ui.icon name="check" class="h-4 w-4" />
                                </span>
                                <p class="text-base leading-relaxed text-ink md:text-lg">{{ $mission }}</p>
                            </div>
                        </x-ui.reveal>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Nilai: kolom editorial dengan garis pemisah ------------------------------ --}}
    <section class="section-y bg-slate-50" aria-labelledby="values-title">
        <div class="container">
            <x-ui.reveal>
                <x-ui.section-title id="values-title" :title="$v['values_title']" />
            </x-ui.reveal>

            <div class="mt-12 grid gap-x-0 gap-y-10 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($v['values'] as $value)
                    <x-ui.reveal :delay="$loop->index * 80">
                        <div class="h-full lg:px-6 {{ $loop->first ? 'lg:pl-0' : 'lg:border-l lg:border-slate-200' }} {{ $loop->last ? 'lg:pr-0' : '' }}">
                            <span class="block h-0.5 w-8 rounded-full bg-accent-500" aria-hidden="true"></span>
                            <h3 class="mt-4 text-xl text-brand-700">{{ $value['title'] }}</h3>
                            <p class="mt-3 text-sm leading-relaxed md:text-base">{{ $value['text'] }}</p>
                        </div>
                    </x-ui.reveal>
                @endforeach
            </div>
        </div>
    </section>

    <x-ui.cta-band />
@endsection