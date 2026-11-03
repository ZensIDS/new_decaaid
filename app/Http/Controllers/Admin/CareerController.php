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
 * B3k: halaman Karir (satu halaman, satu formulir). Super Admin dan Admin.
 * Daftar bidang yang diminati TIDAK diedit di sini: diambil otomatis dari brand di 8 pilar (Pilar & Brand).
 * Alamat tujuan CV boleh dikosongkan; bila kosong dipakai alamat karir bawaan situs.
 */
class CareerController extends Controller
{
    protected const PAGE = 'careers';

    /** Isian pasangan ID/EN: kunci => [label, maks. karakter, textarea?] */
    public const PAIRS = [
        'title'         => ['Judul halaman', 120, false],
        'lead'          => ['Kalimat pengantar', 300, true],
        'open_note'     => ['Catatan lamaran terbuka', 120, false],
        'why_title'     => ['Judul "Mengapa bergabung"', 120, false],
        'why_text'      => ['Teks "Mengapa bergabung"', 400, true],
        'process_title' => ['Judul "Proses seleksi"', 120, false],
        'process_text'  => ['Teks "Proses seleksi"', 300, true],
        'cv_title'      => ['Judul "Kirim CV"', 120, false],
        'cv_text'       => ['Teks "Kirim CV"', 400, true],
    ];

    /** Repeater: kunci => maksimal butir */
    public const LISTS = ['why' => 6, 'process' => 6];

    public function edit(): View
    {
        $row  = PageContentRepo::available() ? PageContentRepo::row(self::PAGE) : null;
        $seed = Content::careersRaw();
        $data = [];

        foreach (Content::CAREERS_EDITABLE as $key) {
            $data[$key] = $row && ! empty($row->content[$key]) ? $row->content[$key] : ($seed[$key] ?? '');
        }

        return view('pages.admin.careers.form', [
            'data'      => $data,
            'row'       => $row,
            'available' => PageContentRepo::available(),
            'pairs'     => self::PAIRS,
            'lists'     => self::LISTS,
            'defaultEmail' => Content::company()['email']['career'] ?? '',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! PageContentRepo::available()) {
            throw ValidationException::withMessages(['title.id' => 'Tabel belum ada. Jalankan dulu: php artisan migrate']);
        }

        $merge = [];
        foreach (array_keys(self::LISTS) as $list) {
            $merge[$list] = $this->nonEmpty($request->input($list, []));
        }
        $request->merge($merge);

        $rules = ['cv_email' => ['nullable', 'email:rfc', 'max:120']];
        $names = ['cv_email' => 'email tujuan CV'];

        foreach (self::PAIRS as $key => [$label, $max]) {
            $rules["$key.id"] = ['required', 'string', "max:$max"];
            $rules["$key.en"] = ['nullable', 'string', "max:$max"];
            $names["$key.id"] = strtolower($label) . ' (ID)';
        }

        foreach (self::LISTS as $list => $max) {
            $rules[$list]             = ['required', 'array', 'min:1', "max:$max"];
            $rules["$list.*.title.id"] = ['required', 'string', 'max:100'];
            $rules["$list.*.title.en"] = ['nullable', 'string', 'max:100'];
            $rules["$list.*.text.id"]  = ['required', 'string', 'max:300'];
            $rules["$list.*.text.en"]  = ['nullable', 'string', 'max:300'];
            $names["$list.*.title.id"] = 'judul butir (ID)';
            $names["$list.*.text.id"]  = 'teks butir (ID)';
        }

        $d = $request->validate($rules, [
            'why.required'     => 'Isi minimal satu butir "Mengapa bergabung".',
            'process.required' => 'Isi minimal satu langkah seleksi.',
            'cv_email.email'   => 'Alamat email tidak valid.',
        ], $names);

        $pair = fn(array $p) => ['id' => trim((string) ($p['id'] ?? '')), 'en' => trim((string) ($p['en'] ?? ''))];
        $item = fn(array $r) => ['title' => $pair($r['title'] ?? []), 'text' => $pair($r['text'] ?? [])];

        $content = ['cv_email' => trim((string) ($d['cv_email'] ?? ''))];

        foreach (array_keys(self::PAIRS) as $key) {
            $content[$key] = $pair($d[$key]);
        }
        foreach (array_keys(self::LISTS) as $list) {
            $content[$list] = array_values(array_map($item, $d[$list]));
        }

        PageContent::updateOrCreate(
            ['page' => self::PAGE],
            ['content' => $content, 'updated_by' => $request->user()->id]
        );

        ActivityLog::record('page.updated', 'mengubah isi halaman Karir');

        return redirect()->route('admin.careers.edit')->with('status', 'Halaman Karir disimpan.');
    }

    /** Kembali ke teks contoh bawaan kode. */
    public function reset(): RedirectResponse
    {
        if (PageContentRepo::available()) {
            PageContent::where('page', self::PAGE)->delete();
        }

        ActivityLog::record('page.reset', 'mengembalikan halaman Karir ke teks contoh');

        return redirect()->route('admin.careers.edit')->with('status', 'Halaman Karir dikembalikan ke teks contoh.');
    }

    /** Baris repeater yang judul dan teksnya (ID dan EN) kosong semua dibuang. */
    protected function nonEmpty(mixed $rows): array
    {
        return array_values(array_filter((array) $rows, function ($row) {
            foreach (['title.id', 'text.id', 'title.en', 'text.en'] as $path) {
                if (trim((string) data_get($row, $path, '')) !== '') {
                    return true;
                }
            }

            return false;
        }));
    }
}
