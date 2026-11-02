<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** B3d: kategori berita. Slug dipakai di URL /berita/kategori/{slug}. */
class NewsCategory extends Model
{
    /** Slug yang tidak boleh dipakai agar tidak bentrok dengan rute (QA12). */
    public const RESERVED_SLUGS = ['kategori', 'category', 'page', 'halaman', 'search', 'cari', 'feed', 'sitemap'];

    protected $fillable = ['slug', 'name', 'sort_order'];

    protected $casts = [
        'name'       => 'array',
        'sort_order' => 'integer',
    ];

    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }

    /** Nama sesuai bahasa; kosong di EN memakai ID. */
    public function label(?string $locale = null): string
    {
        return News::pick($this->name, $locale);
    }
}
