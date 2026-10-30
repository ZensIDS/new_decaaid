@extends('layouts.app')

@php
    $category = $category ?? null;
    abort_if(! $category, 404);

    $ui = \App\Support\Content::ui();
    $page = \App\Support\Content::newsPage();
    $items = array_values(array_filter(\App\Support\Content::news(), fn ($p) => $p['category_slug'] === $category['slug']));
@endphp

@section('title', $category['name'].' | '.$page['eyebrow'].' | '.config('site.name'))
@section('description', $page['category_lead'])

@section('content')
    <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$category['name']" :text="$page['category_lead']"
        :crumbs="[[$ui['home'], lroute('home')], [$page['eyebrow'], lroute('news.index')], [$category['name'], null]]" />

    <section class="section-y bg-white" aria-label="{{ $category['name'] }}">
        <div class="container">
            @include('partials.news-list', ['items' => $items, 'active' => $category['slug'], 'withFeatured' => false])
        </div>
    </section>

    <x-ui.cta-band />
@endsection