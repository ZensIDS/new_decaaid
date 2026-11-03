<?php
namespace App\Console\Commands;

use App\Models\Media;
use App\Support\MediaStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * B3b: mendaftarkan berkas lama ke Media Library: unggahan di storage/app/public dan aset bawaan di public/img
 * (7 logo pilar, 90 logo klien, logo brand, dst.; QA7 butir 4). Idempoten: yang sudah terdaftar dilewati.
 * Folder public/wp-uploads (gambar berita WordPress) sengaja tidak dipindai; pakai --wp bila ingin.
 */
class SyncMedia extends Command
{
    protected $signature = 'media:sync {--only= : uploads atau bundled saja} {--wp : ikut pindai public/wp-uploads}';

    protected $description = 'Daftarkan gambar lama (storage dan public/img) ke Media Library (idempoten)';

    protected const EXT = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'];

    public function handle(): int
    {
        if (! Schema::hasTable('media')) {
            $this->error('Tabel media belum ada. Jalankan dulu: php artisan migrate');

            return self::FAILURE;
        }

        $only = $this->option('only');
        $new  = $skipped = 0;

        $register = function (string $path) use (&$new, &$skipped) {
            if (Media::where('path', $path)->exists()) {
                $skipped++;

                return;
            }

            MediaStore::register($path) ? $new++ : $skipped++;
        };

        if ($only !== 'bundled') {
            foreach (Storage::disk('public')->allFiles() as $file) {
                if (str_starts_with($file, 'thumbs/') || ! in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), self::EXT, true)) {
                    continue;
                }
                $register('storage/' . $file);
            }
        }

        if ($only !== 'uploads') {
            $roots = ['img'];
            if ($this->option('wp')) {
                $roots[] = 'wp-uploads';
            }

            foreach ($roots as $root) {
                $base = public_path($root);

                if (! is_dir($base)) {
                    continue;
                }

                foreach (File::allFiles($base) as $f) {
                    if (in_array(strtolower($f->getExtension()), self::EXT, true)) {
                        $register($root . '/' . str_replace('\\', '/', $f->getRelativePathname()));
                    }
                }
            }
        }

        $this->info("Media: {$new} terdaftar, {$skipped} dilewati. Total sekarang " . Media::count() . '.');

        return self::SUCCESS;
    }
}
