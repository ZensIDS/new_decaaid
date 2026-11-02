<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** B3d: salinan isi berita sebelum diubah (untuk fitur kembalikan versi lama, QA18). */
class NewsRevision extends Model
{
    public const UPDATED_AT = null;

    /** Jumlah revisi yang disimpan per berita. */
    public const KEEP = 20;

    protected $fillable = ['news_id', 'user_id', 'user_name', 'snapshot'];

    protected $casts = [
        'snapshot'   => 'array',
        'created_at' => 'datetime',
    ];
}
