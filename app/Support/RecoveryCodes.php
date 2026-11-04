<?php

namespace App\Support;

use App\Models\User;

/**
 * Kode pemulihan 2FA (dipakai bila ponsel hilang): 8 kode sekali pakai berbentuk XXXXX-XXXXX.
 * Disimpan hanya dalam bentuk hash HMAC (kunci APP_KEY); kode asli ditampilkan sekali saat dibuat.
 */
class RecoveryCodes
{
    public const COUNT = 8;

    /** Tanpa huruf/angka yang mudah tertukar (I, O, 0, 1). */
    protected const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /** @return array<int, string> */
    public static function generate(): array
    {
        $codes = [];

        for ($i = 0; $i < self::COUNT; $i++) {
            $raw = '';
            for ($j = 0; $j < 10; $j++) {
                $raw .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }
            $codes[] = substr($raw, 0, 5).'-'.substr($raw, 5);
        }

        return $codes;
    }

    /** Simpan (ter-hash) untuk pengguna; $user->save() dilakukan pemanggil. */
    public static function assign(User $user, array $codes): void
    {
        $user->two_factor_recovery_codes = json_encode(array_map([self::class, 'hash'], $codes));
    }

    /** Cocokkan masukan dengan kode yang tersisa; bila cocok kode itu dihapus (sekali pakai). */
    public static function consume(User $user, string $input): bool
    {
        $norm = self::normalize($input);

        if (strlen($norm) !== 10) {
            return false;
        }

        $hash  = self::hash($norm);
        $codes = json_decode((string) $user->two_factor_recovery_codes, true);
        $found = false;

        foreach (is_array($codes) ? $codes : [] as $i => $stored) {
            if (hash_equals((string) $stored, $hash) && ! $found) {
                unset($codes[$i]);
                $found = true;
            }
        }

        if ($found) {
            $user->forceFill(['two_factor_recovery_codes' => json_encode(array_values($codes))])->saveQuietly();
        }

        return $found;
    }

    public static function normalize(string $code): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code) ?? '');
    }

    protected static function hash(string $code): string
    {
        return hash_hmac('sha256', self::normalize($code), (string) config('app.key'));
    }
}
