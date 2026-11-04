<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/admin';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Admin (BA1): /admin/*
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Tahap C: pembatas bernama, masing-masing punya hitungan sendiri. (Pembatas numerik `throttle:N,M`
        // di Laravel 9 berbagi satu hitungan per IP/pengguna di semua rute, sehingga rute dengan batas kecil
        // bisa terkunci oleh rute lain.)
        RateLimiter::for('site-search', fn(Request $request) => Limit::perMinute(30)->by('search|'.$request->ip()));
        RateLimiter::for('contact-form', fn(Request $request) => Limit::perMinutes(10, 6)->by('contact|'.$request->ip()));
        RateLimiter::for('seo-files', fn(Request $request) => Limit::perMinute(30)->by('seo|'.$request->ip()));
        foreach (['admin-news-upload' => 60, 'admin-picker-upload' => 30, 'admin-media-upload' => 20, 'admin-export' => 10, 'admin-import' => 10, 'admin-client-bulk' => 20, 'admin-sort' => 60] as $name => $max) {
            RateLimiter::for($name, fn(Request $request) => Limit::perMinute($max)->by($name.'|'.($request->user()?->id ?: $request->ip())));
        }
        RateLimiter::for('admin-forgot', fn(Request $request) => Limit::perMinutes(10, 5)->by('forgot|'.$request->ip()));
        RateLimiter::for('admin-reset', fn(Request $request) => Limit::perMinutes(10, 10)->by('reset|'.$request->ip()));
        RateLimiter::for('admin-2fa', fn(Request $request) => Limit::perMinute(10)->by('2fa|'.$request->ip()));
        RateLimiter::for('admin-password', fn(Request $request) => Limit::perMinute(6)->by('pw|'.($request->user()?->id ?: $request->ip())));
    }
}