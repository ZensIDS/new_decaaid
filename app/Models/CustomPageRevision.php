<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Throwable;

/** Salinan isi Halaman Kustom sebelum diubah (untuk fitur kembalikan versi lama, QA18). */
class CustomPageRevision extends Model
{
    public const UPDATED_AT = null;

    /** Jumlah revisi yang disimpan per halaman. */
    public const KEEP = 20;

    /** Kolom yang ikut dikembalikan. Slug, status, dan footer sengaja tidak ikut (mengubah alamat/tampil). */
    public const RESTORABLE = ['title', 'body', 'seo_title', 'seo_description'];

    protected static ?bool $available = null;

    /** Aman dipanggil sebelum `php artisan migrate`: tanpa tabel, riwayat dilewati dan penyimpanan halaman tetap jalan. */
    public static function available(): bool
    {
        if (self::$available === null) {
            try {
                self::$available = Schema::hasTable('custom_page_revisions');
            } catch (Throwable) {
                self::$available = false;
            }
        }

        return self::$available;
    }

    protected $fillable = ['custom_page_id', 'user_id', 'user_name', 'snapshot'];

    protected $casts = [
        'snapshot'   => 'array',
        'created_at' => 'datetime',
    ];
}
