<?php

use App\Support\Site;

if (! function_exists('lroute')) {
    /**
     * URL halaman publik sesuai bahasa aktif (atau bahasa yang ditentukan).
     *
     * lroute('about.profile')
     * lroute('units.show', ['slug' => 'software'])
     * lroute('home', [], 'en')
     */
    function lroute(string $key, array $params = [], ?string $locale = null): string
    {
        return Site::url($key, $params, $locale);
    }
}

if (! function_exists('switch_url')) {
    /**
     * URL halaman yang sedang dibuka, dalam bahasa lain (untuk tombol ID | EN).
     * $withQuery = false untuk <link rel="alternate" hreflang>: alamat bahasa lain tanpa query string.
     */
    function switch_url(string $locale, bool $withQuery = true): string
    {
        return Site::switchUrl($locale, $withQuery);
    }
}

if (! function_exists('t')) {
    /**
     * Label antarmuka dari App\Support\Labels sesuai bahasa aktif.
     *
     * t('nav.contact_us')  => "Hubungi Kami" (ID) / "Contact Us" (EN)
     */
    function t(string $key): string
    {
        return \App\Support\Labels::get($key);
    }
}

if (! function_exists('logo_url')) {
    /**
     * URL logo PNG yang ruang kosongnya sudah dipangkas otomatis (lihat App\Support\LogoTrim).
     *
     * logo_url('img/logo/decaaid-black.png')
     */
    function logo_url(string $path): string
    {
        return \App\Support\LogoTrim::url($path);
    }
}