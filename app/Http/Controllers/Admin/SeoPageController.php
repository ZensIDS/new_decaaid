<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PageContent;
use App\Support\MediaStore;
use App\Support\PageContentRepo;
use App\Support\SeoMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * B3n bagian 2b: SEO per halaman tetap (satu formulir). Super Admin dan Admin.
 * Judul (maks. 70), deskripsi (maks. 200), dan gambar share per halaman, ditambah gambar share bawaan situs.
 * Yang dikosongkan memakai judul/deskripsi bawaan halaman. Berita, Proyek, dan Halaman Kustom punya SEO sendiri di formulirnya.
 */
class SeoPageController extends Controller
{
    protected const TITLE_MAX = 70;
    protected const DESC_MAX  = 200;

    /** Nama kolom form aman untuk notasi titik: about.profile => about_profile. */
    public static function field(string $key): string
    {
        return str_replace('.', '_', $key);
    }

    public function edit(): View
    {
        $row = PageContentRepo::available() ? PageContentRepo::row(SeoMeta::PAGE) : null;
        $c   = $row && is_array($row->content) ? $row->content : [];

        $pair = fn($v) => ['id' => (string) ($v['id'] ?? ''), 'en' => (string) ($v['en'] ?? '')];

        $pages = [];
        foreach (SeoMeta::PAGES as $key => $label) {
            $p = $c['pages'][$key] ?? [];

            $pages[$key] = [
                'label'       => $label,
                'field'       => self::field($key),
                'title'       => $pair($p['title'] ?? []),
                'description' => $pair($p['description'] ?? []),
                'image'       => (string) ($p['image'] ?? ''),
                'url'         => $this->pageUrl($key),
            ];
        }

        return view('pages.admin.seo.form', [
            'pages'        => $pages,
            'defaultImage' => (string) ($c['default_image'] ?? ''),
            'row'          => $row,
            'available'    => PageContentRepo::available(),
            'titleMax'     => self::TITLE_MAX,
            'descMax'      => self::DESC_MAX,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! PageContentRepo::available()) {
            throw ValidationException::withMessages(['default_image' => 'Tabel belum ada. Jalankan dulu: php artisan migrate']);
        }

        $rules = ['default_image' => ['nullable', 'string', 'max:255']];
        $names = ['default_image' => 'gambar share bawaan'];

        foreach (SeoMeta::PAGES as $key => $label) {
            $f = self::field($key);

            foreach (['id', 'en'] as $l) {
                $rules["pages.$f.title.$l"]       = ['nullable', 'string', 'max:' . self::TITLE_MAX];
                $rules["pages.$f.description.$l"] = ['nullable', 'string', 'max:' . self::DESC_MAX];
                $names["pages.$f.title.$l"]       = 'judul ' . $label . ' (' . strtoupper($l) . ')';
                $names["pages.$f.description.$l"] = 'deskripsi ' . $label . ' (' . strtoupper($l) . ')';
            }

            $rules["pages.$f.image"] = ['nullable', 'string', 'max:255'];
        }

        $d = $request->validate($rules, [], $names);

        $pair    = fn(array $p) => ['id' => trim((string) ($p['id'] ?? '')), 'en' => trim((string) ($p['en'] ?? ''))];
        $isEmpty = fn(array $p) => $p['id'] === '' && $p['en'] === '';

        $pages = [];
        foreach (SeoMeta::PAGES as $key => $label) {
            $in    = $d['pages'][self::field($key)] ?? [];
            $title = $pair($in['title'] ?? []);
            $desc  = $pair($in['description'] ?? []);
            $image = MediaStore::pick($in['image'] ?? null) ?? '';

            // Halaman yang tidak diisi sama sekali tidak disimpan (memakai bawaan).
            if ($isEmpty($title) && $isEmpty($desc) && $image === '') {
                continue;
            }

            $pages[$key] = ['title' => $title, 'description' => $desc, 'image' => $image];
        }

        PageContent::updateOrCreate(
            ['page' => SeoMeta::PAGE],
            ['content' => ['default_image' => MediaStore::pick($d['default_image'] ?? null) ?? '', 'pages' => $pages], 'updated_by' => $request->user()->id]
        );

        ActivityLog::record('seo.updated', 'mengubah SEO Halaman (' . count($pages) . ' halaman diatur)');

        return redirect()->route('admin.seo.edit')->with('status', 'SEO halaman disimpan.');
    }

    /** Kosongkan semua isian: kembali ke judul dan deskripsi bawaan tiap halaman. */
    public function reset(): RedirectResponse
    {
        if (PageContentRepo::available()) {
            PageContent::where('page', SeoMeta::PAGE)->delete();
        }

        ActivityLog::record('seo.reset', 'mengosongkan SEO Halaman');

        return redirect()->route('admin.seo.edit')->with('status', 'SEO halaman dikembalikan ke bawaan.');
    }

    protected function pageUrl(string $key): ?string
    {
        try {
            return lroute($key, [], 'id');
        } catch (\Throwable) {
            return null;
        }
    }
}
