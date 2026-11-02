<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * B3d: berita. Judul, ringkasan, isi, alt gambar, dan SEO disimpan dwibahasa (JSON id/en).
 * Tampil di publik bila status Terbit, atau Terjadwal yang waktunya sudah lewat
 * (tidak butuh cron; penjadwal hanya mempercepat pembaruan cache di masa depan).
 */
class News extends Model
{
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUSES = [
        self::STATUS_DRAFT     => 'Draf',
        self::STATUS_PUBLISHED => 'Terbit',
        self::STATUS_SCHEDULED => 'Terjadwal',
    ];

    public const TRANSLATION = [
        'manual'   => 'Manual',
        'auto'     => 'Otomatis (belum ditinjau)',
        'reviewed' => 'Ditinjau',
    ];

    /** Slug berita yang dilarang (QA12: jaga agar tidak bentrok dengan rute). */
    public const RESERVED_SLUGS = ['kategori', 'category', 'page', 'halaman', 'search', 'cari', 'feed', 'sitemap'];

    /** Kolom dwibahasa. */
    public const LOCALIZED = ['title', 'excerpt', 'body', 'image_alt', 'seo_title', 'seo_description'];

    protected $table = 'news';

    protected $fillable = [
        'slug', 'news_category_id', 'title', 'excerpt', 'body', 'image', 'image_alt',
        'status', 'published_at', 'translation_status', 'seo_title', 'seo_description',
        'is_dummy', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'title'           => 'array',
        'excerpt'         => 'array',
        'body'            => 'array',
        'image_alt'       => 'array',
        'seo_title'       => 'array',
        'seo_description' => 'array',
        'published_at'    => 'datetime',
        'is_dummy'        => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'news_category_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(NewsRevision::class)->latest('id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Hanya berita yang boleh dilihat pengunjung. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where(function (Builder $w) {
                $w->where('status', self::STATUS_PUBLISHED)
                    ->where(fn(Builder $d) => $d->whereNull('published_at')->orWhere('published_at', '<=', now()));
            })->orWhere(function (Builder $w) {
                $w->where('status', self::STATUS_SCHEDULED)->where('published_at', '<=', now());
            });
        });
    }

    public function isVisible(): bool
    {
        if ($this->status === self::STATUS_PUBLISHED) {
            return ! $this->published_at || $this->published_at->lte(now());
        }

        return $this->status === self::STATUS_SCHEDULED && $this->published_at && $this->published_at->lte(now());
    }

    /** Nilai sesuai bahasa; bila kosong memakai Bahasa Indonesia. */
    public static function pick(mixed $value, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if (! is_array($value)) {
            return (string) $value;
        }

        $text = $value[$locale] ?? null;

        return filled(is_string($text) ? trim(strip_tags($text)) : $text) ? (string) $text : (string) ($value['id'] ?? '');
    }

    public function t(string $field, ?string $locale = null): string
    {
        return self::pick($this->{$field}, $locale);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Apakah kolom dwibahasa punya isi EN (untuk penanda di form). */
    public function hasEnglish(): bool
    {
        return filled($this->title['en'] ?? null) && filled(trim(strip_tags($this->body['en'] ?? '')));
    }

    /** Isi yang disalin ke riwayat revisi. */
    public function snapshot(): array
    {
        return $this->only([
            'slug', 'news_category_id', 'title', 'excerpt', 'body', 'image', 'image_alt',
            'status', 'published_at', 'translation_status', 'seo_title', 'seo_description',
        ]);
    }
}
