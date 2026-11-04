<?php

namespace App\Support;

/**
 * Kode sekali pakai berbasis waktu (TOTP, RFC 6238; HMAC-SHA1, 6 digit, langkah 30 detik),
 * kompatibel dengan Google Authenticator, Microsoft Authenticator, Authy, 1Password, dan sejenisnya.
 * Tanpa pustaka tambahan.
 */
class Totp
{
    public const PERIOD = 30;
    public const DIGITS = 6;

    protected const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /** Rahasia acak (20 byte = 160 bit) dalam Base32. */
    public static function generateSecret(int $bytes = 20): string
    {
        return self::base32Encode(random_bytes($bytes));
    }

    public static function code(string $secret, ?int $step = null): string
    {
        $step ??= intdiv(time(), self::PERIOD);
        $key   = self::base32Decode($secret);
        $hash  = hash_hmac('sha1', pack('J', $step), $key, true);
        $off   = ord($hash[19]) & 0x0F;
        $bin   = ((ord($hash[$off]) & 0x7F) << 24) | (ord($hash[$off + 1]) << 16) | (ord($hash[$off + 2]) << 8) | ord($hash[$off + 3]);

        return str_pad((string) ($bin % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Cek kode terhadap langkah sekarang dan $window langkah sebelum/sesudahnya (toleransi selisih jam).
     * Mengembalikan nomor langkah yang cocok (untuk mencegah kode yang sama dipakai dua kali) atau null.
     */
    public static function verify(string $secret, string $code, int $window = 1, ?int $now = null): ?int
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';

        if (! preg_match('/^\d{'.self::DIGITS.'}$/', $code)) {
            return null;
        }

        $current = intdiv($now ?? time(), self::PERIOD);
        $found   = null;

        // Semua langkah dicek (tanpa berhenti di yang pertama) agar waktu respons tidak membocorkan apa pun.
        for ($i = -$window; $i <= $window; $i++) {
            $step = $current + $i;

            if (hash_equals(self::code($secret, $step), $code) && $found === null) {
                $found = $step;
            }
        }

        return $found;
    }

    /** Alamat otpauth:// yang dibaca aplikasi autentikator (dipakai juga sebagai tautan "buka di aplikasi"). */
    public static function uri(string $secret, string $account, string $issuer): string
    {
        return 'otpauth://totp/'.rawurlencode($issuer.':'.$account)
            .'?secret='.$secret
            .'&issuer='.rawurlencode($issuer)
            .'&algorithm=SHA1&digits='.self::DIGITS.'&period='.self::PERIOD;
    }

    /** "ABCD EFGH IJKL ..." agar mudah diketik manual. */
    public static function format(string $secret): string
    {
        return trim(chunk_split($secret, 4, ' '));
    }

    public static function base32Encode(string $bin): string
    {
        $bits = '';
        foreach (str_split($bin) as $ch) {
            $bits .= str_pad(decbin(ord($ch)), 8, '0', STR_PAD_LEFT);
        }

        $out = '';
        foreach (str_split($bits, 5) as $chunk) {
            $out .= self::ALPHABET[bindec(str_pad($chunk, 5, '0'))];
        }

        return $out;
    }

    public static function base32Decode(string $b32): string
    {
        $b32  = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $b32) ?? '');
        $bits = '';

        foreach (str_split($b32) as $ch) {
            $bits .= str_pad(decbin((int) strpos(self::ALPHABET, $ch)), 5, '0', STR_PAD_LEFT);
        }

        $out = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $out .= chr(bindec($byte));
            }
        }

        return $out;
    }
}
