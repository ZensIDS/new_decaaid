<?php
namespace App\Support;

use Carbon\Carbon;
use Throwable;

/**
 * Tahap C (SEO): data terstruktur JSON-LD untuk <head> semua halaman publik.
 *
 *  - Organization (semua halaman): nama, logo, email, telepon, tautan sosial (hanya yang sudah diisi).
 *  - WebSite + SearchAction (beranda): kotak pencarian situs.
 *  - LocalBusiness per kantor (Kontak dan Profil Perusahaan): kantor pusat + cabang dari Pengaturan Situs.
 *  - BreadcrumbList (semua halaman selain beranda).
 *  - Article (detail berita).
 *
 * Data diambil dari sumber yang sama dengan tampilan (Content::company(), NewsRepo), jadi ikut berubah saat
 * admin mengubahnya. Nilai contoh (telepon "0000 0000", tautan sosial "#") tidak dikeluarkan.
 */
class JsonLd
{
    /** Induk breadcrumb untuk halaman detail. */
    protected const PARENT = [
        'news.show'      => 'news.index',
        'news.category'  => 'news.index',
        'portfolio.show' => 'portfolio.index',
        'units.show'     => 'units.index',
        'units.brand'    => 'units.index',
    ];

    public static function render(string $pageTitle, string $description, ?string $image): string
    {
        try {
            $graph = self::graph($pageTitle, $description, $image);
        } catch (Throwable $e) {
            report($e); // data terstruktur tidak boleh merusak halaman

            return '';
        }

        if (! $graph) {
            return '';
        }

        $json = json_encode(
            ['@context' => 'https://schema.org', '@graph' => $graph],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP
        );

        return '<script type="application/ld+json">' . $json . '</script>';
    }

    /** @return array<int, array<string, mixed>> */
    public static function graph(string $pageTitle, string $description, ?string $image): array
    {
        $key    = self::routeKey();
        $locale = app()->getLocale();
        $home   = lroute('home');
        $orgId  = url('/') . '#organization';
        $nodes  = [self::organization($orgId, $home)];

        if ($key === 'home') {
            $nodes[] = array_filter([
                '@type'           => 'WebSite',
                '@id'             => url('/') . '#website',
                'url'             => $home,
                'name'            => config('site.name'),
                'inLanguage'      => $locale,
                'publisher'       => ['@id' => $orgId],
                'potentialAction' => [
                    '@type'       => 'SearchAction',
                    'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => lroute('search') . '?q={search_term_string}'],
                    'query-input' => 'required name=search_term_string',
                ],
            ]);
        }

        if (in_array($key, ['contact', 'about.profile'], true)) {
            foreach (self::offices($orgId) as $office) {
                $nodes[] = $office;
            }
        }

        if ($crumbs = self::breadcrumbs($key, $pageTitle)) {
            $nodes[] = $crumbs;
        }

        if ($key === 'news.show' && ($article = self::article($orgId, $description, $image))) {
            $nodes[] = $article;
        }

        return $nodes;
    }

    /** Kunci rute halaman yang sedang dibuka ("id.news.show" => "news.show"), atau "custom". */
    protected static function routeKey(): ?string
    {
        $name = request()->route()?->getName();

        return $name && str_contains($name, '.') ? substr($name, strpos($name, '.') + 1) : null;
    }

    protected static function organization(string $orgId, string $home): array
    {
        $company = Content::company();
        $logo    = SeoMeta::imageUrl($company['logo']['on_light'] ?? null);

        $sameAs = [];
        foreach (Content::SITE_SOCIALS as $net) {
            $url = (string) ($company['social'][$net] ?? '');

            if (preg_match('#^https?://#i', $url)) {
                $sameAs[] = $url;
            }
        }

        return array_filter([
            '@type'     => 'Organization',
            '@id'       => $orgId,
            'name'      => config('site.name'),
            'url'       => $home,
            'logo'      => $logo ? ['@type' => 'ImageObject', 'url' => $logo] : null,
            'slogan'    => $company['tagline'] ?? null,
            'email'     => $company['email']['general'] ?? null,
            'telephone' => self::phone($company),
            'sameAs'    => $sameAs ?: null,
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    protected static function offices(string $orgId): array
    {
        $company = Content::company();
        $out     = [];

        foreach ($company['offices'] ?? [] as $office) {
            $out[] = array_filter([
                '@type'          => 'LocalBusiness',
                '@id'            => url('/') . '#office-' . ($office['key'] ?? md5((string) $office['city'])),
                'name'           => config('site.name') . ' ' . $office['city'],
                'description'    => $office['type'] ?? null,
                'parentOrganization' => ['@id' => $orgId],
                'url'            => lroute('contact'),
                'email'          => $company['email']['general'] ?? null,
                'telephone'      => self::phone($company),
                'address'        => [
                    '@type'           => 'PostalAddress',
                    'streetAddress'   => $office['address'],
                    'addressLocality' => $office['city'],
                    'addressCountry'  => 'ID',
                ],
            ]);
        }

        return $out;
    }

    protected static function breadcrumbs(?string $key, string $pageTitle): ?array
    {
        if ($key === null || $key === 'home') {
            return null;
        }

        $ui    = Content::ui();
        $items = [['name' => $ui['home'], 'url' => lroute('home')]];
        $names = self::navLabels();
        $slug  = (string) request()->route()?->parameter('slug');

        // Induk: daftar (Berita, Portofolio, Pilar Bisnis); untuk brand juga pilar induknya.
        if ($parent = self::PARENT[$key] ?? null) {
            if (isset($names[$parent])) {
                $items[] = ['name' => $names[$parent], 'url' => lroute($parent)];
            }

            if ($key === 'units.brand' && ($unit = Content::unit($slug))) {
                $items[] = ['name' => $unit['name'], 'url' => lroute('units.show', ['slug' => $slug])];
            }
        }

        $current = trim((string) preg_replace('/\s*\|\s*' . preg_quote((string) config('site.name'), '/') . '\s*$/u', '', $pageTitle));
        $items[] = ['name' => $current !== '' ? $current : ($names[$key] ?? config('site.name')), 'url' => url()->current()];

        return [
            '@type'           => 'BreadcrumbList',
            'itemListElement' => array_map(fn(array $i, int $n) => [
                '@type'    => 'ListItem',
                'position' => $n + 1,
                'name'     => $i['name'],
                'item'     => $i['url'],
            ], $items, array_keys($items)),
        ];
    }

    protected static function article(string $orgId, string $description, ?string $image): ?array
    {
        $post = Content::post((string) request()->route()?->parameter('slug'));

        if (! $post) {
            return null;
        }

        try {
            $date = Carbon::parse($post['date'])->toIso8601String();
        } catch (Throwable) {
            $date = null;
        }

        $headline = (string) ($post['title'] ?? '');
        $picture  = SeoMeta::imageUrl($post['image'] ?? null) ?: $image;

        return array_filter([
            '@type'            => 'Article',
            'headline'         => mb_substr($headline, 0, 110),
            'description'      => $description !== '' ? $description : ($post['excerpt'] ?? null),
            'image'            => $picture ? [$picture] : null,
            'datePublished'    => $date,
            'dateModified'     => $date,
            'inLanguage'       => app()->getLocale(),
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => url()->current()],
            'author'           => ['@id' => $orgId],
            'publisher'        => ['@id' => $orgId],
        ]);
    }

    /** Telepon hanya bila sudah diisi asli (bukan nomor contoh "0000 0000"). */
    protected static function phone(array $company): ?string
    {
        $phone = trim((string) ($company['phone'] ?? ''));

        return $phone !== '' && ! preg_match('/0000\s?0000/', $phone) ? $phone : null;
    }

    /** @return array<string, string> kunci rute => label menu (bahasa aktif) */
    protected static function navLabels(): array
    {
        $map  = [];
        $walk = function (array $items) use (&$map, &$walk) {
            foreach ($items as $item) {
                if (isset($item['route'], $item['label']) && empty($item['params']) && ! isset($map[$item['route']])) {
                    $map[$item['route']] = $item['label'];
                }

                if (! empty($item['children'])) {
                    $walk($item['children']);
                }
            }
        };

        $walk(Content::nav());

        return $map;
    }
}
