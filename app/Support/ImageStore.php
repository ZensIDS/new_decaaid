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
 * Setiap unggahan otomatis didaftarkan ke Media Library (MediaStore, B3b).
 */
class ImageStore
{
    public const MAX_KB    = 5120;
    public const MAX_WIDTH = 1920;
    public const MIMES     = ['image/jpeg', 'image/png', 'image/webp'];

    /** Batas piksel unggahan (cegah "gambar bom" kecil di berkas tetapi sangat besar saat didekode GD). Dipakai di aturan validasi semua unggahan. */
    public const DIMENSIONS = 'dimensions:max_width=8000,max_height=8000';

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

        MediaStore::register('storage/' . $path, $file->getClientOriginalName()); // B3b: otomatis masuk Media Library

        return 'storage/' . $path;
    }

    /**
     * Simpan logo (QA7 butir 3): ruang kosong di sekeliling logo dipangkas otomatis (seperti LogoTrim
     * untuk logo bawaan), lebar maksimal 800 px, disimpan sebagai WebP (atau PNG bila WebP tak ada),
     * transparansi dipertahankan. Bila pemrosesan gagal, berkas disimpan apa adanya lewat store().
     */
    public static function storeLogo(UploadedFile $file, string $folder): string
    {
        $out = self::trimmedLogo($file);

        if ($out === null) {
            return self::store($file, $folder);
        }

        [$data, $ext] = $out;

        $dir  = trim($folder, '/') . '/' . date('Y/m');
        $path = $dir . '/' . Str::lower(Str::random(20)) . '.' . $ext;

        Storage::disk('public')->put($path, $data);

        MediaStore::register('storage/' . $path, $file->getClientOriginalName()); // B3b

        return 'storage/' . $path;
    }

    /** @return array{0: string, 1: string}|null [isi berkas, ekstensi] atau null bila GD tidak bisa memproses */
    protected static function trimmedLogo(UploadedFile $file): ?array
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagecrop') || ! self::ensureMemory($file)) {
            return null;
        }

        $raw = @file_get_contents($file->getRealPath());
        $im  = $raw === false ? false : @imagecreatefromstring($raw);

        if (! $im) {
            return null;
        }

        // Kecilkan dulu (maks 1200 px) supaya pemindaian piksel tetap cepat pada berkas besar.
        $w = imagesx($im);
        $h = imagesy($im);

        if ($w > 1200) {
            $scaled = imagescale($im, 1200, (int) max(1, round($h * 1200 / $w)));
            if ($scaled) {
                imagedestroy($im);
                $im = $scaled;
                $w  = imagesx($im);
                $h  = imagesy($im);
            }
        }

        if (! imageistruecolor($im)) {
            imagepalettetotruecolor($im);
        }

        // Warna latar diperkirakan dari piksel pojok kiri atas.
        $bg       = imagecolorat($im, 0, 0);
        $bgOpaque = (($bg >> 24) & 127) < 10;
        $br       = ($bg >> 16) & 255;
        $bgG      = ($bg >> 8) & 255;
        $bb       = $bg & 255;

        $minX = $w;
        $minY = $h;
        $maxX = -1;
        $maxY = -1;

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $c = imagecolorat($im, $x, $y);

                if ((($c >> 24) & 127) >= 115) {
                    continue; // praktis transparan
                }

                if ($bgOpaque
                    && abs((($c >> 16) & 255) - $br) < 14
                    && abs((($c >> 8) & 255) - $bgG) < 14
                    && abs(($c & 255) - $bb) < 14) {
                    continue; // sama dengan warna latar
                }

                if ($x < $minX) { $minX = $x; }
                if ($y < $minY) { $minY = $y; }
                if ($x > $maxX) { $maxX = $x; }
                if ($y > $maxY) { $maxY = $y; }
            }
        }

        if ($maxX >= 0) {
            $pad  = 2;
            $x0   = max(0, $minX - $pad);
            $y0   = max(0, $minY - $pad);
            $crop = imagecrop($im, [
                'x'      => $x0,
                'y'      => $y0,
                'width'  => min($w - 1, $maxX + $pad) - $x0 + 1,
                'height' => min($h - 1, $maxY + $pad) - $y0 + 1,
            ]);

            if ($crop) {
                imagedestroy($im);
                $im = $crop;
            }
        }

        if (imagesx($im) > 800) {
            $scaled = imagescale($im, 800, (int) max(1, round(imagesy($im) * 800 / imagesx($im))));
            if ($scaled) {
                imagedestroy($im);
                $im = $scaled;
            }
        }

        imagealphablending($im, false);
        imagesavealpha($im, true);

        ob_start();
        if (function_exists('imagewebp')) {
            $ok  = imagewebp($im, null, 90);
            $ext = 'webp';
        } else {
            $ok  = imagepng($im);
            $ext = 'png';
        }
        $data = ob_get_clean();
        imagedestroy($im);

        return $ok && $data ? [$data, $ext] : null;
    }

    /**
     * Hapus berkas hasil unggahan (path dari store()). Aset bawaan di public/img tidak disentuh.
     * Berkas yang masih dipakai di modul mana pun dibiarkan (hapus lewat Media Library setelah dilepas).
     * Panggil SETELAH rekaman pemakainya disimpan/dihapus, supaya pemindaian tidak menghitungnya.
     */
    public static function delete(?string $path): void
    {
        if ($path && str_starts_with($path, 'storage/')) {
            // B3b: gambar bisa dipakai bersama modul lain (dipilih dari Media Library); jangan hapus bila masih dipakai.
            if (self::stillUsed($path)) {
                return;
            }

            Storage::disk('public')->delete(substr($path, strlen('storage/')));
            ImageVariants::forget($path); // v90: varian srcset ikut dihapus
            MediaStore::forget($path); // B3b: hapus catatan di Media Library
        }
    }

    protected static function stillUsed(string $path): bool
    {
        try {
            return count(MediaUsage::find($path)) > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Penjaga memori (v70): GD mendekode gambar menjadi ~4 byte per piksel, jadi foto 24 MP butuh ~100 MB dan
     * gambar 8000 x 8000 ~256 MB; melebihi memory_limit PHP berarti galat fatal (500) yang tidak bisa ditangkap.
     * Bila memori tidak cukup, coba naikkan memory_limit untuk permintaan ini (maks. 1 GB); bila tetap tidak
     * cukup, kembalikan false sehingga berkas disimpan apa adanya tanpa didekode.
     */
    protected static function ensureMemory(UploadedFile $file): bool
    {
        $size = @getimagesize($file->getRealPath());

        if (! $size) {
            return true; // bukan gambar yang bisa dibaca ukurannya; biarkan alur biasa yang memutuskan
        }

        $limit = self::bytes((string) ini_get('memory_limit'));

        if ($limit < 0) {
            return true; // tanpa batas
        }

        $need = ($size[0] * $size[1] * 5) + 32 * 1024 * 1024 + memory_get_usage(true);

        if ($need <= $limit) {
            return true;
        }

        $target = min((int) ceil($need / 1048576) + 16, 1024);
        @ini_set('memory_limit', $target . 'M');

        $now = self::bytes((string) ini_get('memory_limit'));

        return $now < 0 || $need <= $now;
    }

    /** "128M", "1G", "512K", "-1" menjadi byte. */
    public static function bytes(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '-1') {
            return -1;
        }

        $unit = strtolower(substr($value, -1));
        $num  = (float) $value;

        return (int) match ($unit) {
            'g'     => $num * 1073741824,
            'm'     => $num * 1048576,
            'k'     => $num * 1024,
            default => $num,
        };
    }

    /** @return string|null isi WebP, atau null bila GD tidak tersedia / gagal */
    protected static function toWebp(UploadedFile $file): ?string
    {
        if (! function_exists('imagewebp') || ! function_exists('imagecreatefromstring') || ! self::ensureMemory($file)) {
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