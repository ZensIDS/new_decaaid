<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Tahap C: cadangan database dan media.
 *   php artisan site:backup                 database (.sql.gz) + media (.zip)
 *   php artisan site:backup --db-only       hanya database
 *   php artisan site:backup --media-only    hanya media
 *   php artisan site:backup --keep=14       simpan 14 cadangan terakhir per jenis (bawaan 14)
 * Hasil di storage/app/backups (di luar folder publik). Dijadwalkan harian 03:00 di App\Console\Kernel.
 * Salinan di server yang sama BUKAN pengganti cadangan di luar server: unduh/sinkronkan folder itu secara berkala.
 */
class SiteBackup extends Command
{
    protected $signature = 'site:backup {--db-only} {--media-only} {--keep=14 : jumlah cadangan yang disimpan per jenis}';

    protected $description = 'Cadangkan database (mysqldump, gzip) dan media unggahan (zip) ke storage/app/backups';

    /** Folder media yang dicadangkan (relatif ke base_path). */
    protected const MEDIA = ['storage/app/public', 'public/wp-uploads'];

    public function handle(): int
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir) && ! @mkdir($dir, 0750, true) && ! is_dir($dir)) {
            $this->error("Folder {$dir} tidak bisa dibuat.");

            return self::FAILURE;
        }

        $stamp = now()->format('Ymd-His');
        $ok    = true;

        if (! $this->option('media-only')) {
            $ok = $this->database("{$dir}/db-{$stamp}.sql.gz") && $ok;
        }
        if (! $this->option('db-only')) {
            $ok = $this->media("{$dir}/media-{$stamp}.zip") && $ok;
        }

        $keep = max(1, (int) $this->option('keep'));
        foreach (['db-*.sql.gz', 'media-*.zip'] as $pattern) {
            $files = glob("{$dir}/{$pattern}") ?: [];
            rsort($files); // nama berisi tanggal: terbaru dulu
            foreach (array_slice($files, $keep) as $old) {
                @unlink($old);
                $this->line('Dihapus (melewati batas simpan): ' . basename($old));
            }
        }

        return $ok ? self::SUCCESS : self::FAILURE;
    }

    protected function database(string $target): bool
    {
        $conn = config('database.connections.' . config('database.default'));

        if (($conn['driver'] ?? null) !== 'mysql') {
            $this->warn('Cadangan database hanya mendukung MySQL/MariaDB; dilewati.');

            return true;
        }

        $bin = (new ExecutableFinder())->find('mysqldump') ?: (new ExecutableFinder())->find('mariadb-dump');
        if (! $bin) {
            $this->error('mysqldump tidak ditemukan di server. Pasang klien MySQL (mis. paket mysql-client) lalu ulangi.');

            return false;
        }

        $cmd = [$bin, '--single-transaction', '--quick', '--routines', '--no-tablespaces',
            '-h', (string) $conn['host'], '-P', (string) $conn['port'], '-u', (string) $conn['username'], (string) $conn['database']];

        // Kata sandi lewat variabel lingkungan, bukan argumen (tidak terlihat di daftar proses).
        $process = new Process($cmd, null, ['MYSQL_PWD' => (string) $conn['password']], null, 600);

        $gz = gzopen($target, 'wb6');
        if (! $gz) {
            $this->error('Berkas cadangan database tidak bisa dibuat.');

            return false;
        }

        $process->run(function ($type, $buffer) use ($gz) {
            if ($type === Process::OUT) {
                gzwrite($gz, $buffer);
            }
        });
        gzclose($gz);

        if (! $process->isSuccessful()) {
            @unlink($target);
            $this->error('mysqldump gagal: ' . trim($process->getErrorOutput()));

            return false;
        }

        $this->info('Database: ' . basename($target) . ' (' . $this->size($target) . ')');

        return true;
    }

    protected function media(string $target): bool
    {
        if (! class_exists(ZipArchive::class)) {
            $this->error('Ekstensi PHP zip belum aktif; cadangan media dilewati.');

            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error('Berkas cadangan media tidak bisa dibuat.');

            return false;
        }

        $count = 0;
        foreach (self::MEDIA as $rel) {
            $root = base_path($rel);
            if (! is_dir($root)) {
                continue;
            }
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
            foreach ($it as $file) {
                if ($file->isFile() && ! $file->isLink()) {
                    $zip->addFile($file->getPathname(), $rel . '/' . ltrim(substr($file->getPathname(), strlen($root)), '/\\'));
                    $count++;
                }
            }
        }

        if ($count === 0) {
            $zip->close();
            @unlink($target);
            $this->line('Media: tidak ada berkas, dilewati.');

            return true;
        }

        $zip->close();
        $this->info("Media: " . basename($target) . " ({$count} berkas, " . $this->size($target) . ')');

        return true;
    }

    protected function size(string $path): string
    {
        $b = filesize($path) ?: 0;

        return $b >= 1048576 ? round($b / 1048576, 1) . ' MB' : round($b / 1024) . ' KB';
    }
}
