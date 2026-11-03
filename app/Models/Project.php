<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * B3g: proyek portofolio. Judul, ringkasan, deskripsi, lingkup kerja, alt gambar, dan SEO
 * disimpan dwibahasa (JSON id/en). Kolom EN yang kosong jatuh ke Bahasa Indonesia di halaman publik.
 */
class Project extends Model
{
    public const TRANSLATION = [
        'manual'   => 'Manual',
        'auto'     => 'Otomatis (belum ditinjau)',
        'reviewed' => 'Ditinjau',
    ];

    /** Slug yang dilarang: bentrok dengan rute /portofolio/penghargaan dan sejenisnya. */
    public const RESERVED_SLUGS = ['penghargaan', 'awards', 'kategori', 'category', 'page', 'halaman', 'search', 'cari'];

    /** Berapa proyek unggulan yang tampil di beranda. */
    public const HOME_LIMIT = 3;

    /** Kolom dwibahasa. */
    public const LOCALIZED = ['title', 'summary', 'description', 'scope', 'image_alt', 'seo_title', 'seo_description'];

    protected $fillable = [
        'slug', 'pillar_id', 'brand_id', 'title', 'client', 'year', 'summary', 'description', 'scope',
        'image', 'image_alt', 'gallery', 'is_featured', 'is_visible', 'translation_status',
        'seo_title', 'seo_description', 'is_dummy', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'title'           => 'array',
        'summary'         => 'array',
        'description'     => 'array',
        'scope'           => 'array',
        'image_alt'       => 'array',
        'gallery'         => 'array',
        'seo_title'       => 'array',
        'seo_description' => 'array',
        'year'            => 'integer',
        'is_featured'     => 'boolean',
        'is_visible'      => 'boolean',
        'is_dummy'        => 'boolean',
    ];

    public function pillar(): BelongsTo
    {
        return $this->belongsTo(Pillar::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    /** Teks sesuai bahasa; bila kosong memakai Bahasa Indonesia. */
    public function t(string $field, ?string $locale = null): string
    {
        return News::pick($this->{$field}, $locale);
    }

    /** Daftar lingkup kerja sesuai bahasa; EN kosong memakai daftar Indonesia. */
    public function workItems(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $lists   = is_array($this->scope) ? $this->scope : [];
        $chosen  = $lists[$locale] ?? [];

        return array_values(array_filter((array) ($chosen ?: ($lists['id'] ?? [])), fn($i) => filled($i)));
    }

    /** Apakah ada isi berbahasa Inggris (penanda di daftar admin). */
    public function hasEnglish(): bool
    {
        return filled(trim((string) ($this->title['en'] ?? '')));
    }
}