<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PageContent;
use App\Support\Content;
use App\Support\MediaStore;
use App\Support\PageContentRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * B3j: halaman Keberlanjutan (Komunitas Peduli Digital). Satu halaman, satu formulir (tanpa daftar).
 * Super Admin dan Admin. Isi form awal = isi tersimpan, atau teks contoh kode bila belum pernah disimpan.
 */
class SustainabilityController extends Controller
{
    protected const PAGE = 'sustainability';

    /** Foto halaman (v102): disimpan di content['images']['photo']; kosong = foto Beranda atau berkas bawaan (Content::communityImage). */
    public const IMAGE_SLOT = 'photo';

    public function edit(): View
    {
        $row  = PageContentRepo::available() ? PageContentRepo::row(self::PAGE) : null;
        $seed = Content::communityRaw();
        $data = [];

        foreach (Content::SUSTAINABILITY_EDITABLE as $key) {
            $data[$key] = $row && ! empty($row->content[$key]) ? $row->content[$key] : $seed[$key];
        }

        return view('pages.admin.sustainability.form', [
            'data'      => $data,
            'photo'     => (string) ($row?->content['images'][self::IMAGE_SLOT] ?? ''),
            'row'       => $row,
            'available' => PageContentRepo::available(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! PageContentRepo::available()) {
            throw ValidationException::withMessages(['about_title.id' => 'Tabel belum ada. Jalankan dulu: php artisan migrate']);
        }

        // Buang baris repeater yang kosong sebelum divalidasi.
        $request->merge([
            'about'    => $this->nonEmpty($request->input('about', []), ['id']),
            'pillars'  => $this->nonEmpty($request->input('pillars', []), ['title.id', 'text.id', 'title.en', 'text.en']),
            'programs' => $this->nonEmpty($request->input('programs', []), ['title.id', 'text.id', 'title.en', 'text.en']),
        ]);

        $d = $request->validate([
            'about_title.id'    => ['required', 'string', 'max:120'],
            'about_title.en'    => ['nullable', 'string', 'max:120'],
            'about'             => ['required', 'array', 'min:1', 'max:2'],
            'about.*.id'        => ['required', 'string', 'max:700'],
            'about.*.en'        => ['nullable', 'string', 'max:700'],
            'pillars_title.id'  => ['required', 'string', 'max:120'],
            'pillars_title.en'  => ['nullable', 'string', 'max:120'],
            'pillars'           => ['required', 'array', 'min:1', 'max:6'],
            'pillars.*.title.id' => ['required', 'string', 'max:100'],
            'pillars.*.title.en' => ['nullable', 'string', 'max:100'],
            'pillars.*.text.id'  => ['required', 'string', 'max:300'],
            'pillars.*.text.en'  => ['nullable', 'string', 'max:300'],
            'programs_title.id' => ['required', 'string', 'max:120'],
            'programs_title.en' => ['nullable', 'string', 'max:120'],
            'programs'          => ['required', 'array', 'min:1', 'max:8'],
            'programs.*.title.id' => ['required', 'string', 'max:100'],
            'programs.*.title.en' => ['nullable', 'string', 'max:100'],
            'programs.*.text.id'  => ['required', 'string', 'max:300'],
            'programs.*.text.en'  => ['nullable', 'string', 'max:300'],
            'join_title.id'     => ['required', 'string', 'max:120'],
            'join_title.en'     => ['nullable', 'string', 'max:120'],
            'join_text.id'      => ['required', 'string', 'max:400'],
            'join_text.en'      => ['nullable', 'string', 'max:400'],
            'images.photo'      => ['nullable', 'string', 'max:255'],
        ], [
            'about.required'    => 'Isi minimal satu paragraf "Tentang".',
            'pillars.required'  => 'Isi minimal satu butir "Fokus".',
            'programs.required' => 'Isi minimal satu program.',
        ], [
            'about_title.id' => 'judul Tentang (ID)', 'pillars_title.id' => 'judul Fokus (ID)', 'programs_title.id' => 'judul Program (ID)',
            'join_title.id' => 'judul ajakan (ID)', 'join_text.id' => 'teks ajakan (ID)',
            'about.*.id' => 'paragraf (ID)', 'pillars.*.title.id' => 'judul fokus (ID)', 'pillars.*.text.id' => 'teks fokus (ID)',
            'programs.*.title.id' => 'judul program (ID)', 'programs.*.text.id' => 'teks program (ID)',
        ]);

        $pair = fn(array $p) => ['id' => trim((string) ($p['id'] ?? '')), 'en' => trim((string) ($p['en'] ?? ''))];
        $item = fn(array $r) => ['title' => $pair($r['title'] ?? []), 'text' => $pair($r['text'] ?? [])];

        $content = [
            'about_title'    => $pair($d['about_title']),
            'about'          => array_values(array_map($pair, $d['about'])),
            'pillars_title'  => $pair($d['pillars_title']),
            'pillars'        => array_values(array_map($item, $d['pillars'])),
            'programs_title' => $pair($d['programs_title']),
            'programs'       => array_values(array_map($item, $d['programs'])),
            'join_title'     => $pair($d['join_title']),
            'join_text'      => $pair($d['join_text']),
        ];

        // Foto dari Media Library: hanya path sah yang berkasnya ada; kosong = foto bawaan.
        if ($picked = MediaStore::pick($d['images']['photo'] ?? null)) {
            $content['images'][self::IMAGE_SLOT] = $picked;
        }

        PageContent::updateOrCreate(
            ['page' => self::PAGE],
            ['content' => $content, 'updated_by' => $request->user()->id]
        );

        ActivityLog::record('page.updated', 'mengubah isi halaman Keberlanjutan');

        return redirect()->route('admin.sustainability.edit')->with('status', 'Halaman Keberlanjutan disimpan.');
    }

    /** Kembali ke teks contoh bawaan kode (menghapus isi tersimpan). */
    public function reset(): RedirectResponse
    {
        if (PageContentRepo::available()) {
            PageContent::where('page', self::PAGE)->delete();
        }

        ActivityLog::record('page.reset', 'mengembalikan halaman Keberlanjutan ke teks contoh');

        return redirect()->route('admin.sustainability.edit')->with('status', 'Halaman Keberlanjutan dikembalikan ke teks contoh.');
    }

    /** Baris repeater yang semua isiannya (path titik) kosong dibuang. */
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
