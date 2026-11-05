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
            'maintenance'  => ['enabled' => false, 'secret' => '', 'title' => ['id' => '', 'en' => ''], 'message' => ['id' => '', 'en' => ''], 'ips' => [], 'from' => '', 'until' => ''],
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

        return ! empty($m['enabled']) && filled($m['secret'] ?? null) && self::inWindow($m) === 'active';
    }

    /** Waktu (zona aplikasi) dari isian "Y-m-d\TH:i"; null bila kosong atau tidak valid. */
    public static function parseTime(mixed $value): ?\Carbon\Carbon
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $value, config('app.timezone'));
        } catch (Throwable) {
            return null;
        }
    }

    /** Posisi jadwal: 'active' (tanpa jadwal, atau sedang di dalam jendela), 'waiting' (belum mulai), 'ended' (sudah selesai). */
    public static function inWindow(array $m): string
    {
        $from  = self::parseTime($m['from'] ?? null);
        $until = self::parseTime($m['until'] ?? null);

        if ($from && now()->lt($from)) {
            return 'waiting';
        }

        if ($until && now()->gte($until)) {
            return 'ended';
        }

        return 'active';
    }

    /** Detik sampai jadwal selesai (untuk Retry-After); null bila tanpa jadwal selesai. */
    public static function secondsUntilEnd(): ?int
    {
        $until = self::parseTime(self::get()['maintenance']['until'] ?? null);

        return $until && $until->isFuture() ? max(60, now()->diffInSeconds($until)) : null;
    }

    /** Judul dan pesan halaman pemeliharaan sesuai bahasa; kosong = teks bawaan di tampilan (tiap bahasa punya bawaannya sendiri). */
    public static function maintenanceText(string $field, string $locale): string
    {
        $v = (array) (self::get()['maintenance'][$field] ?? []);

        return trim((string) ($v[$locale] ?? ''));
    }

    /** Apakah alamat IP termasuk daftar pengecualian pemeliharaan (cocok persis, IPv4 atau IPv6). */
    public static function ipAllowed(?string $ip): bool
    {
        if (! $ip) {
            return false;
        }

        foreach ((array) (self::get()['maintenance']['ips'] ?? []) as $allowed) {
            if (is_string($allowed) && strcasecmp(trim($allowed), $ip) === 0) {
                return true;
            }
        }

        return false;
    }

    /** Nilai cookie tamu yang membuka situs saat pemeliharaan (diturunkan dari tautan rahasia dan APP_KEY). */
    public static function bypassToken(string $secret): string
    {
        return hash_hmac('sha256', $secret, (string) config('app.key'));
    }
}
