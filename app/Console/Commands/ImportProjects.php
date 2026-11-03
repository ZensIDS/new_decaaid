<?php
namespace App\Console\Commands;

use App\Models\Pillar;
use App\Models\Project;
use App\Support\Content;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * B3g: memindahkan 9 proyek contoh (DUMMY) dari kode (Content::projectSeed) ke database.
 * Idempoten: yang sudah ada (slug sama) dilewati, jadi suntingan admin tidak tertimpa.
 * Pakai --refresh untuk menimpa proyek contoh yang BELUM pernah diubah lewat admin (is_dummy = true).
 */
class ImportProjects extends Command
{
    protected $signature = 'content:import-projects {--refresh : Timpa ulang proyek contoh yang belum diubah admin}';

    protected $description = 'Impor proyek contoh (dummy) ke database (idempoten)';

    public function handle(): int
    {
        if (! Schema::hasTable('projects') || ! Schema::hasTable('pillars')) {
            $this->error('Tabel proyek belum ada. Jalankan dulu: php artisan migrate');

            return self::FAILURE;
        }

        $pillars = Pillar::pluck('id', 'slug');

        if ($pillars->isEmpty()) {
            $this->error('Pilar bisnis belum ada di database. Jalankan dulu: php artisan content:import-pillars');

            return self::FAILURE;
        }

        $created = $updated = $skipped = 0;
        $missing = [];

        foreach (Content::projectSeed() as $i => $row) {
            $pillarId = $pillars[$row['unit']] ?? null;

            if (! $pillarId) {
                $missing[$row['unit']] = true;
            }

            $existing = Project::where('slug', $row['slug'])->first();

            if ($existing && ! ($this->option('refresh') && $existing->is_dummy)) {
                $skipped++;

                continue;
            }

            $data = [
                'pillar_id'          => $pillarId,
                'title'              => $row['title'],
                'client'             => $row['client'],
                'year'               => $row['year'],
                'summary'            => $row['summary'],
                'description'        => $row['description'],
                'scope'              => [
                    'id' => array_column($row['scope'], 'id'),
                    'en' => array_column($row['scope'], 'en'),
                ],
                'image'              => $row['image'],
                'image_alt'          => $row['title'],
                'is_featured'        => $i < Project::HOME_LIMIT, // sama seperti beranda lama (3 pertama)
                'is_visible'         => true,
                'translation_status' => 'manual',
                'is_dummy'           => true,
            ];

            if ($existing) {
                $existing->update($data);
                $updated++;
            } else {
                Project::create(['slug' => $row['slug']] + $data);
                $created++;
            }
        }

        $this->info("Proyek: {$created} dibuat, {$updated} diperbarui, {$skipped} dilewati.");

        if ($missing) {
            $this->warn('Pilar tidak ditemukan untuk: ' . implode(', ', array_keys($missing)) . '. Proyek terkait dibuat tanpa pilar; pilih pilarnya di admin.');
        }

        return self::SUCCESS;
    }
}