@extends('layouts.app')

@php
    // Halaman 404 tidak melewati middleware bahasa, jadi bahasa ditentukan dari URL.
    $loc = request()->segment(1) === 'en' ? 'en' : 'id';
    app()->setLocale($loc);
    \Carbon\Carbon::setLocale($loc);
    $en = $loc === 'en';
@endphp

@section('title', ($en ? 'Page not found' : 'Halaman tidak ditemukan').' | '.config('site.name'))

@section('content')
    <section class="section-y bg-white">
        <div class="container">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-7xl font-extrabold text-brand-700 md:text-8xl">404</p>
                <h1 class="mt-4 text-3xl md:text-4xl">{{ $en ? 'Page not found' : 'Halaman tidak ditemukan' }}</h1>
                <p class="mt-4 text-base leading-relaxed md:text-lg">
                    {{ $en ? 'The page you are looking for may have been moved or no longer exists.' : 'Halaman yang Anda cari mungkin telah dipindahkan atau tidak lagi tersedia.' }}
                </p>

                <form method="get" action="{{ lroute('search', [], $loc) }}" role="search" class="mx-auto mt-8 flex max-w-md gap-3">
                    <label for="nf-q" class="sr-only">{{ $en ? 'Search' : 'Cari' }}</label>
                    <input id="nf-q" type="search" name="q" maxlength="100" placeholder="{{ $en ? 'Search the site' : 'Cari di situs' }}"
                           class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-ink focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30">
                    <x-ui.button type="submit" icon="search">{{ $en ? 'Search' : 'Cari' }}</x-ui.button>
                </form>

                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <x-ui.button :href="lroute('home', [], $loc)" icon="arrow-right">{{ $en ? 'Back to home' : 'Kembali ke beranda' }}</x-ui.button>
                    <x-ui.button :href="lroute('contact', [], $loc)" variant="outline">{{ $en ? 'Contact us' : 'Hubungi kami' }}</x-ui.button>
                </div>
            </div>
        </div>
    </section>
@endsection