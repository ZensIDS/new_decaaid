<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identitas situs
    |--------------------------------------------------------------------------
    */
    'name' => 'DECAA.ID',

    /*
    |--------------------------------------------------------------------------
    | Bahasa yang didukung
    |--------------------------------------------------------------------------
    | URL publik selalu berprefiks bahasa: /id/... dan /en/...
    */
    'locales' => ['id', 'en'],
    'default_locale' => 'id',
    'locale_labels' => ['id' => 'ID', 'en' => 'EN'],

    /*
    |--------------------------------------------------------------------------
    | Daftar halaman publik
    |--------------------------------------------------------------------------
    | key    : nama halaman. Nama route otomatis menjadi "{locale}.{key}",
    |          contoh: id.about.profile, en.about.profile
    | slug   : path per bahasa (boleh memuat {slug} untuk halaman detail)
    | action : method di App\Http\Controllers\PageController
    |
    | View otomatis dicari di resources/views/pages/{key}.blade.php
    | (titik menjadi folder). Jika view belum ada, tampil halaman placeholder.
    */
    'routes' => [

        'home' => [
            'slug' => ['id' => '', 'en' => ''],
            'action' => 'home',
        ],

        // Tentang Kami
        'about.profile' => [
            'slug' => ['id' => 'tentang-kami/profil-perusahaan', 'en' => 'about-us/company-profile'],
            'action' => 'page',
        ],
        'about.vision' => [
            'slug' => ['id' => 'tentang-kami/visi-misi', 'en' => 'about-us/vision-mission'],
            'action' => 'page',
        ],
        'about.journey' => [
            'slug' => ['id' => 'tentang-kami/perjalanan-kami', 'en' => 'about-us/our-journey'],
            'action' => 'page',
        ],
        'about.team' => [
            'slug' => ['id' => 'tentang-kami/jajaran-kepemimpinan', 'en' => 'about-us/leadership'],
            'action' => 'page',
        ],

        // Pilar Bisnis
        'units.index' => [
            'slug' => ['id' => 'pilar-bisnis', 'en' => 'business-pillars'],
            'action' => 'page',
        ],
        'units.show' => [
            'slug' => ['id' => 'pilar-bisnis/{slug}', 'en' => 'business-pillars/{slug}'],
            'action' => 'unit',
        ],

        // Portofolio
        'portfolio.index' => [
            'slug' => ['id' => 'portofolio', 'en' => 'portfolio'],
            'action' => 'page',
        ],
        'portfolio.show' => [
            'slug' => ['id' => 'portofolio/{slug}', 'en' => 'portfolio/{slug}'],
            'action' => 'project',
        ],
        'clients' => [
            'slug' => ['id' => 'klien-mitra', 'en' => 'clients-partners'],
            'action' => 'page',
        ],

        // Berita
        'news.index' => [
            'slug' => ['id' => 'berita', 'en' => 'news'],
            'action' => 'page',
        ],
        'news.category' => [
            'slug' => ['id' => 'berita/kategori/{slug}', 'en' => 'news/category/{slug}'],
            'action' => 'category',
        ],
        'news.show' => [
            'slug' => ['id' => 'berita/{slug}', 'en' => 'news/{slug}'],
            'action' => 'post',
        ],

        // Komunitas (CSR)
        'community' => [
            'slug' => ['id' => 'keberlanjutan', 'en' => 'sustainability'],
            'action' => 'page',
        ],

        // Lainnya
        'careers' => [
            'slug' => ['id' => 'karir', 'en' => 'careers'],
            'action' => 'page',
        ],
        'contact' => [
            'slug' => ['id' => 'kontak', 'en' => 'contact'],
            'action' => 'page',
        ],
        'search' => [
            'slug' => ['id' => 'cari', 'en' => 'search'],
            'action' => 'search',
        ],
        'privacy' => [
            'slug' => ['id' => 'kebijakan-privasi', 'en' => 'privacy-policy'],
            'action' => 'page',
        ],
    ],

];