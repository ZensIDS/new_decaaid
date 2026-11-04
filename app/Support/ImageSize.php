<?php
namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Tahap C: ukuran asli gambar di public/ untuk atribut width/height (mencegah layout shift).
 * Di-cache menurut path + waktu ubah + ukuran file, jadi mengganti file otomatis memperbarui nilainya.
 * SVG dan file yang tidak terbaca mengembalikan null (atribut tidak ditambahkan).
 */
class ImageSize
{
    /** @return array{0:int,1:int}|null */
    public static function of(?string $src): ?array
    {
        if (! $src) {
            return null;
        }

        $path = public_path(ltrim($src, '/'));
        if (! is_file($path) || strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'svg') {
            return null;
        }

        try {
            $key = 'imgsize:' . md5($path . '|' . filemtime($path) . '|' . filesize($path));

            $dims = Cache::remember($key, now()->addDays(30), function () use ($path) {
                $info = @getimagesize($path);

                return $info && $info[0] > 0 && $info[1] > 0 ? [(int) $info[0], (int) $info[1]] : [0, 0];
            });

            return $dims[0] > 0 ? $dims : null;
        } catch (Throwable) {
            return null;
        }
    }
}
