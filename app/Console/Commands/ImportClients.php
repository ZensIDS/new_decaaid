<?php
namespace App\Console\Commands;

use App\Models\Client;
use App\Models\ClientCategory;
use App\Support\Content;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * B3h: memindahkan kategori dan logo klien bawaan (public/img/clients/*) ke database.
 * Idempoten: kategori dikenali dari slug, logo dari path berkasnya; yang sudah ada dilewati,
 * jadi suntingan admin tidak tertimpa. 24 logo terdepan (urutan selang-seling) diberi tanda
 * "Tampil di beranda" agar strip beranda sama seperti sebelum impor.
 */
class ImportClients extends Command
{
    protected $signature = 'content:import-clients';

    protected $description = 'Impor kategori dan logo klien bawaan ke database (idempoten)';

    public function handle(): int
    {
        if (! Schema::hasTable('clients') || ! Schema::hasTable('client_categories')) {
            $this->error('Tabel klien belum ada. Jalankan dulu: php artisan migrate');

            return self::FAILURE;
        }

        $seed  = Content::clientSeed();
        $order = 0;
        $cats  = [];

        foreach ($seed['categories'] as $slug => $name) {
            $cats[$slug] = ClientCategory::firstOrCreate(
                ['slug' => $slug],
                ['name' => ['id' => $name['id'], 'en' => $name['en']], 'sort_order' => ++$order]
            )->id;
        }

        $created = $skipped = $missing = 0;
        $homeLeft = 24;

        foreach ($seed['items'] as $row) {
            $isFirstBatch = $homeLeft-- > 0; // 24 logo terdepan = strip beranda lama

            if (! file_exists(public_path($row['logo']))) {
                $missing++;

                continue;
            }

            if (Client::where('logo', $row['logo'])->exists()) {
                $skipped++;

                continue;
            }

            Client::create([
                'client_category_id' => $cats[$row['category']],
                'name'               => null,
                'logo'               => $row['logo'],
                'sort_order'         => $row['n'],
                'is_visible'         => true,
                'show_on_home'       => $isFirstBatch,
                'permission_ok'      => false,
            ]);
            $created++;
        }

        $this->info("Klien: {$created} dibuat, {$skipped} dilewati, " . count($cats) . ' kategori siap.');

        if ($missing > 0) {
            $this->warn("{$missing} logo dilewati karena berkasnya tidak ada di public/img/clients. Periksa folder logo, lalu jalankan ulang.");
        }

        return self::SUCCESS;
    }
}
