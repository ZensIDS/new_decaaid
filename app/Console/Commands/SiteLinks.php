<?php
namespace App\Console\Commands;

use App\Support\Site;
use App\Support\Sitemap;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Throwable;

/**
 * Tahap C/D (v92): pemeriksa halaman dan tautan internal, tanpa jaringan dan tanpa peramban.
 *   php artisan site:links                 semua halaman sitemap (ID + EN) + tautan internal di dalamnya
 *   php artisan site:links --locale=id     hanya satu bahasa
 *   php artisan site:links --no-links      hanya halaman sitemap (tanpa mengikuti tautan)
 *   php artisan site:links --limit=20      contoh cepat: 20 halaman pertama
 * Setiap halaman diminta lewat kernel HTTP Laravel di dalam proses ini. Yang diperiksa:
 *   GAGAL: status bukan 200, tanpa <title>, tanpa <h1>, tautan internal rusak (404/500).
 *   PERINGATAN: judul kembar antar halaman (satu bahasa), tanpa meta description, lebih dari satu <h1>,
 *               <img> tanpa atribut alt, tautan internal yang dialihkan (301/302).
 * Hanya membaca; tidak mengubah data. Kode keluar 1 bila ada GAGAL (bisa dipasang di skrip deploy).
 * Catatan: berkas statis (gambar, CSS, JS, PDF) dan alamat luar dilewati; mode pemeliharaan (503) akan terbaca GAGAL.
 */
class SiteLinks extends Command
{
    protected $signature = 'site:links
                            {--locale= : id atau en (bawaan: semua bahasa)}
                            {--no-links : Jangan ikuti tautan internal di dalam halaman}
                            {--limit=0 : Batasi jumlah halaman sitemap yang diperiksa (0 = semua)}';

    protected $description = 'Periksa semua halaman sitemap dan tautan internalnya: status, title, h1, description, alt gambar, tautan rusak';

    /** @var array<string, array{status:int, body:string, location:?string}> */
    protected array $cache = [];

    protected array $fails = [];

    protected array $warns = [];

    public function handle(): int
    {
        // Jangan penuhi storage/framework/sessions dengan ratusan berkas sesi dari permintaan internal.
        config(['session.driver' => 'array']);

        $locales = config('site.locales');
        if ($only = $this->option('locale')) {
            if (! in_array($only, $locales, true)) {
                $this->error("Bahasa tidak dikenal: {$only} (pilih: " . implode(', ', $locales) . ')');

                return self::FAILURE;
            }
            $locales = [$only];
        }

        $urls = [];
        foreach (Sitemap::pages() as $page) {
            foreach ($locales as $locale) {
                if (Route::has($locale . '.' . $page['key'])) {
                    $urls[] = [$locale, Site::url($page['key'], $page['params'], $locale)];
                }
            }
        }

        if ($limit = (int) $this->option('limit')) {
            $urls = array_slice($urls, 0, $limit);
        }

        $this->line('');
        $this->info('Pemeriksaan halaman dan tautan DECAA.ID (' . count($urls) . ' halaman)');

        $titles = [];   // locale => [title => [url, ...]]
        $links  = [];   // path => [sumber, ...]

        foreach ($urls as [$locale, $url]) {
            $res  = $this->fetch($url);
            $path = $this->pathOf($url);

            if ($res['status'] !== 200) {
                $this->fails[] = "{$path}: status {$res['status']} (seharusnya 200)";
                $this->line("  <fg=red>x</> {$path} [{$res['status']}]");
                continue;
            }

            $doc = $this->parse($res['body']);
            if (! $doc) {
                $this->fails[] = "{$path}: HTML tidak terbaca";
                continue;
            }

            $x       = new DOMXPath($doc);
            $title   = trim((string) $x->evaluate('string(//title)'));
            $descLen = strlen(trim((string) $x->evaluate('string(//meta[@name="description"]/@content)')));
            $h1      = (int) $x->evaluate('count(//h1)');
            $noAlt   = (int) $x->evaluate('count(//img[not(@alt)])');
            $problem = false;

            if ($title === '') {
                $this->fails[] = "{$path}: tanpa <title>";
                $problem = true;
            } else {
                $titles[$locale][$title][] = $path;
            }
            if ($h1 === 0) {
                $this->fails[] = "{$path}: tanpa <h1>";
                $problem = true;
            } elseif ($h1 > 1) {
                $this->warns[] = "{$path}: {$h1} buah <h1> (disarankan satu)";
            }
            if ($descLen === 0) {
                $this->warns[] = "{$path}: tanpa meta description";
            }
            if ($noAlt > 0) {
                $this->warns[] = "{$path}: {$noAlt} gambar tanpa atribut alt";
            }

            $this->line('  ' . ($problem ? '<fg=red>x</>' : '<fg=green>ok</>') . " {$path}");

            if (! $this->option('no-links')) {
                foreach ($x->query('//a[@href]') as $a) {
                    if ($target = $this->internalPath($a->getAttribute('href'))) {
                        $links[$target][$path] = true;
                    }
                }
            }
        }

        foreach ($titles as $locale => $map) {
            foreach ($map as $title => $paths) {
                if (count($paths) > 1) {
                    $this->warns[] = 'Judul kembar (' . strtoupper($locale) . ") \"{$title}\": " . implode(', ', $paths);
                }
            }
        }

        if ($links) {
            $this->section('Tautan internal (' . count($links) . ' alamat unik)');

            foreach ($links as $target => $sources) {
                $res = $this->fetch(rtrim((string) config('app.url'), '/') . $target);
                $src = implode(', ', array_slice(array_keys($sources), 0, 3)) . (count($sources) > 3 ? ', ...' : '');

                if ($res['status'] >= 300 && $res['status'] < 400) {
                    $this->warns[] = "Tautan dialihkan ({$res['status']}) {$target} -> " . ($res['location'] ?? '?') . "  [di: {$src}]";
                } elseif ($res['status'] >= 400) {
                    $this->fails[] = "Tautan rusak ({$res['status']}) {$target}  [di: {$src}]";
                }
            }
        }

        return $this->report();
    }

    protected function report(): int
    {
        $this->line('');

        foreach ($this->warns as $w) {
            $this->line("  <fg=yellow>PERINGATAN</> {$w}");
        }
        foreach ($this->fails as $f) {
            $this->line("  <fg=red>GAGAL</> {$f}");
        }

        $this->line('');
        $this->line(count($this->fails) . ' gagal, ' . count($this->warns) . ' peringatan.');

        if ($this->fails) {
            $this->error('Ada masalah yang perlu diperbaiki.');

            return self::FAILURE;
        }

        $this->info($this->warns ? 'Tidak ada yang gagal; periksa peringatan di atas.' : 'Semua halaman dan tautan lolos.');

        return self::SUCCESS;
    }

    protected function section(string $title): void
    {
        $this->line('');
        $this->line("<options=bold>{$title}</>");
    }

    /** Minta satu alamat lewat kernel HTTP Laravel (di dalam proses ini). Hasil disimpan per alamat. */
    protected function fetch(string $url): array
    {
        if (isset($this->cache[$url])) {
            return $this->cache[$url];
        }

        $kernel  = app(HttpKernel::class);
        $request = Request::create($url, 'GET', [], [], [], ['HTTP_ACCEPT' => 'text/html']);

        try {
            $response = $kernel->handle($request);
            $status   = $response->getStatusCode();
            $content  = $status === 200 ? $response->getContent() : '';
            $result   = [
                'status'   => $status,
                'body'     => is_string($content) ? $content : '',
                'location' => $response->headers->get('Location'),
            ];
            $kernel->terminate($request, $response);
        } catch (Throwable $e) {
            $result = ['status' => 500, 'body' => '', 'location' => null];
        }

        // Badan halaman hanya dipakai sekali (parse di atas); simpan status saja untuk pemeriksaan tautan.
        $this->cache[$url] = ['status' => $result['status'], 'body' => '', 'location' => $result['location']];

        return $result;
    }

    protected function parse(string $html): ?DOMDocument
    {
        if ($html === '') {
            return null;
        }

        $doc  = new DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $ok   = $doc->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        return $ok ? $doc : null;
    }

    protected function pathOf(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '/';

        return $path;
    }

    /** Path internal yang layak diperiksa dari sebuah href, atau null (alamat luar, jangkar, berkas statis, dst.). */
    protected function internalPath(string $href): ?string
    {
        $href = trim($href);

        if ($href === '' || $href[0] === '#' || preg_match('#^(mailto:|tel:|javascript:|data:|sms:|whatsapp:)#i', $href) || str_starts_with($href, '//')) {
            return null;
        }

        $parts = parse_url($href);
        if ($parts === false) {
            return null;
        }

        if (isset($parts['host'])) {
            $own = parse_url((string) config('app.url'), PHP_URL_HOST);
            if (strcasecmp($parts['host'], (string) $own) !== 0) {
                return null;
            }
        }

        $path = $parts['path'] ?? '';
        if ($path === '' || $path[0] !== '/') {
            return null;
        }

        // Berkas statis dilayani server web, bukan Laravel: lewati.
        if (preg_match('#^/(storage|img|wp-uploads|build|vendor|favicon)#i', $path) || preg_match('#\.[a-z0-9]{2,5}$#i', $path)) {
            return null;
        }

        return $path;
    }
}
