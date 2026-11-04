<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Media;
use App\Support\ImageStore;
use App\Support\MediaStore;
use App\Support\MediaUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * B3b: Media Library. Perpustakaan lengkap (cari, filter, ganti nama, alt, lihat pemakaian, hapus dengan penjaga)
 * untuk Super Admin dan Admin; endpoint pemilih (picker) untuk semua peran admin dipakai formulir lain.
 * Gambar maks. 5 MB, dikecilkan dan diubah ke WebP oleh ImageStore (QA7).
 */
class MediaController extends Controller
{
    protected const PER_PAGE = 36;
    protected const BULK_MAX = 20;

    public function index(Request $request): View
    {
        if (! Schema::hasTable('media')) {
            return view('pages.admin.media.index', ['notReady' => true]);
        }

        $q      = trim((string) $request->query('q'));
        $source = $request->query('sumber');   // upload | bundled
        $folder = $request->query('folder');
        $alt    = $request->query('alt');      // kosong

        $items = Media::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $query->where(fn($w) => $w->where('name', 'like', $like)->orWhere('path', 'like', $like));
            })
            ->when(in_array($source, ['upload', 'bundled'], true), fn($query) => $query->where('source', $source))
            ->when($folder, fn($query) => $query->where('folder', $folder))
            ->when($alt === 'kosong', fn($query) => $query->whereNull('alt'))
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('pages.admin.media.index', [
            'notReady' => false,
            'items'    => $items,
            'folders'  => Media::query()->whereNotNull('folder')->distinct()->orderBy('folder')->pluck('folder'),
            'total'    => Media::count(),
            'noAlt'    => Media::whereNull('alt')->count(),
            'q'        => $q,
            'source'   => $source,
            'folder'   => $folder,
            'alt'      => $alt,
            'filtered' => $q !== '' || $source || $folder || $alt,
            'bulkMax'  => self::BULK_MAX,
        ]);
    }

    /** Unggah satu atau banyak gambar. "Ini logo" memangkas ruang kosong (QA7 butir 3). */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'files'   => ['required', 'array', 'max:' . self::BULK_MAX],
            'files.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:' . ImageStore::MAX_KB, ImageStore::DIMENSIONS],
            'alt_id'  => ['nullable', 'string', 'max:200'],
            'alt_en'  => ['nullable', 'string', 'max:200'],
        ], [
            'files.required' => 'Tidak ada berkas yang terbaca. Bila memilih banyak berkas, total ukurannya mungkin melebihi batas server; kurangi jumlahnya.',
            'files.max'      => 'Maksimal ' . self::BULK_MAX . ' berkas sekali unggah.',
            'files.*.mimes'  => 'Setiap berkas harus JPG, PNG, atau WebP.',
            'files.*.max'    => 'Setiap berkas maksimal 5 MB.',
            'files.*.file'   => 'Salah satu berkas gagal terunggah.',
        ], ['alt_id' => 'alt (ID)', 'alt_en' => 'alt (EN)']);

        $alt  = $this->altPair($request->input('alt_id'), $request->input('alt_en'));
        $logo = $request->boolean('is_logo');
        $made = 0;

        foreach ($request->file('files') as $file) {
            $path  = $logo ? ImageStore::storeLogo($file, 'media') : ImageStore::store($file, 'media');
            $media = MediaStore::register($path, $file->getClientOriginalName());

            if ($media && $alt) {
                $media->update(['alt' => $alt]);
            }

            $made++;
        }

        ActivityLog::record('media.created', "mengunggah {$made} gambar ke Media Library");

        return redirect()->route('admin.media.index')
            ->with('status', "{$made} gambar ditambahkan." . ($alt ? '' : ' Isi alt teks lewat halaman tiap gambar.'));
    }

    public function show(Media $media): View
    {
        return view('pages.admin.media.show', [
            'media'  => $media->load('uploader'),
            'usages' => MediaUsage::find($media->path),
        ]);
    }

    public function update(Request $request, Media $media): RedirectResponse
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:160'],
            'alt_id' => ['nullable', 'string', 'max:200'],
            'alt_en' => ['nullable', 'string', 'max:200'],
        ], [], ['name' => 'nama', 'alt_id' => 'alt (ID)', 'alt_en' => 'alt (EN)']);

        $media->update([
            'name' => trim($data['name']),
            'alt'  => $this->altPair($data['alt_id'] ?? null, $data['alt_en'] ?? null),
        ]);

        ActivityLog::record('media.updated', "mengubah gambar \"{$media->name}\" di Media Library", $media);

        return redirect()->route('admin.media.show', $media)->with('status', 'Perubahan gambar disimpan.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        if ($media->isBundled()) {
            return back()->with('error', 'Ini berkas bawaan situs (public/img) dan tidak bisa dihapus dari sini. Alt dan namanya tetap bisa diubah.');
        }

        $usages = MediaUsage::find($media->path);

        if (count($usages) > 0) {
            return back()->with('error', 'Gambar masih dipakai di ' . count($usages) . ' tempat (lihat daftar di bawah). Ganti atau lepas gambar itu dulu.');
        }

        $name = $media->name;
        MediaStore::remove($media);

        ActivityLog::record('media.deleted', "menghapus gambar \"{$name}\" dari Media Library");

        return redirect()->route('admin.media.index')->with('status', "Gambar \"{$name}\" dihapus.");
    }

    /* ---- Pemilih media (JSON), untuk formulir modul lain ---- */

    public function picker(Request $request): JsonResponse
    {
        if (! Schema::hasTable('media')) {
            return response()->json(['data' => [], 'more' => false, 'notReady' => true]);
        }

        $q = trim((string) $request->query('q'));

        $page = Media::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $query->where(fn($w) => $w->where('name', 'like', $like)->orWhere('path', 'like', $like));
            })
            ->orderByDesc('id')
            ->simplePaginate(24)
            ->withQueryString();

        return response()->json([
            'data' => $page->getCollection()->map(fn(Media $m) => $m->toPicker())->values(),
            'more' => $page->hasMorePages(),
        ]);
    }

    /** Unggah satu gambar dari dalam pemilih; langsung dikembalikan untuk dipilih. */
    public function pickerUpload(Request $request): JsonResponse
    {
        $request->validate([
            'file'   => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:' . ImageStore::MAX_KB, ImageStore::DIMENSIONS],
            'alt_id' => ['nullable', 'string', 'max:200'],
        ], ['file.max' => 'Gambar maksimal 5 MB.', 'file.mimes' => 'Gambar harus JPG, PNG, atau WebP.', 'file.required' => 'Pilih gambar dulu.']);

        $file  = $request->file('file');
        $path  = $request->boolean('is_logo') ? ImageStore::storeLogo($file, 'media') : ImageStore::store($file, 'media');
        $media = MediaStore::register($path, $file->getClientOriginalName());

        if (! $media) {
            // Katalog belum siap (migrate belum dijalankan): tetap kembalikan path agar bisa dipakai.
            return response()->json(['path' => $path, 'url' => asset($path), 'thumb' => asset($path), 'name' => $file->getClientOriginalName(), 'alt' => '']);
        }

        if ($alt = $this->altPair($request->input('alt_id'), null)) {
            $media->update(['alt' => $alt]);
        }

        ActivityLog::record('media.created', "mengunggah gambar \"{$media->name}\" lewat pemilih media", $media);

        return response()->json($media->fresh()->toPicker());
    }

    /** Pasangan alt {id, en}; null bila keduanya kosong (agar filter "alt kosong" akurat). */
    protected function altPair(?string $id, ?string $en): ?array
    {
        $id = trim((string) $id);
        $en = trim((string) $en);

        return $id === '' && $en === '' ? null : ['id' => $id, 'en' => $en];
    }
}
