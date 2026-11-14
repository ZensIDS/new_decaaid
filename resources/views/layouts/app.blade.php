@php
    $company = \App\Support\Content::company();
    // B3n 2b: isian admin (SEO Halaman) > section view > bawaan situs. Section inline sudah ter-escape: dekode dulu agar {{ }} tidak meng-escape dua kali.
    $seo             = \App\Support\SeoMeta::current();
    $pageTitle       = $seo['title'] ?? (html_entity_decode(trim($__env->yieldContent('title')), ENT_QUOTES) ?: config('site.name'));
    $metaDescription = $seo['description'] ?? (html_entity_decode(trim($__env->yieldContent('description')), ENT_QUOTES) ?: $company['tagline']);
    $ogImage         = collect([$seo['image'] ?? null, html_entity_decode(trim($__env->yieldContent('og_image')), ENT_QUOTES), \App\Support\SeoMeta::defaultImage()])
        ->map(fn ($p) => \App\Support\SeoMeta::imageUrl($p))->filter()->first();
    $ogLocales = ['id' => 'id_ID', 'en' => 'en_US'];
    // v91: deskripsi meta dirapikan (tanpa tag/baris baru/spasi ganda, maks. 300 karakter agar tidak dipotong tak rapi).
    $metaDescription = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) $metaDescription))), 300, '...');
    // v91: jenis Open Graph (bawaan website; detail Berita mengisi section og_type = article + article_published).
    $ogType           = html_entity_decode(trim($__env->yieldContent('og_type')), ENT_QUOTES) ?: 'website';
    $articlePublished = trim($__env->yieldContent('article_published'));
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
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="{{ config('site.name') }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:locale" content="{{ $ogLocales[app()->getLocale()] ?? 'id_ID' }}">
    @foreach ($ogLocales as $loc => $ogLocale)
        @if ($loc !== app()->getLocale())
            <meta property="og:locale:alternate" content="{{ $ogLocale }}">
        @endif
    @endforeach
    @if ($ogType === 'article' && $articlePublished !== '')
        <meta property="article:published_time" content="{{ $articlePublished }}">
    @endif
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

    {{--
        Teks rata kanan-kiri (justify) di situs publik (v109, K105). Dipasang sebagai gaya inline agar langsung berlaku tanpa npm run build
        (CSP style-src mengizinkan 'unsafe-inline'). Prinsip: text-align diwariskan dari <body class="site-justify">, jadi blok yang
        sengaja rata tengah/kanan/kiri lewat class Tailwind (text-center, text-right, text-left, md:text-center) tetap seperti aslinya.
        Judul (h1-h6) tetap rata kiri dan tidak dipenggal suku kata, tetapi ikut rata tengah di dalam blok text-center.
        v110: header dan footer dikecualikan (elemen <header>, <footer>, dan blok bergaya header yang diberi class "no-justify": hero Beranda,
        header halaman dalam, hero artikel, menu mobile); isinya kembali ke rata kiri bawaan, dan blok text-center di dalamnya tetap tengah. Tombol dan kolom isian
        tidak mewarisi text-align (bawaan peramban). Teks satu baris tidak berubah; baris terakhir paragraf tetap rata kiri.
        Dimuat sebelum CSS Vite; aturan di bawah sengaja berspesifisitas rendah (:where) agar utilitas Tailwind selalu menang.
        Admin memakai layout lain, tidak terpengaruh.
    --}}
    <style>
        body.site-justify{text-align:justify;text-justify:inter-word;-webkit-hyphens:auto;hyphens:auto;overflow-wrap:break-word}
        :where(body.site-justify) :where(h1,h2,h3,h4,h5,h6){text-align:start;-webkit-hyphens:manual;hyphens:manual}
        :where(body.site-justify :where(header,footer,.no-justify)){text-align:start;-webkit-hyphens:manual;hyphens:manual}
        body.site-justify .text-center :where(h1,h2,h3,h4,h5,h6){text-align:center}
        @media (min-width:768px){body.site-justify .md\:text-center :where(h1,h2,h3,h4,h5,h6){text-align:center} }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="flex min-h-screen flex-col site-justify">
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