<?php

/*
|--------------------------------------------------------------------------
| Header keamanan (App\Http\Middleware\SecurityHeaders)
|--------------------------------------------------------------------------
| .env produksi: SECURITY_HSTS=true setelah HTTPS dipastikan stabil.
| SECURITY_CSP: off | report (bawaan, hanya melapor di konsol browser) | enforce (memblokir).
|
| Turnstile (captcha ringan Cloudflare, formulir Kontak): MATI bila kunci kosong. Aktifkan hanya bila spam
| masih lolos: isi TURNSTILE_SITE_KEY dan TURNSTILE_SECRET_KEY di .env, lalu php artisan config:clear.
*/
return [
    'hsts'         => (bool) env('SECURITY_HSTS', false),
    'hsts_max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 15552000), // 180 hari
    'csp'          => env('SECURITY_CSP', 'report'),
    'turnstile'    => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret'   => env('TURNSTILE_SECRET_KEY'),
    ],
];
