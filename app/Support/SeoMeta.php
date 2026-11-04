<?php
namespace App\Support;

/**
 * B3n bagian 2b: SEO per halaman tetap (judul, deskripsi, gambar share) yang diatur admin di /admin/seo-halaman.
 * Disimpan di page_contents (kunci "seo"). Dipakai layouts/app untuk <title>, deskripsi meta, Open Graph, dan Twitter.
 *
 * Prioritas: isian admin untuk halaman itu > section view (judul/deskripsi bawaan halaman atau SEO item berita/proyek) > bawaan situs.
 * Halaman detail (berita, proyek, brand, halaman kustom) tidak ada di sini: mereka punya SEO sendiri.
 */
class SeoMeta
{
    public const PAGE = 'seo';

    /** Kunci rute (config/site.php) => nama di admin. */
    public const PAGES = [
        'home'            => 'Beranda',
        'about.profile'   => 'Profil Perusahaan',
        'about.vision'    => 'Visi & Misi',
        'about.journey'   => 'Perjalanan Kami',
        'units.index'     => 'Pilar Bisnis',
        'portfolio.index' => 'Portofolio',
        'awards'          => 'Penghargaan',
        'clients'         => 'Klien & Mitra',
        'news.index'      => 'Berita',
        'community'       => 'Keberlanjutan',
        'careers'         => 'Karir',
        'contact'         => 'Kontak',
        'privacy'         => 'Kebijakan Privasi',
    ];

    /** Kunci halaman yang sedang dibuka, bila termasuk daftar PAGES. */
    public static function key(): ?string
    {
        $name = request()->route()?->getName();

        if (! $name || ! str_contains($name, '.')) {
            return null;
        }

        $key = substr($name, strpos($name, '.') + 1); // id.about.profile => about.profile

        return array_key_exists($key, self::PAGES) ? $key : null;
    }

    /** Isian admin untuk halaman ini (bahasa aktif, EN kosong = ID). Hanya kunci yang terisi: title, description, image. */
    public static function current(): array
    {
        $key = self::key();

        if ($key === null) {
            return [];
        }

        $page   = PageContentRepo::get(self::PAGE)['pages'][$key] ?? [];
        $locale = app()->getLocale();
        $out    = [];

        $title = trim((string) ($page['title'][$locale] ?? ''));
        if ($title !== '') {
            // Judul diberi akhiran nama situs, kecuali beranda atau yang sudah memuatnya.
            $site = (string) config('site.name');
            $out['title'] = $key === 'home' || mb_stripos($title, $site) !== false ? $title : $title . ' | ' . $site;
        }

        $desc = trim((string) ($page['description'][$locale] ?? ''));
        if ($desc !== '') {
            $out['description'] = $desc;
        }

        $image = trim((string) ($page['image'] ?? ''));
        if ($image !== '') {
            $out['image'] = $image;
        }

        return $out;
    }

    /** Gambar share bawaan seluruh situs (path), bila diatur. */
    public static function defaultImage(): ?string
    {
        $path = trim((string) (PageContentRepo::get(self::PAGE)['default_image'] ?? ''));

        return $path !== '' ? $path : null;
    }

    /** URL absolut gambar bila berkasnya ada di server; selain itu null (hindari og:image rusak). */
    public static function imageUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '' || str_contains($path, '..') || preg_match('#^https?://#i', $path)) {
            return preg_match('#^https?://#i', $path) ? $path : null;
        }

        return is_file(public_path($path)) || is_file(\App\Models\Media::absolute($path)) ? asset($path) : null;
    }
}
