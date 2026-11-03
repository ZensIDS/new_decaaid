<?php
namespace App\Support;

use App\Models\CustomPage;
use Illuminate\Support\Facades\Route;
use Throwable;

/**
 * B3n: daftar URL untuk sitemap.xml. Tiap halaman punya padanan ID dan EN (hreflang saling menunjuk).
 * Sumber: halaman bawaan (config/site.php, tanpa Pencarian), pilar + brand, proyek, berita + kategori,
 * dan halaman kustom yang terbit. Konten yang disembunyikan/draf tidak ikut karena Content::* sudah menyaringnya.
 */
class Sitemap
{
    /** Halaman bawaan yang tidak masuk sitemap (tidak berguna bagi mesin pencari). */
    protected const SKIP = ['search'];

    /** @return array<int, array{key: string, params: array, lastmod: ?string, priority: string}> */
    public static function pages(): array
    {
        $pages = [];

        foreach (array_keys(config('site.routes')) as $key) {
            if (in_array($key, self::SKIP, true) || str_contains(config("site.routes.$key.slug.id"), '{')) {
                continue;
            }

            $pages[] = [
                'key'      => $key,
                'params'   => [],
                'lastmod'  => null,
                'priority' => $key === 'home' ? '1.0' : (in_array($key, ['privacy'], true) ? '0.3' : '0.7'),
            ];
        }

        try {
            foreach (Content::units() as $unit) {
                $pages[] = ['key' => 'units.show', 'params' => ['slug' => $unit['slug']], 'lastmod' => null, 'priority' => '0.7'];

                foreach ($unit['brands'] ?? [] as $brand) {
                    $pages[] = ['key' => 'units.brand', 'params' => ['slug' => $unit['slug'], 'brand' => $brand['slug']], 'lastmod' => null, 'priority' => '0.6'];
                }
            }

            foreach (Content::portfolio() as $project) {
                $pages[] = ['key' => 'portfolio.show', 'params' => ['slug' => $project['slug']], 'lastmod' => null, 'priority' => '0.6'];
            }

            foreach (array_keys(Content::newsCategories()) as $slug) {
                $pages[] = ['key' => 'news.category', 'params' => ['slug' => $slug], 'lastmod' => null, 'priority' => '0.5'];
            }

            foreach (Content::news() as $post) {
                $pages[] = ['key' => 'news.show', 'params' => ['slug' => $post['slug']], 'lastmod' => $post['date'] ?? null, 'priority' => '0.6'];
            }

            if (CustomPageRepo::available()) {
                foreach (CustomPage::published()->orderBy('slug')->get(['slug', 'updated_at']) as $page) {
                    $pages[] = ['key' => 'custom', 'params' => ['slug' => $page->slug], 'lastmod' => $page->updated_at?->format('Y-m-d'), 'priority' => '0.4'];
                }
            }
        } catch (Throwable) {
            // Sumber konten bermasalah: tetap sajikan halaman bawaan saja.
        }

        return $pages;
    }

    /** Teks XML sitemap lengkap. */
    public static function xml(): string
    {
        $locales = config('site.locales');
        $out     = ['<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'];

        foreach (self::pages() as $page) {
            $urls = [];

            foreach ($locales as $locale) {
                if (Route::has($locale . '.' . $page['key'])) {
                    $urls[$locale] = Site::url($page['key'], $page['params'], $locale);
                }
            }

            foreach ($urls as $loc) {
                $out[] = '  <url>';
                $out[] = '    <loc>' . self::e($loc) . '</loc>';

                if ($page['lastmod']) {
                    $out[] = '    <lastmod>' . self::e($page['lastmod']) . '</lastmod>';
                }

                foreach ($urls as $locale => $alt) {
                    $out[] = '    <xhtml:link rel="alternate" hreflang="' . $locale . '" href="' . self::e($alt) . '"/>';
                }

                if (isset($urls[config('site.default_locale')])) {
                    $out[] = '    <xhtml:link rel="alternate" hreflang="x-default" href="' . self::e($urls[config('site.default_locale')]) . '"/>';
                }

                $out[] = '    <priority>' . $page['priority'] . '</priority>';
                $out[] = '  </url>';
            }
        }

        $out[] = '</urlset>';

        return implode("\n", $out) . "\n";
    }

    protected static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
