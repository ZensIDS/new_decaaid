<?php
namespace App\Console\Commands;

use App\Models\News;
use App\Models\NewsCategory;
use App\Support\Content;
use App\Support\Html;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * B3d: memindahkan berita dan kategori dummy dari kode (Content::newsSeed) ke database.
 * Idempoten: yang sudah ada dilewati (suntingan admin tidak tertimpa). Pakai --refresh
 * untuk menimpa item dummy yang BELUM pernah diubah lewat admin (is_dummy = true).
 */
class ImportNews extends Command
{
    protected $signature = 'content:import-news {--refresh : Timpa ulang berita dummy dari kode}';

    protected $description = 'Impor kategori dan berita dummy ke database (idempoten)';

    /** Kategori awal. "community" berganti nama jadi Keberlanjutan (K16, QA12), slug tetap. */
    protected const CATEGORIES = [
        'activities' => ['Kegiatan', 'Activities'],
        'community'  => ['Keberlanjutan', 'Sustainability'],
        'business'   => ['Bisnis', 'Business'],
        'technology' => ['Teknologi', 'Technology'],
        'education'  => ['Edukasi', 'Education'],
    ];

    public function handle(): int
    {
        if (! Schema::hasTable('news')) {
            $this->error('Tabel berita belum ada. Jalankan dulu: php artisan migrate');

            return self::FAILURE;
        }

        $order = 0;
        $cats  = [];
        foreach (self::CATEGORIES as $slug => [$id, $en]) {
            $cat = NewsCategory::firstOrCreate(
                ['slug' => $slug],
                ['name' => ['id' => $id, 'en' => $en], 'sort_order' => ++$order]
            );
            $cats[$slug] = $cat->id;
        }

        $created = $updated = $skipped = 0;

        foreach (Content::newsSeed() as $row) {
            $existing = News::where('slug', $row['slug'])->first();

            if ($existing && ! ($this->option('refresh') && $existing->is_dummy)) {
                $skipped++;

                continue;
            }

            $html = ['id' => '', 'en' => ''];
            foreach ($row['body'] as $block) {
                foreach (['id', 'en'] as $lang) {
                    $html[$lang] .= isset($block['h'])
                        ? '<h2>' . e($block['h'][$lang]) . '</h2>'
                        : '<p>' . e($block[$lang]) . '</p>';
                }
            }

            $data = [
                'news_category_id'   => $cats[$row['category_slug']] ?? null,
                'title'              => $row['title'],
                'excerpt'            => $row['excerpt'],
                'body'               => ['id' => Html::clean($html['id']), 'en' => Html::clean($html['en'])],
                'image'              => $row['image'],
                'image_alt'          => $row['title'],
                'status'             => News::STATUS_PUBLISHED,
                'published_at'       => $row['date'] . ' 09:00:00',
                'translation_status' => 'manual',
                'is_dummy'           => true,
            ];

            if ($existing) {
                $existing->update($data);
                $updated++;
            } else {
                News::create(['slug' => $row['slug']] + $data);
                $created++;
            }
        }

        $this->info("Kategori: " . count($cats) . " siap. Berita: {$created} dibuat, {$updated} diperbarui, {$skipped} dilewati.");

        return self::SUCCESS;
    }
}
