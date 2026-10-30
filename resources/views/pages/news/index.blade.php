@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $page = \App\Support\Content::newsPage();
    $items = \App\Support\Content::news();
@endphp

@section('title', $page['eyebrow'].' | '.config('site.name'))
@section('description', $page['lead'])

@section('content')
    <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$page['title']" :text="$page['lead']"
        :crumbs="[[$ui['home'], lroute('home')], [$page['eyebrow'], null]]" />

    <section class="section-y bg-white" aria-label="{{ $page['eyebrow'] }}">
        <div class="container">
            @include('partials.news-list', ['items' => $items, 'active' => null, 'withFeatured' => true])
        </div>
    </section>

    <x-ui.cta-band />
@endsection