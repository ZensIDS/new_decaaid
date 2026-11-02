<?php
namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Pillar;
use App\Support\Pillars;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * B3c: memindahkan 8 pilar dan seluruh brand dari kode (App\Support\Pillars) ke database.
 * Idempoten: pilar/brand yang sudah ada dilewati (suntingan admin tidak tertimpa). Pakai --refresh
 * untuk menimpa brand dummy yang BELUM pernah diubah lewat admin (is_dummy = true).
 * Tautan diimpor apa adanya (Q15); normalisasi ke https:// terjadi saat brand disimpan lewat admin.
 */
class ImportPillars extends Command
{
    protected $signature = 'content:import-pillars {--refresh : Timpa ulang brand dummy yang belum disentuh admin}';

    protected $description = 'Impor pilar bisnis dan brand dari kode ke database (idempoten)';

    /** Pilar yang isinya masih narasi buatan (belum ada di dokumen): brand-nya ditandai Dummy. */
    protected const DUMMY_PILLARS = ['commercial-technology'];

    public function handle(): int
    {
        if (! Schema::hasTable('pillars') || ! Schema::hasTable('brands')) {
            $this->error('Tabel pilar belum ada. Jalankan dulu: php artisan migrate');

            return self::FAILURE;
        }

        $pillarsNew = $brandsNew = $brandsUpdated = $brandsSkipped = 0;
        $pillarOrder = 0;

        foreach (Pillars::all() as $row) {
            $pillarOrder++;

            $pillar = Pillar::where('slug', $row['slug'])->first();

            if (! $pillar) {
                $pillar = Pillar::create([
                    'slug'       => $row['slug'],
                    'name'       => $row['name'],
                    'icon'       => $row['icon'] ?? 'briefcase',
                    'sort_order' => $pillarOrder,
                    'is_visible' => true,
                ]);
                $pillarsNew++;
            }

            $dummy      = in_array($row['slug'], self::DUMMY_PILLARS, true);
            $brandOrder = 0;

            foreach ($row['brands'] as $b) {
                $brandOrder++;

                $existing = Brand::where('slug', $b['slug'])->first();

                if ($existing && ! ($this->option('refresh') && $existing->is_dummy)) {
                    $brandsSkipped++;

                    continue;
                }

                $data = [
                    'pillar_id'          => $pillar->id,
                    'name'               => $b['name'],
                    'logo'               => $b['logo'] ?? null,
                    'description'        => $this->bilingual($b['description'] ?? null),
                    'instagram'          => $b['instagram'] ?? null,
                    'tiktok'             => $b['tiktok'] ?? null,
                    'url'                => $b['url'] ?? null,
                    'services'           => $this->services($b['services'] ?? null),
                    'sort_order'         => $brandOrder,
                    'is_visible'         => true,
                    'translation_status' => 'auto', // EN = terjemahan draf, belum ditinjau (K26)
                    'is_dummy'           => $dummy,
                ];

                if ($existing) {
                    $existing->update($data);
                    $brandsUpdated++;
                } else {
                    Brand::create(['slug' => $b['slug']] + $data);
                    $brandsNew++;
                }
            }
        }

        $this->info("Pilar: {$pillarsNew} dibuat. Brand: {$brandsNew} dibuat, {$brandsUpdated} diperbarui, {$brandsSkipped} dilewati.");

        return self::SUCCESS;
    }

    /** @return array{id: string, en: string}|null */
    protected function bilingual(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $id = trim((string) ($value['id'] ?? ''));
        $en = trim((string) ($value['en'] ?? ''));

        return ($id === '' && $en === '') ? null : ['id' => $id, 'en' => $en];
    }

    protected function services(mixed $services): ?array
    {
        if (! is_array($services) || empty($services['items'])) {
            return null;
        }

        return [
            'title' => $this->bilingual($services['title'] ?? null) ?? ['id' => '', 'en' => ''],
            'items' => array_map(fn(array $item) => [
                'title' => $this->bilingual($item['title'] ?? null) ?? ['id' => '', 'en' => ''],
                'text'  => $this->bilingual($item['text'] ?? null) ?? ['id' => '', 'en' => ''],
            ], $services['items']),
        ];
    }
}