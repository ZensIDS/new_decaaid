@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $page = \App\Support\Content::searchPage();
    $q = $q ?? '';
    $groups = \App\Support\Content::search($q);
    $total = collect($groups)->sum(fn ($g) => count($g['items']));
@endphp

@section('title', $page['title'].' | '.config('site.name'))
@section('description', $page['prompt'])

@section('content')
    <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$page['title']"
        :crumbs="[[$ui['home'], lroute('home')], [$page['title'], null]]" />

    <section class="section-y bg-white" aria-label="{{ $page['title'] }}">
        <div class="container">
            <form method="get" action="{{ lroute('search') }}" role="search" class="mx-auto flex max-w-2xl gap-3">
                <label for="search-q" class="sr-only">{{ $page['button'] }}</label>
                <input id="search-q" type="search" name="q" value="{{ $q }}" maxlength="100" placeholder="{{ $page['placeholder'] }}"
                       class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-base text-ink placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30">
                <x-ui.button type="submit" size="lg" icon="search">{{ $page['button'] }}</x-ui.button>
            </form>

            <div class="mx-auto mt-12 max-w-3xl">
                @if (mb_strlen(trim($q)) < 2)
                    <p class="text-center text-base">{{ $page['prompt'] }}</p>
                @elseif ($total === 0)
                    <div class="rounded-xl bg-slate-50 p-8 text-center">
                        <h2 class="text-xl">{{ $page['none_title'] }}</h2>
                        <p class="mt-2 text-base">{{ $page['none_text'] }}</p>
                        <div class="mt-6 flex flex-wrap justify-center gap-3">
                            <x-ui.button :href="lroute('units.index')" variant="outline" size="sm">{{ $page['groups']['units'] }}</x-ui.button>
                            <x-ui.button :href="lroute('portfolio.index')" variant="outline" size="sm">{{ $page['groups']['projects'] }}</x-ui.button>
                            <x-ui.button :href="lroute('news.index')" variant="outline" size="sm">{{ $page['groups']['news'] }}</x-ui.button>
                        </div>
                    </div>
                @else
                    <p class="text-sm font-semibold text-slate-500" role="status">{{ $total }} {{ $page['count'] }} “{{ $q }}”</p>

                    @foreach ($groups as $group)
                        <div class="mt-8">
                            <h2 class="eyebrow">{{ $group['label'] }}</h2>
                            <ul class="mt-3 divide-y divide-slate-200 rounded-xl border border-slate-200">
                                @foreach ($group['items'] as $item)
                                    <li>
                                        <a href="{{ $item['url'] }}" class="block p-5 transition hover:bg-brand-50">
                                            <span class="font-semibold text-brand-700">{{ $item['title'] }}</span>
                                            @if ($item['excerpt'])
                                                <span class="mt-1 block text-sm leading-relaxed">{{ $item['excerpt'] }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>
@endsection