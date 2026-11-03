<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** B3l: pesan dari formulir Kontak. */
class ContactMessage extends Model
{
    /** Lama penyimpanan (bulan), K31. */
    public const RETENTION_MONTHS = 12;

    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'message', 'locale', 'ip_address',
        'is_read', 'read_at', 'read_by', 'notified',
    ];

    protected $casts = [
        'is_read'  => 'boolean',
        'notified' => 'boolean',
        'read_at'  => 'datetime',
    ];

    public function reader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }
}
