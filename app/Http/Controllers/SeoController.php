<?php
namespace App\Http\Controllers;

use App\Support\Sitemap;
use Illuminate\Http\Response;

/**
 * B3n: sitemap.xml dan robots.txt dinamis.
 * SITE_NOINDEX=true (staging): robots.txt melarang semua dan sitemap kosong.
 */
class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $xml = config('site.noindex')
            ? '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>' . "\n"
            : Sitemap::xml();

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function robots(): Response
    {
        if (config('site.noindex')) {
            $txt = "User-agent: *\nDisallow: /\n";
        } else {
            $txt = "User-agent: *\nDisallow: /admin\n";

            foreach (config('site.locales') as $locale) {
                $txt .= 'Disallow: /' . $locale . '/' . config('site.routes.search.slug.' . $locale) . "\n";
            }

            $txt .= "\nSitemap: " . url('/sitemap.xml') . "\n";
        }

        return response($txt, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
