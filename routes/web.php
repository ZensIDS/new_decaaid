<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\HealthController;
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
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->middleware('throttle:seo-files')->name('seo.sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->middleware('throttle:seo-files')->name('seo.robots');

// Tahap E: titik pemeriksaan untuk monitoring uptime (JSON, tanpa detail internal, tetap menjawab saat pemeliharaan).
Route::get('/health', HealthController::class)->middleware('throttle:health')->name('health');

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
                $page = Route::get($route['slug'][$locale], [PageController::class, $route['action']])
                    ->where('slug', '[A-Za-z0-9\-]+')
                    ->name($key);

                // Tahap C: pencarian membaca database, batasi 30 permintaan per menit per IP (pembatas bernama `site-search`).
                if ($key === 'search') {
                    $page->middleware('throttle:site-search');
                }
            }

            // B4: kirim formulir Kontak (POST, lewat fetch). Batas kirim: 6 per 10 menit per IP.
            Route::post(config('site.routes.contact.slug.'.$locale), [ContactController::class, 'store'])
                ->middleware('throttle:contact-form')
                ->name('contact.send');

            // B3m (QA1): Halaman Kustom. HARUS paling akhir agar tidak menangkap slug halaman bawaan.
            Route::get('{slug}', [PageController::class, 'custom'])
                ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
                ->name('custom');
        });
}