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
 * B3m: Kebijakan Privasi (halaman Legal). Satu formulir: kalimat pengantar, tanggal pembaruan, dan bagian-bagian
 * (judul + teks biasa; baris baru = paragraf baru; daftar isi dibuat otomatis dari judul bagian). Super Admin dan Admin.
 * Halaman kustom tambahan (QA1) belum ada; menyusul sebagai B3m berikutnya.
 */
class PrivacyController extends Controller
{
    protected const PAGE = 'privacy';

    public const MAX_SECTIONS = 15;

    public function edit(): View
    {
        $row  = PageContentRepo::available() ? PageContentRepo::row(self::PAGE) : null;
        $seed = Content::privacyRaw();
        $data = [];

        foreach (Content::PRIVACY_EDITABLE as $key) {
            $data[$key] = $row && ! empty($row->content[$key]) ? $row->content[$key] : $seed[$key];
        }

        return view('pages.admin.privacy.form', [
            'data'      => $data,
            'row'       => $row,
            'available' => PageContentRepo::available(),
            'max'       => self::MAX_SECTIONS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! PageContentRepo::available()) {
            throw ValidationException::withMessages(['lead.id' => 'Tabel belum ada. Jalankan dulu: php artisan migrate']);
        }

        $request->merge(['sections' => array_values(array_filter((array) $request->input('sections', []), function ($row) {
            foreach (['title.id', 'text.id', 'title.en', 'text.en'] as $path) {
                if (trim((string) data_get($row, $path, '')) !== '') {
                    return true;
                }
            }

            return false;
        }))]);

        $d = $request->validate([
            'lead.id'             => ['required', 'string', 'max:400'],
            'lead.en'             => ['nullable', 'string', 'max:400'],
            'updated'             => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'sections'            => ['required', 'array', 'min:1', 'max:' . self::MAX_SECTIONS],
            'sections.*.title.id' => ['required', 'string', 'max:100'],
            'sections.*.title.en' => ['nullable', 'string', 'max:100'],
            'sections.*.text.id'  => ['required', 'string', 'max:4000'],
            'sections.*.text.en'  => ['nullable', 'string', 'max:4000'],
        ], [
            'sections.required'   => 'Isi minimal satu bagian.',
            'updated.before_or_equal' => 'Tanggal pembaruan tidak boleh di masa depan.',
            'updated.date_format' => 'Tanggal pembaruan tidak valid.',
        ], [
            'lead.id' => 'kalimat pengantar (ID)', 'updated' => 'tanggal pembaruan',
            'sections.*.title.id' => 'judul bagian (ID)', 'sections.*.text.id' => 'teks bagian (ID)',
        ]);

        $pair = fn(array $p) => ['id' => trim((string) ($p['id'] ?? '')), 'en' => trim((string) ($p['en'] ?? ''))];

        PageContent::updateOrCreate(['page' => self::PAGE], [
            'content' => [
                'lead'     => $pair($d['lead']),
                'updated'  => $d['updated'],
                'sections' => array_values(array_map(
                    fn(array $r) => ['title' => $pair($r['title'] ?? []), 'text' => $pair($r['text'] ?? [])],
                    $d['sections']
                )),
            ],
            'updated_by' => $request->user()->id,
        ]);

        ActivityLog::record('page.updated', 'mengubah isi halaman Kebijakan Privasi');

        return redirect()->route('admin.pages.privacy.edit')->with('status', 'Kebijakan Privasi disimpan.');
    }

    /** Kembali ke draf bawaan kode. */
    public function reset(): RedirectResponse
    {
        if (PageContentRepo::available()) {
            PageContent::where('page', self::PAGE)->delete();
        }

        ActivityLog::record('page.reset', 'mengembalikan halaman Kebijakan Privasi ke draf contoh');

        return redirect()->route('admin.pages.privacy.edit')->with('status', 'Kebijakan Privasi dikembalikan ke draf contoh.');
    }
}
