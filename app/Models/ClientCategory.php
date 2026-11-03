<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** B3h: kategori klien (Kementerian, Universitas, dst.). Slug hanya kunci filter di halaman Klien & Mitra. */
class ClientCategory extends Model
{
    /** "all" dicadangkan: dipakai tombol filter Semua di halaman publik. */
    public const RESERVED_SLUGS = ['all', 'semua'];

    protected $fillable = ['slug', 'name', 'sort_order'];

    protected $casts = [
        'name'       => 'array',
        'sort_order' => 'integer',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Nama sesuai bahasa; kosong di EN memakai ID. */
    public function label(?string $locale = null): string
    {
        return News::pick($this->name, $locale);
    }
}
