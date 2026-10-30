@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $page = \App\Support\Content::privacyPage();
@endphp

@section('title', $page['title'].' | '.config('site.name'))
@section('description', $page['lead'])

@section('content')
    <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$page['title']" :text="$page['lead']"
        :crumbs="[[$ui['home'], lroute('home')], [$page['title'], null]]" />

    <section class="section-y bg-white" aria-label="{{ $page['title'] }}">
        <div class="container grid gap-12 lg:grid-cols-12 lg:gap-16">
            {{-- Daftar isi --}}
            <nav aria-label="{{ $page['toc'] }}" class="lg:col-span-4">
                <div class="rounded-xl bg-slate-50 p-6 lg:sticky lg:top-28">
                    <p class="eyebrow">{{ $page['toc'] }}</p>
                    <ol class="mt-4 space-y-2 text-sm">
                        @foreach ($page['sections'] as $section)
                            <li><a href="#s{{ $loop->iteration }}" class="font-semibold text-slate-600 transition hover:text-brand-700">{{ $loop->iteration }}. {{ $section['title'] }}</a></li>
                        @endforeach
                    </ol>
                </div>
            </nav>

            {{-- Isi --}}
            <div class="lg:col-span-8">
                <p class="text-sm font-semibold text-slate-500">
                    {{ $page['updated_label'] }}:
                    <time datetime="{{ $page['updated'] }}">{{ \Carbon\Carbon::parse($page['updated'])->translatedFormat('d F Y') }}</time>
                </p>

                <div class="mt-6 space-y-10">
                    @foreach ($page['sections'] as $section)
                        <section id="s{{ $loop->iteration }}" class="scroll-mt-28">
                            <h2 class="text-2xl">{{ $loop->iteration }}. {{ $section['title'] }}</h2>
                            <p class="mt-3 text-base leading-8 text-slate-700 md:text-lg">{{ $section['text'] }}</p>
                        </section>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection