<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PageContent;
use App\Support\Content;
use App\Support\PageContentRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * B3e: Beranda (satu halaman, satu formulir). Super Admin dan Admin.
 * Bisa diedit: teks tiap section (ID/EN), 4+ nilai perusahaan, poin Keberlanjutan, serta tampil/sembunyi dan urutan
 * section (QA1). Hero (paling atas) dan ajakan Hubungi Kami (paling bawah) selalu tampil di posisinya.
 * Tetap di kode: foto hero dan tentang (menunggu Media Library B3b), label tombol, proyek unggulan (diatur lewat
 * centang "unggulan" di modul Proyek), kartu brand, logo klien (modul Klien & Mitra), carousel (modul Penghargaan),
 * berita (modul Berita), nama dan ringkasan komunitas (modul Keberlanjutan).
 */
class HomePageController extends Controller
{
    protected const PAGE = 'home';

    /** Kelompok isian: kunci => [judul kartu, ikon, keterangan, daftar field] ; field = [label, maks. karakter, textarea?, wajib?] */
    public const GROUPS = [
        'hero' => ['Hero (Paling Atas)', 'home', 'Bagian pembuka. Selalu tampil di paling atas. Judul hero terpisah dari slogan di Pengaturan Situs.', [
            'eyebrow' => ['Label kecil di atas judul', 60, false, false],
            'title'   => ['Judul', 120, false, true],
            'text'    => ['Teks', 300, true, true],
        ]],
        'about' => ['Tentang DECAA.ID', 'book', 'Ringkasan perusahaan beserta dua foto di sebelahnya.', [
            'eyebrow' => ['Label kecil', 60, false, true],
            'title'   => ['Judul', 160, false, true],
            'text'    => ['Teks', 900, true, true],
        ]],
        'units' => ['Pilar Bisnis', 'cube', 'Judul di atas kartu brand (juga dipakai di Profil Perusahaan). Kartu brand sendiri otomatis dari Pilar & Brand.', [
            'eyebrow' => ['Label kecil', 60, false, true],
            'title'   => ['Judul', 160, false, true],
            'text'    => ['Teks', 300, true, true],
        ]],
        'projects' => ['Proyek Unggulan', 'briefcase', 'Judul di atas tiga proyek. Proyek yang tampil dipilih lewat centang "unggulan" di modul Proyek.', [
            'eyebrow' => ['Label kecil', 60, false, true],
            'title'   => ['Judul', 160, false, true],
            'text'    => ['Teks', 300, true, true],
        ]],
        'clients' => ['Klien & Mitra', 'users', 'Judul di samping strip logo. Logo diatur di modul Klien & Mitra (centang "tampil di beranda").', [
            'eyebrow' => ['Label kecil', 60, false, true],
            'title'   => ['Judul', 160, false, true],
        ]],
        'values' => ['Mengapa DECAA.ID', 'check', 'Judul dan butir nilai perusahaan (1 sampai 8, tampil bernomor di bagian biru tua).', [
            'eyebrow' => ['Label kecil', 60, false, true],
            'title'   => ['Judul', 160, false, true],
        ]],
        'news' => ['Berita Terbaru', 'newspaper', 'Judul di atas tiga berita terbaru (berita diambil otomatis dari modul Berita).', [
            'eyebrow' => ['Label kecil', 60, false, true],
            'title'   => ['Judul', 160, false, true],
            'text'    => ['Teks', 300, true, true],
        ]],
        'cta' => ['Ajakan Hubungi Kami (Paling Bawah)', 'mail', 'Bagian biru di paling bawah. Selalu tampil, dan teks ini juga dipakai di bagian ajakan pada halaman dalam (Tentang Kami, Portofolio, Berita, dll.).', [
            'title' => ['Judul', 160, false, true],
            'text'  => ['Teks', 300, true, true],
        ]],
    ];

    public const MAX_VALUES = 8;
    public const MAX_POINTS = 6;

    public function edit(): View
    {
        $row  = PageContentRepo::available() ? PageContentRepo::row(self::PAGE) : null;
        $seed = Content::homeRaw();
        $has  = $row && is_array($row->content) ? $row->content : [];
        $data = [];

        foreach (Content::HOME_TEXTS as $section => $fields) {
            foreach ($fields as $field) {
                $data[$section][$field] = $has[$section][$field] ?? $seed[$section][$field] ?? ['id' => '', 'en' => ''];
            }
        }

        $data['values']['items']     = ! empty($has['values']['items']) ? $has['values']['items'] : $seed['values']['items'];
        $data['community']['points'] = ! empty($has['community']['points']) ? $has['community']['points'] : $seed['community']['points'];

        return view('pages.admin.home.form', [
            'data'      => $data,
            'sections'  => $this->sectionRows($has['sections'] ?? null),
            'row'       => $row,
            'available' => PageContentRepo::available(),
            'groups'    => self::GROUPS,
            'maxValues' => self::MAX_VALUES,
            'maxPoints' => self::MAX_POINTS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! PageContentRepo::available()) {
            throw ValidationException::withMessages(['hero.title.id' => 'Tabel belum ada. Jalankan dulu: php artisan migrate']);
        }

        $request->merge([
            'values'    => array_merge((array) $request->input('values', []), ['items' => $this->nonEmpty($request->input('values.items', []), ['title.id', 'text.id', 'title.en', 'text.en'])]),
            'community' => ['points' => $this->nonEmpty($request->input('community.points', []), ['id', 'en'])],
        ]);

        $known = array_keys(Content::HOME_SECTIONS);
        $rules = [
            'sections'           => ['required', 'array', 'size:'.count($known)],
            'sections.*.key'     => ['required', 'in:'.implode(',', $known), 'distinct'],
            'sections.*.visible' => ['required', 'in:0,1'],
        ];
        $names = [];

        foreach (self::GROUPS as $section => [, , , $fields]) {
            foreach ($fields as $field => [$label, $max, , $required]) {
                $rules["$section.$field.id"] = [$required ? 'required' : 'nullable', 'string', "max:$max"];
                $rules["$section.$field.en"] = ['nullable', 'string', "max:$max"];
                $names["$section.$field.id"] = strtolower($label).' (ID)';
            }
        }

        $rules['values.items']            = ['required', 'array', 'min:1', 'max:'.self::MAX_VALUES];
        $rules['values.items.*.title.id'] = ['required', 'string', 'max:100'];
        $rules['values.items.*.title.en'] = ['nullable', 'string', 'max:100'];
        $rules['values.items.*.text.id']  = ['required', 'string', 'max:300'];
        $rules['values.items.*.text.en']  = ['nullable', 'string', 'max:300'];
        $rules['community.points']        = ['required', 'array', 'min:1', 'max:'.self::MAX_POINTS];
        $rules['community.points.*.id']   = ['required', 'string', 'max:120'];
        $rules['community.points.*.en']   = ['nullable', 'string', 'max:120'];

        $names['values.items.*.title.id'] = 'judul nilai (ID)';
        $names['values.items.*.text.id']  = 'teks nilai (ID)';
        $names['community.points.*.id']   = 'poin Keberlanjutan (ID)';

        $d = $request->validate($rules, [
            'values.items.required'      => 'Isi minimal satu butir nilai perusahaan.',
            'community.points.required'  => 'Isi minimal satu poin Keberlanjutan.',
            'sections.size'              => 'Daftar section tidak lengkap. Muat ulang halaman lalu coba lagi.',
            'sections.*.key.distinct'    => 'Daftar section berisi kunci ganda. Muat ulang halaman lalu coba lagi.',
        ], $names);

        $pair = fn(mixed $p) => ['id' => trim((string) ($p['id'] ?? '')), 'en' => trim((string) ($p['en'] ?? ''))];

        $content = [
            'sections' => array_values(array_map(
                fn($r) => ['key' => $r['key'], 'visible' => $r['visible'] === '1'],
                $d['sections']
            )),
        ];

        foreach (self::GROUPS as $section => [, , , $fields]) {
            foreach (array_keys($fields) as $field) {
                $content[$section][$field] = $pair($d[$section][$field] ?? []);
            }
        }

        $content['values']['items'] = array_values(array_map(
            fn($r) => ['title' => $pair($r['title'] ?? []), 'text' => $pair($r['text'] ?? [])],
            $d['values']['items']
        ));
        $content['community']['points'] = array_values(array_map($pair, $d['community']['points']));

        PageContent::updateOrCreate(
            ['page' => self::PAGE],
            ['content' => $content, 'updated_by' => $request->user()->id]
        );

        ActivityLog::record('page.updated', 'mengubah isi halaman Beranda');

        return redirect()->route('admin.home.edit')->with('status', 'Beranda disimpan.');
    }

    /** Kembali ke teks contoh dan urutan bawaan kode. */
    public function reset(): RedirectResponse
    {
        if (PageContentRepo::available()) {
            PageContent::where('page', self::PAGE)->delete();
        }

        ActivityLog::record('page.reset', 'mengembalikan halaman Beranda ke teks contoh');

        return redirect()->route('admin.home.edit')->with('status', 'Beranda dikembalikan ke teks contoh.');
    }

    /**
     * Baris section untuk form: urutan tersimpan lebih dulu (kunci tak dikenal dibuang), kunci yang belum tercatat
     * ditambahkan di akhir dan tampil.
     *
     * @return array<int, array{key: string, label: string, visible: bool}>
     */
    protected function sectionRows(mixed $stored): array
    {
        $rows = [];
        $seen = [];

        foreach ((array) $stored as $r) {
            $key = is_array($r) ? ($r['key'] ?? null) : null;

            if (! isset(Content::HOME_SECTIONS[$key]) || isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $rows[]     = ['key' => $key, 'label' => Content::HOME_SECTIONS[$key], 'visible' => ! empty($r['visible'])];
        }

        foreach (Content::HOME_SECTIONS as $key => $label) {
            if (! isset($seen[$key])) {
                $rows[] = ['key' => $key, 'label' => $label, 'visible' => true];
            }
        }

        return $rows;
    }

    /** Baris repeater yang semua isiannya (ID dan EN) kosong dibuang. */
    protected function nonEmpty(mixed $rows, array $paths): array
    {
        return array_values(array_filter((array) $rows, function ($row) use ($paths) {
            foreach ($paths as $path) {
                if (trim((string) data_get($row, $path, '')) !== '') {
                    return true;
                }
            }

            return false;
        }));
    }
}
