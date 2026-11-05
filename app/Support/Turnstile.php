<?php
namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Cloudflare Turnstile (captcha ringan) untuk formulir Kontak. MATI secara bawaan: aktif hanya bila
 * TURNSTILE_SITE_KEY dan TURNSTILE_SECRET_KEY terisi di .env. Honeypot dan pembatas kirim tetap berlaku.
 *
 * Kebijakan gangguan: bila server Cloudflare tidak bisa dihubungi (timeout/galat jaringan), pengiriman
 * DIIZINKAN dan peringatan dicatat di log, supaya pesan calon klien tidak hilang. Penolakan hanya bila
 * Cloudflare menjawab tegas bahwa verifikasi gagal atau token tidak ada.
 */
class Turnstile
{
    protected const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public static function enabled(): bool
    {
        return filled(config('security.turnstile.site_key')) && filled(config('security.turnstile.secret'));
    }

    public static function siteKey(): string
    {
        return (string) config('security.turnstile.site_key');
    }

    /** true = lolos (atau Cloudflare tak terjangkau); false = ditolak. Tidak dipanggil bila fitur mati. */
    public static function passes(?string $token, ?string $ip = null): bool
    {
        $token = trim((string) $token);

        if ($token === '') {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post(self::VERIFY_URL, array_filter([
                'secret'   => config('security.turnstile.secret'),
                'response' => $token,
                'remoteip' => $ip,
            ]));

            if ($response->serverError()) {
                Log::warning('Turnstile: server Cloudflare galat, pengiriman diizinkan.', ['status' => $response->status()]);

                return true;
            }

            return (bool) $response->json('success', false);
        } catch (Throwable $e) {
            Log::warning('Turnstile: tidak terhubung ke Cloudflare, pengiriman diizinkan.', ['error' => $e->getMessage()]);

            return true;
        }
    }
}
