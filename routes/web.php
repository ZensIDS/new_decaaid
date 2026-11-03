<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SeoController;
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

// B3n: sitemap XML (ID + EN dengan hreflang) dan robots.txt dinamis (mengikuti SITE_NOINDEX).
// Bila ada berkas statis public/robots.txt atau public/sitemap.xml, hapus agar rute ini yang dipakai.
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');

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

            // B4: kirim formulir Kontak (POST, lewat fetch). Batas kirim: 6 per 10 menit per IP.
            Route::post(config('site.routes.contact.slug.'.$locale), [ContactController::class, 'store'])
                ->middleware('throttle:6,10')
                ->name('contact.send');

            // B3m (QA1): Halaman Kustom. HARUS paling akhir agar tidak menangkap slug halaman bawaan.
            Route::get('{slug}', [PageController::class, 'custom'])
                ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
                ->name('custom');
        });
}