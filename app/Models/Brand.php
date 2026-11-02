<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * B3c: brand/perusahaan di dalam sebuah pilar. Deskripsi dwibahasa (JSON id/en);
 * kolom yang kosong tidak ditampilkan di halaman publik (aturan "tidak diisi karangan").
 */
class Brand extends Model
{
    public const TRANSLATION = [
        'manual'   => 'Manual',
        'auto'     => 'Otomatis (belum ditinjau)',
        'reviewed' => 'Ditinjau',
    ];

    protected $fillable = [
        'pillar_id', 'slug', 'name', 'logo', 'description', 'instagram', 'tiktok', 'url',
        'services', 'sort_order', 'is_visible', 'translation_status', 'is_dummy',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'description' => 'array',
        'services'    => 'array',
        'sort_order'  => 'integer',
        'is_visible'  => 'boolean',
        'is_dummy'    => 'boolean',
    ];

    public function pillar(): BelongsTo
    {
        return $this->belongsTo(Pillar::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Apakah ada deskripsi Indonesia (dipakai penanda "Tanpa deskripsi" di daftar admin). */
    public function hasDescription(): bool
    {
        return filled(trim((string) ($this->description['id'] ?? '')));
    }

    public function hasEnglish(): bool
    {
        return filled(trim((string) ($this->description['en'] ?? '')));
    }
}