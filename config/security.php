<?php

/*
|--------------------------------------------------------------------------
| Header keamanan (App\Http\Middleware\SecurityHeaders)
|--------------------------------------------------------------------------
| .env produksi: SECURITY_HSTS=true setelah HTTPS dipastikan stabil.
| SECURITY_CSP: off | report (bawaan, hanya melapor di konsol browser) | enforce (memblokir).
*/
return [
    'hsts'         => (bool) env('SECURITY_HSTS', false),
    'hsts_max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 15552000), // 180 hari
    'csp'          => env('SECURITY_CSP', 'report'),
];
