@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $l = \App\Support\Content::about()['legal'];
    $email = \App\Support\Content::company()['email']['general'];
@endphp

@section('title', $l['title'].' | '.config('site.name'))
@section('description', $l['lead'])

@section('content')
    <x-ui.page-header :eyebrow="$l['eyebrow']" :title="$l['title']" :text="$l['lead']"
        :crumbs="[[$ui['home'], lroute('home')], [$l['eyebrow'], lroute('about.profile')], [$l['title'], null]]" />

    @include('partials.about-nav')

    {{-- Daftar dokumen (DUMMY) ------------------------------------------------- --}}
    <section class="section-y bg-white" aria-label="{{ $l['title'] }}">
        <div class="container">
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($l['items'] as $item)
                    <x-ui.reveal :delay="($loop->index % 3) * 100">
                        <x-ui.card class="flex flex-col p-8">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                                <x-ui.icon name="check" class="h-5 w-5" />
                            </span>
                            <h3 class="mt-5 text-lg">{{ $item['title'] }}</h3>
                            <p class="mt-2 flex-1 text-base leading-relaxed">{{ $item['text'] }}</p>
                            <div class="mt-5">
                                <x-ui.badge tone="neutral">{{ $ui['available_on_request'] }}</x-ui.badge>
                            </div>
                        </x-ui.card>
                    </x-ui.reveal>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Cara meminta dokumen -------------------------------------------------- --}}
    <section class="section-y bg-brand-50" aria-labelledby="request-title">
        <div class="container grid gap-10 lg:grid-cols-12 lg:gap-16">
            <x-ui.reveal class="lg:col-span-5">
                <h2 id="request-title" class="text-3xl leading-tight md:text-4xl">{{ $l['request_title'] }}</h2>
                <p class="mt-4 text-base leading-relaxed md:text-lg">{{ $l['request_text'] }}</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <x-ui.button :href="lroute('contact').'?subject=documents'" icon="arrow-right">{{ $ui['request_docs'] }}</x-ui.button>
                    <x-ui.button :href="'mailto:'.$email" variant="outline">{{ $email }}</x-ui.button>
                </div>
            </x-ui.reveal>

            <ol class="space-y-4 lg:col-span-7">
                @foreach ($l['steps'] as $step)
                    <li>
                        <x-ui.reveal :delay="$loop->index * 100">
                            <div class="flex gap-5 rounded-xl bg-white p-5 shadow-card md:p-6">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-700 text-sm font-bold text-white">{{ $loop->iteration }}</span>
                                <p class="self-center text-base leading-relaxed text-ink">{{ $step }}</p>
                            </div>
                        </x-ui.reveal>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <x-ui.cta-band />
@endsection
