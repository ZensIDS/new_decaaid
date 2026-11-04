<?php
namespace App\Support;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * B3d/B4: sumber data berita untuk halaman publik. Mengembalikan array dengan bentuk yang sama
 * seperti data dummy lama (slug, category_slug, category, date, image, title, excerpt),
 * ditambah body_html, image_alt, seo_title, dan seo_description, sehingga view hanya perlu
 * sedikit perubahan. Kolom EN yang kosong otomatis memakai Bahasa Indonesia.
 *
 * Bila tabel belum ada (migrasi belum dijalankan), available() bernilai false dan
 * App\Support\Content memakai data dummy bawaan kode agar situs tidak error.
 */
class NewsRepo
{
    protected static ?bool $available = null;

    public static function available(): bool
    {
        if (self::$available === null) {
            try {
                self::$available = Schema::hasTable('news') && Schema::hasTable('news_categories');
            } catch (Throwable) {
                self::$available = false;
            }
        }

        return self::$available;
    }

    /** Semua berita yang boleh tampil, terbaru dulu. */
    public static function all(): array
    {
        return News::query()->visible()->with('category')
            ->orderByDesc('published_at')->orderByDesc('id')
            ->get()
            ->map(fn(News $n) => self::shape($n))
            ->all();
    }

    /**
     * B4: pencarian berita langsung di database (judul, ringkasan, isi), terbaru dulu, dibatasi SiteSearch::LIMIT.
     * Mengembalikan [['title', 'excerpt', 'slug']]; excerpt = ringkasan, atau cuplikan isi bila ringkasan kosong.
     */
    public static function search(string $q, int $limit = SiteSearch::LIMIT): array
    {
        $query = News::query()->visible();

        SiteSearch::whereJsonLike($query, ['title', 'excerpt', 'body'], $q);

        return $query->orderByDesc('published_at')->orderByDesc('id')->limit($limit)->get()
            ->map(fn(News $n) => [
                'title'   => $n->t('title'),
                'excerpt' => $n->t('excerpt') !== '' ? $n->t('excerpt') : SiteSearch::snippet($n->t('body'), $q),
                'slug'    => $n->slug,
            ])
            ->all();
    }

    public static function find(string $slug): ?array
    {
        $news = News::query()->visible()->with('category')->where('slug', $slug)->first();

        return $news ? self::shape($news) : null;
    }

    /** Kategori yang punya berita tampil (untuk chip di halaman Berita). */
    public static function categories(): array
    {
        $used = News::query()->visible()->whereNotNull('news_category_id')->pluck('news_category_id')->unique();

        return NewsCategory::query()->whereIn('id', $used)->orderBy('sort_order')->orderBy('id')->get()
            ->mapWithKeys(fn(NewsCategory $c) => [$c->slug => $c->label()])
            ->all();
    }

    /** Semua kategori (termasuk yang masih kosong), dipakai halaman kategori. */
    public static function category(string $slug): ?array
    {
        $c = NewsCategory::query()->where('slug', $slug)->first();

        return $c ? ['slug' => $c->slug, 'name' => $c->label()] : null;
    }

    protected static function shape(News $n): array
    {
        $title = $n->t('title');
        $locale = app()->getLocale();

        return [
            // Tahap B5: isi bahasa ini hasil mesin dan belum ditinjau (tampil sebagai catatan di halaman berita).
            'machine_translated' => $n->translation_status === 'auto'
                && filled($n->translated_from)
                && $n->translated_from !== $locale
                && filled(trim(strip_tags((string) ($n->title[$locale] ?? '')))),
            'slug'            => $n->slug,
            'category_slug'   => $n->category?->slug ?? '',
            'category'        => $n->category?->label() ?? '',
            'date'            => ($n->published_at ?? $n->created_at)->format('Y-m-d'),
            'image'           => $n->image,
            'image_alt'       => $n->t('image_alt') ?: $title,
            'title'           => $title,
            'excerpt'         => $n->t('excerpt'),
            'body_html'       => $n->t('body'),
            'seo_title'       => $n->t('seo_title'),
            'seo_description' => $n->t('seo_description'),
        ];
    }
}
