<?php
namespace App\Console\Commands;

use App\Models\News;
use App\Models\UrlRedirect;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * B3n + B6: membuat Redirect 301 dari URL WordPress lama ke alamat berita baru (/id/berita/{slug}).
 * Pemetaan lewat News.wp_id (diisi oleh content:import-wp), jadi tetap benar walau slug baru berbeda.
 * Dibuat dua pola umum: /{slug-wp} dan /{tahun}/{bulan}/{slug-wp}. Idempoten.
 *
 *   php artisan redirects:import-wp --dry-run
 *   php artisan redirects:import-wp
 */
class ImportWpRedirects extends Command
{
    protected $signature = 'redirects:import-wp {--dry-run : Hanya hitung, tanpa menulis} {--json=database/imports/wp_news.json : Berkas data WordPress}';

    protected $description = 'Buat redirect 301 dari URL WordPress lama ke alamat berita baru';

    public function handle(): int
    {
        if (! Schema::hasTable('redirects') || ! Schema::hasColumn('news', 'wp_id')) {
            $this->error('Tabel redirects atau kolom news.wp_id belum ada. Jalankan dulu: php artisan migrate');

            return self::FAILURE;
        }

        $path = base_path($this->option('json'));

        if (! is_file($path)) {
            $this->error("Berkas tidak ditemukan: {$this->option('json')}");

            return self::FAILURE;
        }

        $rows = json_decode((string) file_get_contents($path), true);

        if (! is_array($rows)) {
            $this->error('Isi berkas JSON tidak terbaca.');

            return self::FAILURE;
        }

        $news = News::whereNotNull('wp_id')->pluck('slug', 'wp_id');
        $dry  = (bool) $this->option('dry-run');
        $created = $updated = $unchanged = $missing = 0;

        foreach ($rows as $r) {
            $slug = $news[$r['wp_id'] ?? ''] ?? null;

            if (! $slug || empty($r['slug'])) {
                $missing++;
                continue;
            }

            $to    = route('id.news.show', ['slug' => $slug], false); // /id/berita/{slug}
            $date  = strtotime((string) ($r['date'] ?? ''));
            $froms = ['/' . $r['slug']];

            if ($date) {
                $froms[] = '/' . date('Y', $date) . '/' . date('m', $date) . '/' . $r['slug'];
            }

            foreach ($froms as $raw) {
                $from = UrlRedirect::normalizeFrom($raw);

                if ($from === '' || $from === UrlRedirect::normalizeFrom($to) || UrlRedirect::isBlocked($from)) {
                    continue;
                }

                $rule = UrlRedirect::where('from_path', $from)->first();

                if ($rule && $rule->to_path === $to) {
                    $unchanged++;
                } elseif ($rule) {
                    $updated++;
                    if (! $dry) {
                        $rule->update(['to_path' => $to, 'status_code' => 301, 'is_active' => true]);
                    }
                } else {
                    $created++;
                    if (! $dry) {
                        UrlRedirect::create(['from_path' => $from, 'to_path' => $to, 'status_code' => 301, 'is_active' => true, 'note' => 'Impor WordPress']);
                    }
                }
            }
        }

        $this->info(($dry ? '[dry-run] ' : '') . "Dibuat: $created, diperbarui: $updated, tidak berubah: $unchanged, berita belum diimpor/dilewati: $missing");

        return self::SUCCESS;
    }
}
