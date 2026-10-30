@php
    $company = \App\Support\Content::company();
    $metaDescription = trim($__env->yieldContent('description')) ?: $company['tagline'];
    $ogLocales = ['id' => 'id_ID', 'en' => 'en_US'];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('site.name'))</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="theme-color" content="#1B3590">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Favicon: taruh file di public/favicon.ico --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

    {{-- Bahasa alternatif --}}
    @foreach (config('site.locales') as $loc)
        <link rel="alternate" hreflang="{{ $loc }}" href="{{ switch_url($loc) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ switch_url(config('site.default_locale')) }}">

    {{-- Open Graph dasar (gambar share otomatis menyusul di tahap C) --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('site.name') }}">
    <meta property="og:title" content="@yield('title', config('site.name'))">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ $ogLocales[app()->getLocale()] ?? 'id_ID' }}">

    @if (config('site.noindex'))
        <meta name="robots" content="noindex, nofollow">
    @endif

    {{-- Tanpa JavaScript: tampilkan konten yang biasanya muncul bertahap saat scroll --}}
    <noscript><style>.reveal{opacity:1!important;transform:none!important}</style></noscript>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main"
       class="sr-only rounded bg-brand-700 px-4 py-2 text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60]">
        {{ t('a11y.skip') }}
    </a>

    @include('partials.header')

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer')

    @stack('scripts')
</body>
</html>