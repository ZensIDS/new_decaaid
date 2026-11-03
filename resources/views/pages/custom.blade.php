@extends('layouts.app')

@php
    // $customPage dikirim PageController::custom() (halaman Terbit saja; selain itu 404).
    $ui    = \App\Support\Content::ui();
    $title = $customPage->t('title');
    $seoTitle = trim($customPage->t('seo_title'));
    $seoDesc  = trim($customPage->t('seo_description'));
    $body  = $customPage->t('body');
@endphp

@section('title', ($seoTitle !== '' ? $seoTitle : $title).' | '.config('site.name'))
@if ($seoDesc !== '')
    @section('description', $seoDesc)
@endif

@section('content')
    <x-ui.page-header :title="$title" :crumbs="[[$ui['home'], lroute('home')], [$title, null]]" />

    <section class="section-y bg-white" aria-label="{{ $title }}">
        <div class="container">
            <div class="mx-auto max-w-3xl">
                {{-- Isi dari editor admin: HTML sudah disaring server (App\Support\Html::clean) --}}
                <div class="news-body space-y-6 text-base leading-8 text-slate-700 md:text-lg [&_a]:font-semibold [&_a]:text-brand-700 [&_a]:underline [&_blockquote]:border-l-4 [&_blockquote]:border-slate-300 [&_blockquote]:pl-5 [&_blockquote]:italic [&_h2]:pt-6 [&_h2]:text-2xl [&_h2]:text-ink md:[&_h2]:text-3xl [&_h3]:pt-4 [&_h3]:text-xl [&_h3]:text-ink [&_img]:h-auto [&_img]:max-w-full [&_img]:rounded-2xl [&_ol]:list-decimal [&_ol]:space-y-2 [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:space-y-2 [&_ul]:pl-6">
                    {!! $body !!}
                </div>
            </div>
        </div>
    </section>
@endsection
