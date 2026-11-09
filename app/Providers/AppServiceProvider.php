<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Helper global (lroute, switch_url) tanpa perlu mengubah composer.json
        require_once app_path('Support/Helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->forceCanonicalUrl();
    }

    /**
     * SEO (v107): di produksi, semua alamat yang dibuat aplikasi (canonical, hreflang, og:url, sitemap, robots.txt, JSON-LD)
     * dipaksa memakai APP_URL, supaya tidak ada versi ganda yang dibaca Google sebagai halaman berbeda:
     *  - http:// padahal situs https:// (terjadi bila proxy/CDN meneruskan HTTPS sebagai HTTP ke server);
     *  - www.decaa.id dan decaa.id yang sama-sama bisa dibuka.
     * Aman: hanya aktif bila APP_ENV=production, APP_URL berawalan https:// dan bukan localhost, serta permintaan yang
     * sedang diproses memang datang dari host yang sama dengan APP_URL (www diabaikan); host lain (IP, domain staging) tidak dipaksa.
     * Ini hanya merapikan alamat di dalam halaman; pengalihan www ke non-www tetap sebaiknya diatur di server (lihat docs/PANDUAN-SEO.md).
     */
    protected function forceCanonicalUrl(): void
    {
        if ($this->app->runningInConsole() || ! $this->app->environment('production')) {
            return;
        }

        $root  = rtrim((string) config('app.url'), '/');
        $parts = parse_url($root) ?: [];
        $host  = strtolower((string) ($parts['host'] ?? ''));

        if (($parts['scheme'] ?? '') !== 'https' || $host === '' || in_array($host, ['localhost', '127.0.0.1'], true)) {
            return;
        }

        $bare = fn (string $h) => preg_replace('/^www\./', '', strtolower($h));

        if ($bare((string) $this->app['request']->getHost()) !== $bare($host)) {
            return;
        }

        URL::forceRootUrl($root);
        URL::forceScheme('https');
    }
}