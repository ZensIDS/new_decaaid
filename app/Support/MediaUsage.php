<?php
namespace App\Support;

use App\Models\Award;
use App\Models\Brand;
use App\Models\Client;
use App\Models\CustomPage;
use App\Models\News;
use App\Models\PageContent;
use App\Models\Project;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * B3b: pelacak pemakaian gambar. Mencari path sebuah berkas di semua modul yang menyimpan gambar
 * (kolom gambar dan isi HTML/JSON). Dipakai halaman detail Media Library dan penjaga hapus.
 * Pencarian dilakukan di PHP (bukan LIKE) karena JSON menyimpan "/" sebagai "\/" dan datanya kecil.
 */
class MediaUsage
{
    /** Nama halaman page_contents => [label, route admin]. */
    protected const PAGES = [
        'home'          => ['Beranda', 'admin.home.edit'],
        'about'         => ['Tentang Kami', 'admin.about.edit'],
        'sustainability' => ['Keberlanjutan', 'admin.sustainability.edit'],
        'careers'       => ['Karir', 'admin.careers.edit'],
        'site_settings' => ['Pengaturan Situs', 'admin.settings.edit'],
        'privacy'       => ['Kebijakan Privasi', 'admin.pages.privacy.edit'],
        'menus'         => ['Menu', 'admin.menus.edit'],
    ];

    /** @return array<int, array{label: string, url: ?string}> */
    public static function find(string $path): array
    {
        $needle = ltrim($path, '/');
        $out    = [];

        $add = function (string $label, ?string $url) use (&$out) {
            $out[] = ['label' => $label, 'url' => $url];
        };

        $has = fn($value) => $value !== null && $value !== '' && str_contains(self::flat($value), $needle);
        $url = fn(string $name, $param = null) => Route::has($name) ? route($name, $param) : null;

        try {
            if (Schema::hasTable('news')) {
                foreach (News::query()->select(['id', 'title', 'image', 'body'])->cursor() as $n) {
                    if ($has($n->image) || $has($n->body)) {
                        $add('Berita: ' . $n->t('title', 'id'), $url('admin.news.edit', $n));
                    }
                }
            }

            if (Schema::hasTable('projects')) {
                foreach (Project::query()->select(['id', 'title', 'image', 'gallery'])->cursor() as $p) {
                    if ($has($p->image) || $has($p->gallery)) {
                        $add('Proyek: ' . $p->t('title', 'id'), $url('admin.projects.edit', $p));
                    }
                }
            }

            if (Schema::hasTable('awards')) {
                foreach (Award::query()->select(['id', 'title', 'image'])->cursor() as $a) {
                    if ($has($a->image)) {
                        $add('Penghargaan: ' . $a->t('title', 'id'), $url('admin.awards.edit', $a));
                    }
                }
            }

            if (Schema::hasTable('clients')) {
                foreach (Client::query()->select(['id', 'name', 'logo'])->cursor() as $c) {
                    if ($has($c->logo)) {
                        $add('Klien: ' . $c->displayName(), $url('admin.clients.edit', $c));
                    }
                }
            }

            if (Schema::hasTable('brands')) {
                foreach (Brand::query()->select(['id', 'name', 'logo'])->cursor() as $b) {
                    if ($has($b->logo)) {
                        $add('Brand: ' . (is_array($b->name) ? ($b->name['id'] ?? '') : (string) $b->name), $url('admin.pillars.brands.edit', $b));
                    }
                }
            }

            if (Schema::hasTable('custom_pages')) {
                $cpCols = ['id', 'title', 'body'];
                if (CustomPage::hasSeoImageColumn()) {
                    $cpCols[] = 'seo_image';
                }
                foreach (CustomPage::query()->select($cpCols)->cursor() as $cp) {
                    if ($has($cp->body) || $has($cp->seo_image ?? null)) {
                        $add('Halaman kustom: ' . News::pick($cp->title ?? [], 'id'), $url('admin.custompages.edit', $cp));
                    }
                }
            }

            if (Schema::hasTable('page_contents')) {
                foreach (PageContent::query()->select(['id', 'page', 'content'])->cursor() as $pc) {
                    if ($has($pc->content)) {
                        [$label, $route] = self::PAGES[$pc->page] ?? [ucfirst(str_replace('_', ' ', (string) $pc->page)), null];
                        $add('Halaman: ' . $label, $route ? $url($route) : null);
                    }
                }
            }
        } catch (Throwable) {
            // pemindaian sebagian tetap berguna; jangan gagalkan halaman
        }

        return $out;
    }

    /** Teks datar dari string atau array/JSON, dengan "\/" dikembalikan jadi "/". */
    protected static function flat(mixed $value): string
    {
        $text = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return str_replace('\\/', '/', (string) $text);
    }
}
