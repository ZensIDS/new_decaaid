<?php
namespace App\Console\Commands;

use App\Models\News;
use App\Models\NewsCategory;
use App\Support\WpNewsConverter as W;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Impor berita lama WordPress (data siap pakai di database/imports/wp_news.json).
 *
 *   php artisan content:import-wp --dry-run     # simulasi, tidak menulis apa pun
 *   php artisan content:import-wp               # impor sungguhan
 *   php artisan content:import-wp --refresh     # perbarui berita impor yang belum pernah disunting di admin
 *
 * TIDAK memanggil penerjemah: kolom EN dikosongkan (pengunjung EN melihat teks Indonesia), jadi tidak memakai kuota API.
 * Idempoten: berita yang sudah diimpor (berdasarkan wp_id) dilewati.
 */
class ImportWpNews extends Command
{
    protected $signature = 'content:import-wp
                            {--file=database/imports/wp_news.json : Lokasi berkas JSON}
                            {--dry-run : Simulasi saja}
                            {--refresh : Timpa berita impor yang belum disunting di admin}
                            {--skip-images : Lanjut walau folder public/wp-uploads belum ada}';

    protected $description = 'Impor berita lama WordPress ke database (tanpa terjemahan)';

    public function handle(): int
    {
        if (! Schema::hasTable('news') || ! Schema::hasColumn('news', 'wp_id')) {
            $this->error('Kolom wp_id belum ada. Jalankan dulu: php artisan migrate');

            return self::FAILURE;
        }

        $file = base_path($this->option('file'));
        if (! is_file($file)) {
            $this->error("Berkas tidak ditemukan: {$file}");

            return self::FAILURE;
        }

        $rows = json_decode(file_get_contents($file), true);
        if (! is_array($rows)) {
            $this->error('Isi JSON tidak valid.');

            return self::FAILURE;
        }

        $uploads = public_path(W::UPLOADS_DIR);
        if (! is_dir($uploads) && ! $this->option('skip-images')) {
            $this->error("Folder gambar belum ada: {$uploads}");
            $this->line('Salin isi wp-content/uploads dari hosting lama ke folder itu (struktur tahun/bulan dan nama file jangan diubah),');
            $this->line('lalu jalankan ulang. Atau pakai --skip-images bila ingin impor teks dulu.');

            return self::FAILURE;
        }

        $dry     = (bool) $this->option('dry-run');
        $refresh = (bool) $this->option('refresh');

        // Kategori dari WordPress (nama proper noun: sama untuk ID dan EN, tidak diterjemahkan).
        $cats  = [];
        $order = (int) NewsCategory::max('sort_order');
        foreach (W::CATEGORY_NAMES as $slug => $name) {
            $cat = NewsCategory::where('slug', $slug)->first();
            if (! $cat && ! $dry) {
                $cat = NewsCategory::create(['slug' => $slug, 'name' => ['id' => $name, 'en' => $name], 'sort_order' => ++$order]);
            }
            $cats[$slug] = $cat?->id;
        }

        $taken    = News::pluck('slug')->all();
        $reserved = array_merge(News::RESERVED_SLUGS, \App\Models\NewsCategory::RESERVED_SLUGS);
        $stat     = ['dibuat' => 0, 'diperbarui' => 0, 'dilewati' => 0];
        $noThumb  = [];
        $missing  = [];

        $bar = $this->output->createProgressBar(count($rows));

        foreach ($rows as $r) {
            $bar->advance();

            $existing = News::where('wp_id', $r['wp_id'])->first();

            if ($existing && ! ($refresh && $existing->updated_by === null)) {
                $stat['dilewati']++;

                continue;
            }

            $title = W::title($r['title']);
            $body  = W::body($r['content']);
            $slug  = $existing ? $existing->slug : W::slug($r['slug'], $title, $taken, $reserved);
            $taken[] = $slug;

            // Gambar unggulan: ukuran "large" bila ada (nama file asli WordPress), lalu cadangan lain.
            $image = null;
            foreach ($r['thumb']['candidates'] ?? [] as $cand) {
                if (is_file($uploads . '/' . $cand)) {
                    $image = W::UPLOADS_DIR . '/' . $cand;
                    break;
                }
            }
            if (! $image) {
                $noThumb[] = $slug;
            }

            foreach (W::localImages($body) as $img) {
                if (! is_file($uploads . '/' . $img)) {
                    $missing[] = $img;
                }
            }

            $alt = trim($r['thumb']['alt'] ?? '') ?: $title;
            $cat = W::category($r['categories'] ?? []);

            $data = [
                'news_category_id'   => $cat ? ($cats[$cat] ?? null) : null,
                'title'              => ['id' => $title, 'en' => ''],
                'excerpt'            => ['id' => W::excerpt($r['excerpt'] ?? '', $body), 'en' => ''],
                'body'               => ['id' => $body, 'en' => ''],
                'image'              => $image,
                'image_alt'          => ['id' => $alt, 'en' => ''],
                'status'             => News::STATUS_PUBLISHED,
                'published_at'       => $r['date'],
                'translation_status' => 'manual',
                'translated_from'    => null,
                'is_dummy'           => false,
            ];

            if ($dry) {
                $stat[$existing ? 'diperbarui' : 'dibuat']++;

                continue;
            }

            DB::transaction(function () use ($existing, $slug, $data, $r, &$stat) {
                if ($existing) {
                    $existing->forceFill($data)->save();
                    $stat['diperbarui']++;
                } else {
                    $news = new News();
                    $news->forceFill(['wp_id' => $r['wp_id'], 'slug' => $slug] + $data);
                    $news->created_at = $r['date'];
                    $news->updated_at = $r['modified'] ?? $r['date'];
                    $news->timestamps = false; // pertahankan tanggal asli WordPress
                    $news->save();
                    $stat['dibuat']++;
                }
            });
        }

        $bar->finish();
        $this->newLine(2);

        $this->info(($dry ? '[SIMULASI] ' : '') . "Berita: {$stat['dibuat']} dibuat, {$stat['diperbarui']} diperbarui, {$stat['dilewati']} dilewati.");

        if ($noThumb) {
            $this->warn(count($noThumb) . ' berita tanpa gambar unggulan (file tidak ditemukan di public/' . W::UPLOADS_DIR . '). Contoh: ' . implode(', ', array_slice($noThumb, 0, 5)));
        }
        if ($missing) {
            $missing = array_values(array_unique($missing));
            $this->warn(count($missing) . ' gambar di dalam isi berita tidak ditemukan. Contoh:');
            foreach (array_slice($missing, 0, 10) as $m) {
                $this->line("  - {$m}");
            }
        }
        if (! $noThumb && ! $missing) {
            $this->info('Semua gambar ditemukan.');
        }

        return self::SUCCESS;
    }
}