<?php
namespace App\Support;

use App\Models\Media;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * B3b: mendaftarkan berkas gambar ke Media Library (tabel `media`) dan membuat thumbnail.
 * Dipanggil otomatis oleh ImageStore setiap ada unggahan, dan oleh `php artisan media:sync`
 * untuk berkas lama. Semua kegagalan ditelan: unggahan tidak boleh gagal hanya karena katalog.
 */
class MediaStore
{
    public const THUMB_WIDTH = 480;

    protected static ?bool $ready = null;

    public static function ready(): bool
    {
        if (self::$ready === null) {
            try {
                self::$ready = Schema::hasTable('media');
            } catch (Throwable) {
                self::$ready = false;
            }
        }

        return self::$ready;
    }

    /** Daftarkan (atau kembalikan yang sudah ada). $path relatif ke public/. */
    public static function register(string $path, ?string $original = null): ?Media
    {
        try {
            if (! self::ready()) {
                return null;
            }

            if ($found = Media::where('path', $path)->first()) {
                return $found;
            }

            $abs = Media::absolute($path);

            if (! is_file($abs)) {
                return null;
            }

            $info   = @getimagesize($abs); // SVG: false
            $source = str_starts_with($path, 'storage/') ? 'upload' : 'bundled';
            $base   = $original ? pathinfo($original, PATHINFO_FILENAME) : pathinfo($path, PATHINFO_FILENAME);

            $media = Media::create([
                'path'       => $path,
                'name'       => mb_substr(trim(str_replace(['_', '-'], ' ', $base)) ?: 'Gambar', 0, 160),
                'mime'       => $info['mime'] ?? (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'svg' ? 'image/svg+xml' : null),
                'size'       => (int) @filesize($abs),
                'width'      => $info[0] ?? null,
                'height'     => $info[1] ?? null,
                'source'     => $source,
                'folder'     => self::folderOf($path),
                'created_by' => $source === 'upload' ? auth()->id() : null,
            ]);

            if ($thumb = self::makeThumb($abs, $media->id, $info)) {
                $media->update(['thumb' => $thumb]);
            }

            return $media;
        } catch (Throwable) {
            return null;
        }
    }

    /** storage/news/2026/11/x.webp => news ; img/clients/a/1.png => clients ; img/logo.png => img */
    public static function folderOf(string $path): string
    {
        $parts = explode('/', $path);

        if ($parts[0] === 'storage') {
            return $parts[1] ?? 'lainnya';
        }

        if ($parts[0] === 'img') {
            return count($parts) > 2 ? $parts[1] : 'img';
        }

        return $parts[0];
    }

    /** Thumbnail WebP lebar 480 px; null bila tak perlu (sudah kecil) atau GD tak bisa. */
    protected static function makeThumb(string $abs, int $id, array|false $info): ?string
    {
        if (! $info || ($info[0] ?? 0) <= self::THUMB_WIDTH) {
            return null;
        }

        if (! function_exists('imagewebp') || ! function_exists('imagecreatefromstring')
            || filesize($abs) > 15 * 1024 * 1024 || ($info[0] * $info[1]) > 40_000_000) {
            return null;
        }

        $raw = @file_get_contents($abs);
        $im  = $raw === false ? false : @imagecreatefromstring($raw);

        if (! $im) {
            return null;
        }

        $scaled = imagescale($im, self::THUMB_WIDTH, (int) max(1, round(imagesy($im) * self::THUMB_WIDTH / imagesx($im))));
        imagedestroy($im);

        if (! $scaled) {
            return null;
        }

        imagealphablending($scaled, false);
        imagesavealpha($scaled, true);

        ob_start();
        $ok   = imagewebp($scaled, null, 78);
        $data = ob_get_clean();
        imagedestroy($scaled);

        if (! $ok || ! $data) {
            return null;
        }

        Storage::disk('public')->put("thumbs/{$id}.webp", $data);

        return "storage/thumbs/{$id}.webp";
    }

    /**
     * Path gambar yang dipilih dari Media Library (kolom `image_path` di formulir): hanya path berbentuk
     * storage/..., img/..., atau wp-uploads/... tanpa ".." dan berkasnya benar-benar ada. Selain itu null.
     */
    public static function pick(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '' || strlen($path) > 255 || str_contains($path, '..')
            || ! preg_match('#^(storage|img|wp-uploads)/[A-Za-z0-9_./-]+$#', $path)) {
            return null;
        }

        return is_file(Media::absolute($path)) ? $path : null;
    }

    /** Hapus catatan Media Library untuk berkas yang sudah dihapus modul lain (ImageStore::delete). */
    public static function forget(string $path): void
    {
        try {
            if (self::ready() && $media = Media::where('path', $path)->first()) {
                if ($media->thumb) {
                    Storage::disk('public')->delete(substr($media->thumb, 8));
                }
                $media->delete();
            }
        } catch (Throwable) {
            // katalog tidak boleh menggagalkan penghapusan
        }
    }

    /** Hapus catatan + berkas unggahan + thumbnail. Aset bawaan tidak pernah dihapus dari disk. */
    public static function remove(Media $media): void
    {
        if ($media->thumb) {
            ImageStore::delete($media->thumb);
        }

        if (! $media->isBundled()) {
            ImageStore::delete($media->path);
        }

        $media->delete();
    }
}
