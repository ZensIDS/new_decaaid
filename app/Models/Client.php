<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** B3h: klien atau mitra. Yang wajib hanya logo dan kategori; nama boleh kosong (K30). */
class Client extends Model
{
    protected $fillable = [
        'client_category_id', 'name', 'logo', 'sort_order', 'is_visible', 'show_on_home',
        'permission_ok', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'sort_order'    => 'integer',
        'is_visible'    => 'boolean',
        'show_on_home'  => 'boolean',
        'permission_ok' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ClientCategory::class, 'client_category_id');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    /** Nama untuk admin: nama klien, atau penanda bila kosong. */
    public function displayName(): string
    {
        return filled($this->name) ? (string) $this->name : 'Tanpa nama';
    }

    /** URL logo untuk admin; null bila berkas tidak ada. */
    public function logoUrl(): ?string
    {
        return $this->logo && file_exists(public_path($this->logo)) ? asset($this->logo) : null;
    }
}
