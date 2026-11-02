<?php
namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Penyimpan gambar unggahan admin (QA7): maksimal 5 MB, dikecilkan (lebar maks 1920 px),
 * dikonversi ke WebP bila GD mendukung. Disimpan di disk "public" dan dilayani lewat
 * tautan storage (php artisan storage:link). Path yang dikembalikan relatif ke public/,
 * mis. "storage/news/2026/11/abc.webp", sehingga <x-ui.img> bisa memakainya langsung.
 * Media Library lengkap (thumbnail, alt, pelacak pemakaian) menyusul di B3b.
 */
class ImageStore
{
    public const MAX_KB    = 5120;
    public const MAX_WIDTH = 1920;
    public const MIMES     = ['image/jpeg', 'image/png', 'image/webp'];

    /** Simpan dan kembalikan path relatif ke public/. */
    public static function store(UploadedFile $file, string $folder): string
    {
        $dir  = trim($folder, '/') . '/' . date('Y/m');
        $name = Str::lower(Str::random(20));
        $webp = self::toWebp($file);

        if ($webp !== null) {
            $path = "{$dir}/{$name}.webp";
            Storage::disk('public')->put($path, $webp);
        } else {
            $ext  = $file->guessExtension() ?: 'jpg';
            $path = "{$dir}/{$name}.{$ext}";
            Storage::disk('public')->putFileAs($dir, $file, "{$name}.{$ext}");
        }

        return 'storage/' . $path;
    }

    /** Hapus berkas hasil unggahan (path dari store()). Aset bawaan di public/img tidak disentuh. */
    public static function delete(?string $path): void
    {
        if ($path && str_starts_with($path, 'storage/')) {
            Storage::disk('public')->delete(substr($path, strlen('storage/')));
        }
    }

    /** @return string|null isi WebP, atau null bila GD tidak tersedia / gagal */
    protected static function toWebp(UploadedFile $file): ?string
    {
        if (! function_exists('imagewebp') || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        $raw = @file_get_contents($file->getRealPath());
        $img = $raw === false ? false : @imagecreatefromstring($raw);

        if (! $img) {
            return null;
        }

        $w = imagesx($img);
        $h = imagesy($img);

        if ($w > self::MAX_WIDTH) {
            $scaled = imagescale($img, self::MAX_WIDTH, (int) round($h * self::MAX_WIDTH / $w));
            if ($scaled) {
                imagedestroy($img);
                $img = $scaled;
            }
        }

        imagepalettetotruecolor($img);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        ob_start();
        $ok   = imagewebp($img, null, 82);
        $data = ob_get_clean();
        imagedestroy($img);

        return $ok && $data ? $data : null;
    }
}
