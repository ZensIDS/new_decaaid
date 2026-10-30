<?php

namespace App\Support;

/**
 * Logo PNG sering punya ruang kosong (transparan/putih) yang besar di sekelilingnya,
 * sehingga tampil jauh lebih kecil dari kotaknya. Kelas ini membuat salinan logo yang
 * sudah dipangkas otomatis (sekali saja, lalu disimpan) di public/img/_trim/.
 * File asli tidak disentuh. Bila apa pun gagal, logo asli yang dipakai.
 */
class LogoTrim
{
    /** @var array<string, string> */
    protected static array $memo = [];

    /** URL logo yang sudah dipangkas (atau URL asli bila tidak bisa). */
    public static function url(string $path): string
    {
        $path = ltrim($path, '/');

        if (isset(self::$memo[$path])) {
            return self::$memo[$path];
        }

        $url = asset($path);

        try {
            $src = public_path($path);

            if (is_file($src)
                && strtolower(pathinfo($src, PATHINFO_EXTENSION)) === 'png'
                && function_exists('imagecreatefrompng')
                && function_exists('imagecrop')) {
                $name = pathinfo($src, PATHINFO_FILENAME);
                $rel = 'img/_trim/'.$name.'-'.filemtime($src).'.png';
                $dest = public_path($rel);

                if (is_file($dest) || self::make($src, $dest, $name)) {
                    $url = asset($rel);
                }
            }
        } catch (\Throwable $e) {
            // Gagal memangkas: pakai logo asli.
        }

        return self::$memo[$path] = $url;
    }

    protected static function make(string $src, string $dest, string $name): bool
    {
        $im = @imagecreatefrompng($src);
        if (! $im) {
            return false;
        }

        if (! imageistruecolor($im)) {
            imagepalettetotruecolor($im);
        }

        $w = imagesx($im);
        $h = imagesy($im);

        // Warna latar diperkirakan dari piksel pojok kiri atas.
        $bg = imagecolorat($im, 0, 0);
        $bgOpaque = (($bg >> 24) & 127) < 10;
        [$br, $bgG, $bb] = [($bg >> 16) & 255, ($bg >> 8) & 255, $bg & 255];

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

                $minX = min($minX, $x);
                $minY = min($minY, $y);
                $maxX = max($maxX, $x);
                $maxY = max($maxY, $y);
            }
        }

        if ($maxX < 0) {
            return false;
        }

        $pad = 2;
        $x0 = max(0, $minX - $pad);
        $y0 = max(0, $minY - $pad);
        $x1 = min($w - 1, $maxX + $pad);
        $y1 = min($h - 1, $maxY + $pad);

        $crop = imagecrop($im, ['x' => $x0, 'y' => $y0, 'width' => $x1 - $x0 + 1, 'height' => $y1 - $y0 + 1]);
        if (! $crop) {
            return false;
        }

        $dir = dirname($dest);
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return false;
        }

        // Hapus versi lama dari logo yang sama (bila file asli berganti).
        foreach (glob($dir.'/'.$name.'-*.png') ?: [] as $old) {
            @unlink($old);
        }

        imagealphablending($crop, false);
        imagesavealpha($crop, true);

        return imagepng($crop, $dest, 9);
    }
}