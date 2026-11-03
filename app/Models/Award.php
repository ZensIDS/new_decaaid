<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * B3i: penghargaan dan sertifikat. Judul, deskripsi, dan alt gambar dwibahasa (JSON id/en);
 * kolom EN yang kosong jatuh ke Bahasa Indonesia di halaman publik.
 */
class Award extends Model
{
    public const TRANSLATION = [
        'manual'   => 'Manual',
        'auto'     => 'Otomatis (belum ditinjau)',
        'reviewed' => 'Ditinjau',
    ];

    protected $fillable = [
        'title', 'description', 'year', 'image', 'image_alt', 'is_featured', 'is_visible',
        'translation_status', 'is_dummy', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'title'       => 'array',
        'description' => 'array',
        'image_alt'   => 'array',
        'year'        => 'integer',
        'is_featured' => 'boolean',
        'is_visible'  => 'boolean',
        'is_dummy'    => 'boolean',
    ];

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

    /** Apakah ada isi berbahasa Inggris (penanda di daftar admin). */
    public function hasEnglish(): bool
    {
        return filled(trim((string) ($this->title['en'] ?? '')));
    }
}
