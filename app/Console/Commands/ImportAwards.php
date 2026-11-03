<?php
namespace App\Console\Commands;

use App\Models\Award;
use App\Support\Content;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * B3i: memindahkan 6 penghargaan contoh (DUMMY) dari kode (Content::awardSeed) ke database.
 * Idempoten: yang sudah ada (judul ID sama) dilewati, jadi suntingan admin tidak tertimpa.
 * Pakai --refresh untuk menimpa penghargaan contoh yang BELUM pernah diubah lewat admin (is_dummy = true).
 */
class ImportAwards extends Command
{
    protected $signature = 'content:import-awards {--refresh : Timpa ulang penghargaan contoh yang belum diubah admin}';

    protected $description = 'Impor penghargaan contoh (dummy) ke database (idempoten)';

    public function handle(): int
    {
        if (! Schema::hasTable('awards')) {
            $this->error('Tabel penghargaan belum ada. Jalankan dulu: php artisan migrate');

            return self::FAILURE;
        }

        $created = $updated = $skipped = 0;

        foreach (Content::awardSeed() as $row) {
            $existing = Award::query()->get()->first(fn(Award $a) => ($a->title['id'] ?? null) === $row['title']['id']);

            if ($existing && ! ($this->option('refresh') && $existing->is_dummy)) {
                $skipped++;

                continue;
            }

            $data = [
                'title'              => $row['title'],
                'description'        => $row['text'],
                'year'               => (int) $row['year'],
                'image'              => $row['image'],
                'image_alt'          => $row['title'],
                'is_featured'        => (bool) $row['featured'],
                'is_visible'         => true,
                'translation_status' => 'manual',
                'is_dummy'           => true,
            ];

            if ($existing) {
                $existing->update($data);
                $updated++;
            } else {
                Award::create($data);
                $created++;
            }
        }

        $this->info("Penghargaan: {$created} dibuat, {$updated} diperbarui, {$skipped} dilewati.");

        return self::SUCCESS;
    }
}
