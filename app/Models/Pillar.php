<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * B3c: pilar bisnis (jenis bisnis). Memuat brand/perusahaan di dalamnya.
 * Slug dipakai di URL /pilar-bisnis/{slug}.
 */
class Pillar extends Model
{
    /** Ikon yang bisa dipilih (nama ikon di components/ui/icon, QA8 butir 3). */
    public const ICONS = [
        'code-bracket'  => 'Kode',
        'cpu-chip'      => 'Chip',
        'computer'      => 'Komputer',
        'signal'        => 'Sinyal',
        'academic-cap'  => 'Pendidikan',
        'pencil'        => 'Alat Tulis',
        'gift'          => 'Hadiah',
        'cake'          => 'Makanan',
        'briefcase'     => 'Bisnis',
        'sparkles'      => 'Inovasi',
        'heart'         => 'Peduli',
        'users'         => 'Komunitas',
        'book-open'     => 'Buku',
        'chat'          => 'Percakapan',
        'map-pin'       => 'Lokasi',
        'paper-airplane' => 'Kirim',
    ];

    protected $fillable = ['slug', 'name', 'icon', 'seo_title', 'seo_description', 'sort_order', 'is_visible'];

    protected $casts = [
        'seo_title'       => 'array',
        'seo_description' => 'array',
        'sort_order'      => 'integer',
        'is_visible'      => 'boolean',
    ];

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class)->orderBy('sort_order')->orderBy('id');
    }
}