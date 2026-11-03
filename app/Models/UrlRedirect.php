<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * B3n: satu aturan pengalihan URL. Dipakai oleh App\Support\RedirectRepo (dipanggil dari Exceptions\Handler saat 404).
 */
class UrlRedirect extends Model
{
    protected $table = 'redirects';

    public const CODES = [301 => 'Permanen (301)', 302 => 'Sementara (302)'];

    /** Awalan alamat yang tidak boleh dialihkan (milik sistem). */
    public const BLOCKED = ['/admin', '/storage', '/build', '/vendor', '/sitemap.xml', '/robots.txt'];

    protected $fillable = ['from_path', 'to_path', 'status_code', 'is_active', 'note', 'created_by', 'updated_by'];

    protected $casts = [
        'status_code' => 'integer',
        'is_active'   => 'boolean',
        'hits'        => 'integer',
        'last_hit_at' => 'datetime',
    ];

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Ubah alamat asal (path atau URL lengkap) menjadi bentuk baku: "/path/kecil", tanpa host, query, #, dan "/" akhir.
     * Beranda ("/") menghasilkan string kosong, yang berarti tidak sah.
     */
    public static function normalizeFrom(string $value): string
    {
        $value = trim($value);

        if (preg_match('#^https?://#i', $value)) {
            $value = (string) parse_url($value, PHP_URL_PATH);
        }

        $value = preg_replace('/[?#].*$/', '', $value) ?? '';
        $value = mb_strtolower(rawurldecode($value));
        $value = trim($value, "/ \t\n\r\0\x0B");

        return $value === '' ? '' : '/' . $value;
    }

    /** Tujuan: path internal (diawali "/") atau URL http(s) lengkap; selain itu kosong (tidak sah). */
    public static function normalizeTo(string $value): string
    {
        $value = trim($value);

        if (preg_match('#^https?://[^\s]+$#i', $value)) {
            return $value;
        }

        if (str_starts_with($value, '/') && ! str_starts_with($value, '//') && ! preg_match('/\s/', $value)) {
            return $value;
        }

        return '';
    }

    public static function isBlocked(string $from): bool
    {
        foreach (self::BLOCKED as $prefix) {
            if ($from === $prefix || str_starts_with($from, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }
}
