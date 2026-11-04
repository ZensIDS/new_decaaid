@php
    $company = \App\Support\Content::company();
    // B3n 2b: isian admin (SEO Halaman) > section view > bawaan situs. Section inline sudah ter-escape: dekode dulu agar {{ }} tidak meng-escape dua kali.
    $seo             = \App\Support\SeoMeta::current();
    $pageTitle       = $seo['title'] ?? (html_entity_decode(trim($__env->yieldContent('title')), ENT_QUOTES) ?: config('site.name'));
    $metaDescription = $seo['description'] ?? (html_entity_decode(trim($__env->yieldContent('description')), ENT_QUOTES) ?: $company['tagline']);
    $ogImage         = collect([$seo['image'] ?? null, html_entity_decode(trim($__env->yieldContent('og_image')), ENT_QUOTES), \App\Support\SeoMeta::defaultImage()])
        ->map(fn ($p) => \App\Support\SeoMeta::imageUrl($p))->filter()->first();
    $ogLocales = ['id' => 'id_ID', 'en' => 'en_US'];
    // Tahap C (v64): halaman yang tidak boleh diindeks (hasil pencarian, 404) mengisi section "robots" dengan nilai noindex, follow.
    // Halaman seperti itu tidak punya alamat kanonis maupun padanan bahasa yang berarti, jadi canonical dan hreflang dilewati.
    $robots = trim(html_entity_decode($__env->yieldContent('robots'), ENT_QUOTES));
    // Alamat kanonis tanpa query string, kecuali nomor halaman daftar (?page=2 dst.) yang memang halaman berbeda.
    $pageNo    = (int) request()->query('page', 1);
    $pageQs    = $pageNo > 1 ? '?page=' . $pageNo : '';
    $canonical = url()->current() . $pageQs;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="theme-color" content="#1B3590">
    @unless ($robots !== '')
        <link rel="canonical" href="{{ $canonical }}">
    @endunless

    {{-- Favicon: pilihan admin (Pengaturan Situs), cadangan public/favicon.ico --}}
    @if ($favicon = favicon_url())
        <link rel="icon" href="{{ $favicon }}">
        <link rel="apple-touch-icon" href="{{ $favicon }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @endif

    {{-- Bahasa alternatif (tanpa query string, agar tiap bahasa menunjuk satu alamat bersih) --}}
    @unless ($robots !== '')
        @foreach (config('site.locales') as $loc)
            <link rel="alternate" hreflang="{{ $loc }}" href="{{ switch_url($loc, false) . $pageQs }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ switch_url(config('site.default_locale'), false) . $pageQs }}">
    @endunless

    {{-- Open Graph dan Twitter. Gambar share: halaman (admin) > gambar berita/proyek > bawaan situs (SEO Halaman) --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('site.name') }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:locale" content="{{ $ogLocales[app()->getLocale()] ?? 'id_ID' }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if ($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    @include('partials.analytics')

    {{-- Data terstruktur (Organization, WebSite, LocalBusiness, BreadcrumbList, Article): App\Support\JsonLd --}}
    {!! \App\Support\JsonLd::render($pageTitle, $metaDescription, $ogImage) !!}

    @if (config('site.noindex'))
        <meta name="robots" content="noindex, nofollow">
    @elseif ($robots !== '')
        <meta name="robots" content="{{ $robots }}">
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