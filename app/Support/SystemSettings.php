<?php
namespace App\Support;

use App\Models\PageContent;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * B3p: pengaturan sistem (halaman 'system' di page_contents): mode pemeliharaan, analitik, kode verifikasi.
 * Dibaca di setiap permintaan publik, jadi di-cache 5 menit dan dibersihkan otomatis saat admin menyimpan.
 * Tabel belum ada atau galat apa pun = nilai bawaan (pemeliharaan mati, tanpa analitik).
 */
class SystemSettings
{
    public const PAGE = 'system';

    protected const CACHE_KEY = 'site.system_settings';

    public static function defaults(): array
    {
        return [
            'maintenance'  => ['enabled' => false, 'secret' => ''],
            'analytics'    => ['provider' => 'none', 'id' => '', 'script_url' => ''],
            'verification' => ['google' => '', 'bing' => ''],
        ];
    }

    public static function get(): array
    {
        try {
            $stored = Cache::remember(self::CACHE_KEY, 300, function () {
                return PageContentRepo::available()
                    ? (PageContent::where('page', self::PAGE)->value('content') ?: [])
                    : [];
            });
        } catch (Throwable) {
            $stored = [];
        }

        return array_replace_recursive(self::defaults(), is_array($stored) ? $stored : []);
    }

    public static function forget(): void
    {
        try {
            Cache::forget(self::CACHE_KEY);
        } catch (Throwable) {
        }
    }

    public static function maintenanceOn(): bool
    {
        $m = self::get()['maintenance'];

        return ! empty($m['enabled']) && filled($m['secret'] ?? null);
    }

    /** Nilai cookie tamu yang membuka situs saat pemeliharaan (diturunkan dari tautan rahasia dan APP_KEY). */
    public static function bypassToken(string $secret): string
    {
        return hash_hmac('sha256', $secret, (string) config('app.key'));
    }
}
