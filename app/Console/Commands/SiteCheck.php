<?php
namespace App\Console\Commands;

use App\Support\ImageStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Tahap C/D: pemeriksaan lingkungan sebelum staging/live.
 *   php artisan site:check               pemeriksaan umum (cocok untuk lokal/Docker)
 *   php artisan site:check --production  tambahan aturan produksi (APP_ENV, APP_DEBUG, HTTPS, noindex, email, HSTS)
 * Kode keluar 1 bila ada FAIL, jadi bisa dipasang di skrip deploy. Hanya membaca; tidak mengubah apa pun.
 */
class SiteCheck extends Command
{
    protected $signature = 'site:check {--production : Terapkan aturan produksi (FAIL bila tidak sesuai)}';

    protected $description = 'Periksa lingkungan: PHP, batas unggah, .env, database, storage, jadwal, dan keamanan';

    protected int $fails = 0;

    protected int $warns = 0;

    public function handle(): int
    {
        $prod = (bool) $this->option('production');

        $this->line('');
        $this->info('Pemeriksaan lingkungan DECAA.ID'.($prod ? ' (mode produksi)' : ''));

        // 1. PHP
        $this->section('PHP');
        $this->check(version_compare(PHP_VERSION, '8.0.2', '>='), 'PHP '.PHP_VERSION, 'Laravel 9 butuh PHP 8.0.2 atau lebih baru');
        foreach (['mbstring', 'pdo_mysql', 'fileinfo', 'gd', 'zip', 'openssl'] as $ext) {
            $this->check(extension_loaded($ext), "ekstensi {$ext}", "ekstensi {$ext} belum aktif", in_array($ext, ['zip', 'gd'], true) ? 'warn' : 'fail');
        }
        $this->check(function_exists('imagewebp'), 'GD mendukung WebP', 'GD tanpa WebP: gambar disimpan apa adanya (tanpa konversi dan pengecilan)', 'warn');

        // 2. Batas unggah dan memori
        $this->section('Batas unggah dan memori');
        $min = 6 * 1048576;
        $upload = ImageStore::bytes((string) ini_get('upload_max_filesize'));
        $post   = ImageStore::bytes((string) ini_get('post_max_size'));
        $this->check($upload < 0 || $upload >= $min, 'upload_max_filesize '.ini_get('upload_max_filesize'), 'minimal 6M agar gambar 5 MB lolos dengan pesan dari aplikasi');
        $this->check($post < 0 || $post >= $min, 'post_max_size '.ini_get('post_max_size'), 'minimal 6M (idealnya 40M untuk unggah banyak)');
        $mem = ImageStore::bytes((string) ini_get('memory_limit'));
        $this->check($mem < 0 || $mem >= 256 * 1048576, 'memory_limit '.ini_get('memory_limit'), 'disarankan 256M atau lebih; ImageStore menaikkannya sementara (maks. 1G) saat mendekode gambar besar', 'warn');
        $this->line('  catatan: Nginx perlu `client_max_body_size` minimal 6m (tidak bisa diperiksa dari PHP)');

        // 3. .env
        $this->section('.env dan konfigurasi');
        $env = app()->environment();
        $this->check(! $prod || $env === 'production', "APP_ENV={$env}", 'produksi harus APP_ENV=production');
        $this->check(! $prod || ! config('app.debug'), 'APP_DEBUG='.(config('app.debug') ? 'true' : 'false'), 'produksi harus APP_DEBUG=false', $prod ? 'fail' : 'warn');
        $url = (string) config('app.url');
        $this->check(! $prod || str_starts_with($url, 'https://'), "APP_URL={$url}", 'produksi harus https://', $prod ? 'fail' : 'warn');
        $this->check(filled(config('app.key')), 'APP_KEY terisi', 'jalankan php artisan key:generate');
        $noindex = (bool) config('site.noindex');
        $this->line('  SITE_NOINDEX='.($noindex ? 'true (situs disembunyikan dari mesin pencari)' : 'false'));
        if ($prod && $env === 'production') {
            $this->check(! $noindex, 'SITE_NOINDEX=false di produksi', 'SITE_NOINDEX=true di produksi: situs tidak akan muncul di Google. Boleh hanya untuk staging', 'fail');
        }
        $mailer = (string) config('mail.default');
        $this->check(! $prod || ! in_array($mailer, ['log', 'array'], true), "MAIL_MAILER={$mailer}", 'produksi harus smtp: email Kontak hanya dicatat di log', $prod ? 'fail' : 'warn');
        $this->check(filled(config('mail.from.address')), 'MAIL_FROM_ADDRESS '.config('mail.from.address'), 'isi MAIL_FROM_ADDRESS');
        $this->check(! $prod || (bool) config('security.hsts'), 'SECURITY_HSTS='.(config('security.hsts') ? 'true' : 'false'), 'aktifkan setelah HTTPS stabil', 'warn');
        $csp = (string) config('security.csp');
        $this->line("  SECURITY_CSP={$csp}".($csp === 'report' ? ' (mode lapor; naikkan ke enforce setelah dipantau)' : ''));
        $this->check(config('session.driver') !== 'array', 'SESSION_DRIVER='.config('session.driver'), 'array tidak menyimpan sesi');

        // 4. Database
        $this->section('Database');
        $dbOk = false;
        try {
            DB::connection()->getPdo();
            $dbOk = true;
        } catch (Throwable $e) {
            $this->check(false, '', 'koneksi gagal: '.$e->getMessage());
        }

        if ($dbOk) {
            $this->check(true, 'terhubung ke '.config('database.connections.'.config('database.default').'.database'), '');
            $tables = ['users', 'news', 'projects', 'page_contents', 'media', 'contact_messages', 'activity_logs', 'redirects'];
            $missing = array_values(array_filter($tables, fn($t) => ! Schema::hasTable($t)));
            $this->check($missing === [], 'tabel inti lengkap', 'tabel belum ada: '.implode(', ', $missing).' (php artisan migrate)');
            try {
                $migrator = app('migrator');
                $files    = $migrator->getMigrationFiles(array_merge($migrator->paths(), [database_path('migrations')]));
                $ran      = Schema::hasTable('migrations') ? $migrator->getRepository()->getRan() : [];
                $pending  = array_values(array_diff(array_keys($files), $ran));
                $this->check($pending === [], 'tidak ada migration tertunda', count($pending).' migration tertunda (php artisan migrate)');
            } catch (Throwable $e) {
                $this->check(false, '', 'status migration tidak terbaca: '.$e->getMessage(), 'warn');
            }
            try {
                $admins = Schema::hasTable('users') ? DB::table('users')->where('role', 'super_admin')->where('is_active', 1)->count() : 0;
                $this->check($admins >= 1, "Super Admin aktif: {$admins}", 'belum ada Super Admin aktif');
                if (Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                    $without = DB::table('users')->whereIn('role', ['super_admin', 'admin'])->where('is_active', 1)->whereNull('two_factor_confirmed_at')->count();
                    $this->check($without === 0, 'Super Admin dan Admin aktif semuanya memakai verifikasi 2 langkah', "{$without} akun belum mengaktifkan 2FA (akan diminta saat masuk berikutnya)", $prod ? 'fail' : 'warn');
                } else {
                    $this->check(false, '', 'kolom 2FA belum ada (php artisan migrate)', 'warn');
                }
            } catch (Throwable) {
                $this->line('  (jumlah Super Admin tidak diperiksa: kolom role/is_active tidak ditemukan)');
            }
        }

        // 5. Storage
        $this->section('Storage dan berkas');
        $this->check(is_link(public_path('storage')) || is_dir(public_path('storage')), 'public/storage ada', 'jalankan php artisan storage:link');
        foreach (['storage/app/public', 'storage/logs', 'storage/framework/cache', 'storage/framework/views', 'bootstrap/cache'] as $dir) {
            $this->check(is_dir(base_path($dir)) && is_writable(base_path($dir)), "{$dir} bisa ditulis", "{$dir} tidak ada atau tidak bisa ditulis");
        }
        foreach (['public/img/logo/decaaid-white.png', 'public/favicon.ico'] as $file) {
            $this->check(file_exists(base_path($file)), $file.' ada', $file.' belum ada', 'warn');
        }
        $this->check(! file_exists(public_path('robots.txt')) && ! file_exists(public_path('sitemap.xml')), 'tidak ada robots.txt/sitemap.xml statis', 'hapus berkas statis di public/ agar rute dinamis dipakai', 'warn');
        $this->check(! file_exists(public_path('hot')), 'tidak ada public/hot (Vite dev)', 'hapus public/hot; situs akan mencari server Vite', $prod ? 'fail' : 'warn');
        $this->check(file_exists(public_path('build/manifest.json')), 'public/build ada (npm run build)', 'jalankan npm run build', $prod ? 'fail' : 'warn');

        // 6. Jadwal dan cadangan
        $this->section('Jadwal dan cadangan');
        $this->line('  cron: `* * * * * php /path/artisan schedule:run` harus aktif (messages:prune 02:00, site:backup 03:00)');
        $dir = storage_path('app/backups');
        $latest = is_dir($dir) ? collect(glob($dir.'/*.{gz,zip}', GLOB_BRACE) ?: [])->map(fn($f) => filemtime($f))->max() : null;
        if ($latest) {
            $days = (int) floor((time() - $latest) / 86400);
            $this->check($days <= 2, "cadangan terakhir {$days} hari lalu", "cadangan terakhir {$days} hari lalu: periksa cron", 'warn');
        } else {
            $this->check(false, '', 'belum ada cadangan di storage/app/backups (php artisan site:backup)', 'warn');
        }

        // 7. Cache
        $this->section('Cache (produksi)');
        $this->line('  setelah deploy: php artisan config:cache && php artisan route:cache && php artisan view:cache');
        $this->check(! $prod || app()->configurationIsCached(), 'config ter-cache', 'jalankan php artisan config:cache', 'warn');
        $this->check(! $prod || app()->routesAreCached(), 'rute ter-cache', 'jalankan php artisan route:cache', 'warn');

        $this->line('');
        $this->line($this->fails === 0
            ? "<info>Selesai: tidak ada FAIL</info>, {$this->warns} peringatan."
            : "<error> {$this->fails} FAIL </error>, {$this->warns} peringatan.");

        return $this->fails === 0 ? self::SUCCESS : self::FAILURE;
    }

    protected function section(string $title): void
    {
        $this->line('');
        $this->line("<options=bold>{$title}</>");
    }

    protected function check(bool $ok, string $okText, string $failText, string $level = 'fail'): void
    {
        if ($ok) {
            if ($okText !== '') {
                $this->line("  <info>OK</info>    {$okText}");
            }

            return;
        }

        if ($level === 'warn') {
            $this->warns++;
            $this->line("  <comment>WARN</comment>  {$failText}");
        } else {
            $this->fails++;
            $this->line("  <error>FAIL</error>  {$failText}");
        }
    }
}
