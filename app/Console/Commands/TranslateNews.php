<?php
namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\News;
use App\Support\Html;
use App\Support\NewsAutoTranslate;
use App\Support\Translator;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

/**
 * B5: terjemahan massal berita yang bahasanya masih kosong (mis. 171 berita hasil impor WordPress yang hanya berbahasa Indonesia).
 *   php artisan translate:news --dry-run             daftar calon + perkiraan jumlah karakter, tanpa memanggil penyedia
 *   php artisan translate:news --limit=20            terjemahkan 20 berita terbaru yang membutuhkan (bawaan 20; 0 = semua)
 *   php artisan translate:news --id=12 --id=15       hanya berita tertentu
 *   php artisan translate:news --status=all          sertakan Draf (bawaan: Terbit dan Terjadwal)
 *   php artisan translate:news --include-reviewed    sertakan berita berstatus "Ditinjau" (bawaan: dilewati)
 * Aturan sama dengan form Berita (App\Support\NewsAutoTranslate): hanya kolom KOSONG yang diisi, tulisan admin tidak pernah ditimpa;
 * berita yang diterjemahkan ditandai "Otomatis (belum ditinjau)". Berjalan serempak (tanpa queue) dalam kelompok kecil agar biaya
 * dan batas laju penyedia terkendali; ulangi perintah sampai daftar calon habis. Berhenti sendiri bila galat beruntun (mis. kuota habis).
 */
class TranslateNews extends Command
{
    protected $signature = 'translate:news
        {--limit=20 : jumlah berita per jalan (0 = semua)}
        {--id=* : hanya berita dengan id ini}
        {--status=published : published (Terbit dan Terjadwal) atau all (termasuk Draf)}
        {--include-reviewed : sertakan berita berstatus Ditinjau}
        {--sleep=1 : jeda detik antarberita}
        {--max-errors=3 : berhenti setelah sekian galat beruntun}
        {--dry-run : hanya tampilkan calon dan perkiraan}';

    protected $description = 'Terjemahkan otomatis bahasa yang kosong pada berita (massal, dalam kelompok kecil)';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        if (! $dry && ! Translator::enabled()) {
            $this->error('Penyedia terjemahan belum diatur. Isi TRANSLATE_DRIVER dan kuncinya di .env, lalu php artisan config:clear.');

            return self::FAILURE;
        }

        $query = News::query()->orderByDesc('published_at')->orderByDesc('id');

        if ($this->option('status') !== 'all') {
            $query->whereIn('status', [News::STATUS_PUBLISHED, News::STATUS_SCHEDULED]);
        }

        if ($ids = array_filter((array) $this->option('id'))) {
            $query->whereIn('id', array_map('intval', $ids));
        }

        if (! $this->option('include-reviewed')) {
            $query->where(fn($q) => $q->whereNull('translation_status')->orWhere('translation_status', '!=', 'reviewed'));
        }

        $candidates = $query->get()->filter(fn(News $n) => $this->needs($n))->values();
        $total      = $candidates->count();
        $limit      = (int) $this->option('limit');
        $batch      = $limit > 0 ? $candidates->take($limit) : $candidates;

        $this->info("Calon terjemahan: {$total} berita".($limit > 0 && $total > $limit ? " (jalan ini: {$batch->count()})" : '').'.');

        if ($dry) {
            $chars = 0;
            foreach ($batch as $n) {
                $size   = $this->sourceChars($n);
                $chars += $size;
                $this->line(sprintf('  #%d  %s  (%s karakter)  %s', $n->id, optional($n->published_at)->format('Y-m-d') ?? '----------', number_format($size), mb_strimwidth((string) ($n->title['id'] ?? $n->title['en'] ?? ''), 0, 60, '...')));
            }
            $this->line('Perkiraan: '.number_format($chars).' karakter sumber untuk jalan ini. Tidak ada yang dikirim ke penyedia.');

            return self::SUCCESS;
        }

        if ($batch->isEmpty()) {
            $this->info('Tidak ada yang perlu diterjemahkan.');

            return self::SUCCESS;
        }

        $ok = $failed = $streak = 0;
        $max = max(1, (int) $this->option('max-errors'));

        foreach ($batch as $i => $news) {
            try {
                $this->translate($news);
                $ok++;
                $streak = 0;
                $this->line("  OK    #{$news->id} ".mb_strimwidth((string) ($news->title['id'] ?? ''), 0, 60, '...'));
            } catch (Throwable $e) {
                $failed++;
                $streak++;
                $this->line("  GAGAL #{$news->id}: ".$e->getMessage());

                if ($streak >= $max) {
                    $this->error("Berhenti: {$streak} galat beruntun (cek kuota/kunci penyedia). Ulangi perintah setelah diperbaiki; berita yang sudah selesai tidak diproses lagi.");
                    break;
                }
            }

            if ($i < $batch->count() - 1 && ($sleep = (float) $this->option('sleep')) > 0) {
                usleep((int) ($sleep * 1_000_000));
            }
        }

        if ($ok > 0) {
            ActivityLog::record('news.translated_batch', "menerjemahkan {$ok} berita secara massal (perintah translate:news)");
        }

        $sisa = max(0, $total - $ok);
        $this->line('');
        $this->line("Selesai: {$ok} berhasil, {$failed} gagal, sisa calon {$sisa}.");
        $this->line('Berita hasil mesin berstatus "Otomatis (belum ditinjau)"; tinjau di admin Berita lalu ubah ke "Ditinjau".');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** Ada kolom yang terisi di satu bahasa tetapi kosong di bahasa lain. */
    protected function needs(News $n): bool
    {
        foreach (News::LOCALIZED as $field) {
            $id = $this->has($n, $field, 'id');
            $en = $this->has($n, $field, 'en');

            if ($id xor $en) {
                return true;
            }
        }

        return false;
    }

    protected function has(News $n, string $field, string $locale): bool
    {
        $value = (string) (($n->{$field}[$locale] ?? ''));

        return trim($field === 'body' ? strip_tags($value, '<img>') : $value) !== '';
    }

    protected function sourceChars(News $n): int
    {
        $sum = 0;
        foreach (News::LOCALIZED as $field) {
            foreach (['id', 'en'] as $l) {
                $o = $l === 'id' ? 'en' : 'id';
                if ($this->has($n, $field, $l) && ! $this->has($n, $field, $o)) {
                    $sum += mb_strlen((string) $n->{$field}[$l]);
                }
            }
        }

        return $sum;
    }

    /** @throws RuntimeException bila penyedia gagal (berita tidak diubah) */
    protected function translate(News $n): void
    {
        $input = [];
        foreach (News::LOCALIZED as $field) {
            $input[$field] = ['id' => $n->{$field}['id'] ?? '', 'en' => $n->{$field}['en'] ?? ''];
        }

        [$filled, $direction, $warning] = NewsAutoTranslate::fill($input, $n);

        if ($direction === null) {
            throw new RuntimeException($warning ?: 'Tidak ada yang diterjemahkan.');
        }

        $update = [];
        foreach (News::LOCALIZED as $field) {
            $pair = ['id' => trim((string) ($filled[$field]['id'] ?? '')), 'en' => trim((string) ($filled[$field]['en'] ?? ''))];

            if ($field === 'body') {
                $pair = ['id' => Html::clean($pair['id']), 'en' => Html::clean($pair['en'])];
            }

            $update[$field] = $pair;
        }

        // Sama seperti form Berita: terjemahan terjadi, tandai belum ditinjau (kolom ada sejak migration 2026_11_02_203714).
        $update['translation_status'] = 'auto';
        $update['translated_from']    = $direction;

        $n->forceFill($update)->save();

        if ($warning) {
            $this->line("        peringatan: {$warning}");
        }
    }
}
