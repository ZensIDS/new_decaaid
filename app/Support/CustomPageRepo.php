<?php
namespace App\Support;

use App\Models\CustomPage;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * B3m (QA1): membaca Halaman Kustom untuk situs publik. Bila tabel belum ada (belum migrate),
 * semua method aman: find() mengembalikan null dan footerLinks() kosong, jadi situs tidak pernah error.
 */
class CustomPageRepo
{
    protected static ?bool $available = null;

    /** @var array<string, array<int, array{slug: string, title: string}>> */
    protected static array $footer = [];

    public static function available(): bool
    {
        if (self::$available === null) {
            try {
                self::$available = Schema::hasTable('custom_pages');
            } catch (Throwable) {
                self::$available = false;
            }
        }

        return self::$available;
    }

    /** Halaman terbit menurut slug, atau null. */
    public static function find(string $slug): ?CustomPage
    {
        if (! self::available()) {
            return null;
        }

        try {
            return CustomPage::published()->where('slug', $slug)->first();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * B4: pencarian Halaman Kustom terbit (judul dan isi). Mengembalikan [['title', 'excerpt', 'slug']].
     */
    public static function search(string $q, int $limit = SiteSearch::LIMIT): array
    {
        if (! self::available()) {
            return [];
        }

        try {
            $query = CustomPage::published();

            SiteSearch::whereJsonLike($query, ['title', 'body'], $q);

            return $query->orderBy('sort_order')->orderBy('id')->limit($limit)->get()
                ->map(fn(CustomPage $p) => [
                    'title'   => $p->t('title'),
                    'excerpt' => SiteSearch::snippet($p->t('body'), $q),
                    'slug'    => $p->slug,
                ])
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Tautan footer (halaman terbit yang dicentang "tampil di footer"), judul sesuai bahasa aktif.
     *
     * @return array<int, array{slug: string, title: string, column: string}>
     */
    public static function footerLinks(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        if (isset(self::$footer[$locale])) {
            return self::$footer[$locale];
        }

        $links = [];

        if (self::available()) {
            try {
                $links = CustomPage::published()->where('show_in_footer', true)
                    ->orderBy('sort_order')->orderBy('id')->get()
                    ->map(fn(CustomPage $p) => [
                        'slug'   => $p->slug,
                        'title'  => $p->t('title', $locale),
                        'column' => array_key_exists((string) ($p->footer_column ?? ''), CustomPage::FOOTER_COLUMNS) ? $p->footer_column : 'company',
                    ])
                    ->all();
            } catch (Throwable) {
                $links = [];
            }
        }

        return self::$footer[$locale] = $links;
    }
}
