<?php
namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Tahap C (v90): gambar responsif (`srcset`) untuk gambar unggahan admin (`storage/...`).
 * Varian lebih kecil (WebP) dibuat sekali, saat gambar pertama kali ditampilkan, dan disimpan di
 * `storage/app/public/variants/{lebar}/{path asli}`. Hasilnya di-cache menurut path + waktu ubah + ukuran file.
 * Aman bila gagal (GD tak ada, folder tak bisa ditulis, gambar terlalu besar): tidak ada `srcset`, gambar
 * asli tetap tampil seperti biasa.
 */
class ImageVariants
{
    /** Lebar varian (px). Dipakai hanya bila gambar asli minimal GAIN kali lebih lebar. */
    public const WIDTHS = [480, 960];
    public const GAIN   = 1.25;

    /** Batas piksel gambar asli yang boleh didekode (menjaga memori). */
    protected const MAX_PIXELS = 16000000;

    /** Nilai atribut `srcset`, atau null bila tidak perlu/tidak bisa. */
    public static function srcset(?string $src): ?string
    {
        $src = ltrim((string) $src, '/');

        if (! str_starts_with($src, 'storage/') || ! in_array(strtolower(pathinfo($src, PATHINFO_EXTENSION)), ['webp', 'jpg', 'jpeg', 'png'], true)) {
            return null;
        }

        $file = public_path($src);

        if (! is_file($file)) {
            return null;
        }

        try {
            $key = 'imgsrcset:' . md5($file . '|' . filemtime($file) . '|' . filesize($file));

            // Cache::remember tidak menyimpan null, jadi "tidak ada srcset" disimpan sebagai string kosong.
            $value = Cache::remember($key, now()->addDays(30), fn() => self::build($src) ?? '');

            return $value !== '' ? $value : null;
        } catch (Throwable) {
            return null;
        }
    }

    /** Hapus semua varian sebuah gambar (dipanggil saat gambar asli dihapus). */
    public static function forget(string $src): void
    {
        $src = ltrim($src, '/');

        if (! str_starts_with($src, 'storage/')) {
            return;
        }

        try {
            $rel  = substr($src, strlen('storage/'));
            $disk = Storage::disk('public');

            foreach ($disk->directories('variants') as $dir) {
                $disk->delete($dir . '/' . $rel);
            }
        } catch (Throwable) {
            // varian yatim tidak berbahaya
        }
    }

    protected static function build(string $src): ?string
    {
        $dims = ImageSize::of($src);

        if (! $dims || $dims[0] * $dims[1] > self::MAX_PIXELS) {
            return null;
        }

        $rel     = substr($src, strlen('storage/'));
        $entries = [];

        foreach (self::WIDTHS as $width) {
            if ($dims[0] < $width * self::GAIN) {
                continue;
            }

            $variant = "variants/{$width}/" . preg_replace('/\.[A-Za-z0-9]+$/', '', $rel) . '.webp';

            if (Storage::disk('public')->exists($variant) || self::make(public_path($src), $dims, $width, $variant)) {
                $entries[] = asset('storage/' . $variant) . " {$width}w";
            }
        }

        if (! $entries) {
            return null;
        }

        $entries[] = asset($src) . " {$dims[0]}w";

        return implode(', ', $entries);
    }

    /** Buat satu varian WebP berlebar $width. */
    protected static function make(string $file, array $dims, int $width, string $variant): bool
    {
        if (! function_exists('imagewebp') || ! function_exists('imagecreatefromstring')) {
            return false;
        }

        $raw = @file_get_contents($file);
        $im  = $raw === false ? false : @imagecreatefromstring($raw);

        if (! $im) {
            return false;
        }

        $height = (int) max(1, round($dims[1] * $width / $dims[0]));
        $out    = imagecreatetruecolor($width, $height);

        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
        imagecopyresampled($out, $im, 0, 0, 0, 0, $width, $height, imagesx($im), imagesy($im));

        ob_start();
        $ok   = imagewebp($out, null, 80);
        $data = ob_get_clean();

        imagedestroy($im);
        imagedestroy($out);

        if (! $ok || ! is_string($data) || $data === '') {
            return false;
        }

        return Storage::disk('public')->put($variant, $data);
    }
}
