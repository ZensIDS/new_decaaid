<?php
namespace App\Models;

use App\Models\Concerns\MachineTranslated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * B3c: brand/perusahaan di dalam sebuah pilar. Deskripsi dwibahasa (JSON id/en);
 * kolom yang kosong tidak ditampilkan di halaman publik (aturan "tidak diisi karangan").
 */
class Brand extends Model
{
    use MachineTranslated;

    /**
     * Kolom dwibahasa yang ikut terjemahan otomatis: kolom => [maks karakter, HTML?].
     * Judul dan butir Layanan (di kolom JSON 'services') ditangani terpisah oleh BrandController.
     */
    public const AUTO_FIELDS = [
        'description'     => [4000, false],
        'seo_title'       => [120, false],
        'seo_description' => [300, false],
    ];

    public const TRANSLATION = [
        'manual'   => 'Manual',
        'auto'     => 'Otomatis (belum ditinjau)',
        'reviewed' => 'Ditinjau',
    ];

    protected $fillable = [
        'pillar_id', 'slug', 'name', 'logo', 'description', 'instagram', 'tiktok', 'url',
        'services', 'seo_title', 'seo_description', 'sort_order', 'is_visible', 'translation_status', 'translated_from', 'is_dummy',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'description' => 'array',
        'services'    => 'array',
        'seo_title'       => 'array',
        'seo_description' => 'array',
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

    /** Isi bahasa $locale hasil mesin dan belum ditinjau (tampil sebagai catatan di halaman brand). Brand tidak punya 'title': deskripsi yang jadi patokan. */
    public function machineTranslated(string $locale): bool
    {
        return $this->translation_status === 'auto'
            && filled($this->translated_from)
            && $this->translated_from !== $locale
            && filled(trim(strip_tags((string) ($this->description[$locale] ?? ''))));
    }

    public function hasEnglish(): bool
    {
        return filled(trim((string) ($this->description['en'] ?? '')));
    }
}