<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * B3m (QA1): halaman kustom. Judul, isi, dan SEO dwibahasa (JSON id/en); EN kosong jatuh ke ID.
 * Hanya yang berstatus Terbit yang bisa dibuka pengunjung.
 */
class CustomPage extends Model
{
    /** Kata yang selalu dilarang sebagai slug (selain slug halaman bawaan, lihat reservedSlugs()). */
    public const EXTRA_RESERVED = [
        'admin', 'storage', 'wp-uploads', 'img', 'build', 'vendor', 'style-guide',
        'page', 'halaman', 'search', 'cari', 'feed', 'sitemap', 'sitemap-xml', 'robots', 'kategori', 'category',
    ];

    protected $fillable = [
        'slug', 'title', 'body', 'seo_title', 'seo_description',
        'is_published', 'show_in_footer', 'sort_order', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'title'           => 'array',
        'body'            => 'array',
        'seo_title'       => 'array',
        'seo_description' => 'array',
        'is_published'    => 'boolean',
        'show_in_footer'  => 'boolean',
        'sort_order'      => 'integer',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /** Nilai sesuai bahasa; bila kosong memakai Bahasa Indonesia. */
    public function t(string $field, ?string $locale = null): string
    {
        return News::pick($this->{$field}, $locale);
    }

    public function hasEnglish(): bool
    {
        return filled($this->title['en'] ?? null) && filled(trim(strip_tags((string) ($this->body['en'] ?? ''))));
    }

    /**
     * Slug yang tidak boleh dipakai: segmen pertama semua halaman bawaan (kedua bahasa) + daftar tambahan.
     * Rute bawaan memang menang lebih dulu, tetapi dilarang agar admin tidak bingung mengapa halamannya tak muncul.
     *
     * @return array<int, string>
     */
    public static function reservedSlugs(): array
    {
        $reserved = self::EXTRA_RESERVED;

        foreach ((array) config('site.routes') as $route) {
            foreach ((array) ($route['slug'] ?? []) as $slug) {
                $first = explode('/', trim((string) $slug, '/'))[0] ?? '';
                if ($first !== '' && ! str_starts_with($first, '{')) {
                    $reserved[] = $first;
                }
            }
        }

        return array_values(array_unique($reserved));
    }
}
