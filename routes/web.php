<?php

use App\Http\Controllers\PageController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (situs publik DECAA.ID)
|--------------------------------------------------------------------------
| Semua halaman publik berprefiks bahasa (/id, /en). Daftar halaman dan
| slug per bahasa diatur di config/site.php.
| Nama route: "{locale}.{key}" (contoh: id.about.profile).
| Gunakan helper lroute('about.profile') di view agar mengikuti bahasa aktif.
*/

Route::redirect('/', '/'.config('site.default_locale'));

// A1: halaman internal untuk memeriksa komponen. Hanya aktif di local.
// Hapus blok ini sebelum deploy ke staging.
if (app()->environment('local')) {
    Route::get('/style-guide', fn () => view('pages.style-guide'))
        ->middleware(SetLocale::class.':id')
        ->name('style-guide');
}

foreach (config('site.locales') as $locale) {
    Route::prefix($locale)
        ->middleware(SetLocale::class.':'.$locale)
        ->name($locale.'.')
        ->group(function () use ($locale) {
            foreach (config('site.routes') as $key => $route) {
                Route::get($route['slug'][$locale], [PageController::class, $route['action']])
                    ->where('slug', '[A-Za-z0-9\-]+')
                    ->name($key);
            }
        });
}