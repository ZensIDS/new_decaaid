<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Memangkas ruang kosong (transparan/putih) di sekeliling logo PNG, supaya logo
 * tampil proporsional dan ukurannya bisa diatur lewat class Tailwind.
 *
 * docker-compose exec laravel.test php artisan logo:trim
 *
 * File asli dicadangkan ke storage/app/logo-backup (tidak menimpa cadangan lama).
 */
class TrimLogos extends Command
{
    protected $signature = 'logo:trim {--dir=img/logo : Folder PNG di dalam public/}';

    protected $description = 'Pangkas ruang kosong di sekeliling logo PNG (asli dicadangkan)';

    public function handle(): int
    {
        if (! function_exists('imagecropauto')) {
            $this->error('Ekstensi PHP GD tidak tersedia, perintah tidak bisa dijalankan.');

            return self::FAILURE;
        }

        $dir = public_path(trim((string) $this->option('dir'), '/'));
        $files = glob($dir.'/*.png') ?: [];

        if (! $files) {
            $this->warn("Tidak ada file PNG di {$dir}");

            return self::SUCCESS;
        }

        $backup = storage_path('app/logo-backup');
        if (! is_dir($backup)) {
            mkdir($backup, 0775, true);
        }

        foreach ($files as $file) {
            $name = basename($file);
            $im = @imagecreatefrompng($file);

            if (! $im) {
                $this->warn("{$name}: bukan PNG yang valid, dilewati");
                continue;
            }

            $w = imagesx($im);
            $h = imagesy($im);
            $crop = imagecropauto($im, IMG_CROP_DEFAULT);

            if ($crop === false || (imagesx($crop) === $w && imagesy($crop) === $h)) {
                $this->line("{$name}: tidak ada margin yang bisa dipangkas ({$w}x{$h})");
                continue;
            }

            if (! file_exists($backup.'/'.$name)) {
                copy($file, $backup.'/'.$name);
            }

            imagealphablending($crop, false);
            imagesavealpha($crop, true);
            imagepng($crop, $file, 9);

            $this->info(sprintf('%s: %dx%d -> %dx%d', $name, $w, $h, imagesx($crop), imagesy($crop)));
        }

        return self::SUCCESS;
    }
}