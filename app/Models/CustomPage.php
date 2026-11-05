<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Throwable;

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

    /** Pilihan status terjemahan (sama dengan Berita). */
    public const TRANSLATION = [
        'manual'   => 'Manual',
        'auto'     => 'Otomatis (belum ditinjau)',
        'reviewed' => 'Ditinjau',
    ];

    /** Kolom dwibahasa yang ikut terjemahan otomatis: kolom => [maks karakter (null = HTML), HTML?]. */
    public const AUTO_FIELDS = [
        'title'           => [150, false],
        'body'            => [null, true],
        'seo_title'       => [120, false],
        'seo_description' => [300, false],
    ];

    protected static ?bool $translationColumns = null;
    protected static ?bool $seoImageColumn = null;

    protected $fillable = [
        'slug', 'title', 'body', 'seo_title', 'seo_description', 'seo_image',
        'is_published', 'show_in_footer', 'sort_order', 'translation_status', 'translated_from', 'created_by', 'updated_by',
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

    /** Kolom gambar share sudah ada (setelah `php artisan migrate`)? Sebelum itu fitur dilewati tanpa galat. */
    public static function hasSeoImageColumn(): bool
    {
        if (self::$seoImageColumn === null) {
            try {
                self::$seoImageColumn = Schema::hasColumn('custom_pages', 'seo_image');
            } catch (Throwable) {
                self::$seoImageColumn = false;
            }
        }

        return self::$seoImageColumn;
    }

    /** Kolom status terjemahan sudah ada (setelah `php artisan migrate`)? Sebelum itu fitur dilewati tanpa galat. */
    public static function hasTranslationColumns(): bool
    {
        if (self::$translationColumns === null) {
            try {
                self::$translationColumns = Schema::hasColumn('custom_pages', 'translation_status');
            } catch (Throwable) {
                self::$translationColumns = false;
            }
        }

        return self::$translationColumns;
    }

    /** Isi bahasa $locale hasil mesin dan belum ditinjau (tampil sebagai catatan di halaman publik). */
    public function machineTranslated(string $locale): bool
    {
        return $this->translation_status === 'auto'
            && filled($this->translated_from)
            && $this->translated_from !== $locale
            && filled(trim(strip_tags((string) ($this->title[$locale] ?? ''))));
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(CustomPageRevision::class)->latest('id');
    }

    /** Kondisi isi saat ini, disimpan ke Riwayat Revisi sebelum diubah. */
    public function snapshot(): array
    {
        return $this->only(['slug', 'title', 'body', 'seo_title', 'seo_description']);
    }

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
