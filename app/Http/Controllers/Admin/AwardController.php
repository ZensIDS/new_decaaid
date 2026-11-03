<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Award;
use App\Support\ImageStore;
use App\Support\MediaStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * B3i: modul Penghargaan & Sertifikat (K30: sederhana, tanpa jenis, penerbit, dan PDF).
 * Super Admin, Admin, dan Editor boleh menambah dan mengubah; hanya Super Admin dan Admin yang boleh
 * menghapus (lihat routes/admin.php). "Terbaru" ditentukan manual: hanya SATU penghargaan unggulan
 * (tampil besar di halaman Penghargaan); menandai yang baru otomatis melepas yang lama.
 */
class AwardController extends Controller
{
    protected const PER_PAGE = 10;

    public function index(Request $request): View
    {
        $q     = trim((string) $request->query('q'));
        $year  = $request->query('tahun');
        $state = $request->query('status');

        $items = Award::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $query->where(fn($w) => $w->where('title', 'like', $like)->orWhere('description', 'like', $like));
            })
            ->when($year, fn($query) => $query->where('year', $year))
            ->when($state === 'tampil', fn($query) => $query->where('is_visible', true))
            ->when($state === 'sembunyi', fn($query) => $query->where('is_visible', false))
            ->when($state === 'unggulan', fn($query) => $query->where('is_featured', true))
            ->orderByDesc('year')->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('pages.admin.awards.index', [
            'items'    => $items,
            'years'    => Award::query()->distinct()->orderByDesc('year')->pluck('year'),
            'q'        => $q,
            'year'     => $year,
            'state'    => $state,
            'filtered' => $q !== '' || $year || $state,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.awards.form', ['award' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $award             = new Award($data);
        $award->created_by = $request->user()->id;
        $award->updated_by = $request->user()->id;

        if ($request->hasFile('image')) {
            $award->image = ImageStore::store($request->file('image'), 'awards');
        } elseif ($picked = MediaStore::pick($request->input('image_path'))) {
            $award->image = $picked; // dipilih dari Media Library
        }

        $award->save();
        $this->keepSingleFeatured($award);

        ActivityLog::record('award.created', "menambah penghargaan \"{$award->t('title', 'id')}\"", $award);

        return redirect()->route('admin.awards.edit', $award)
            ->with('status', 'Penghargaan dibuat. ' . $this->visibilityNote($award));
    }

    public function edit(Award $award): View
    {
        return view('pages.admin.awards.form', ['award' => $award->load('author')]);
    }

    public function update(Request $request, Award $award): RedirectResponse
    {
        $data = $this->validated($request);

        $award->fill($data);
        $award->updated_by = $request->user()->id;
        $award->is_dummy   = false; // sudah disentuh admin: tidak ikut tertimpa --refresh

        $oldImage = $award->image;

        if ($request->hasFile('image')) {
            $award->image = ImageStore::store($request->file('image'), 'awards');
        } elseif ($request->boolean('remove_image')) {
            $award->image = null;
        } elseif ($picked = MediaStore::pick($request->input('image_path'))) {
            $award->image = $picked; // dipilih dari Media Library
        }

        $award->save();
        $this->keepSingleFeatured($award);
        $this->dropOldImage($oldImage, $award->image);

        ActivityLog::record('award.updated', "mengubah penghargaan \"{$award->t('title', 'id')}\"", $award);

        return redirect()->route('admin.awards.edit', $award)
            ->with('status', 'Perubahan disimpan. ' . $this->visibilityNote($award));
    }

    /** Tampilkan atau sembunyikan dari situs. */
    public function toggle(Award $award): RedirectResponse
    {
        $award->is_visible = ! $award->is_visible;
        $award->updated_by = auth()->id();
        $award->save();

        ActivityLog::record('award.updated', ($award->is_visible ? 'menampilkan' : 'menyembunyikan') . " penghargaan \"{$award->t('title', 'id')}\"", $award);

        return back()->with('status', "Penghargaan \"{$award->t('title', 'id')}\" " . ($award->is_visible ? 'ditampilkan di situs.' : 'disembunyikan dari situs.'));
    }

    /** Jadikan penghargaan ini yang "terbaru" (sorotan besar), atau lepas tandanya. */
    public function feature(Award $award): RedirectResponse
    {
        $award->is_featured = ! $award->is_featured;
        $award->updated_by  = auth()->id();
        $award->save();
        $this->keepSingleFeatured($award);

        ActivityLog::record('award.updated', ($award->is_featured ? 'menjadikan sorotan' : 'melepas sorotan') . " penghargaan \"{$award->t('title', 'id')}\"", $award);

        return back()->with('status', $award->is_featured
            ? "\"{$award->t('title', 'id')}\" menjadi penghargaan terbaru (sorotan besar)."
            : "Sorotan \"{$award->t('title', 'id')}\" dilepas. Situs memakai penghargaan terbaru berdasarkan tahun.");
    }

    public function destroy(Award $award): RedirectResponse
    {
        $title = $award->t('title', 'id');
        $image = $award->image;

        $award->delete();

        // Hapus berkas unggahan hanya bila tidak dipakai penghargaan lain (ImageStore hanya menyentuh storage/).
        $this->dropOldImage($image, null);

        ActivityLog::record('award.deleted', "menghapus penghargaan \"{$title}\"");

        return redirect()->route('admin.awards.index')->with('status', "Penghargaan \"{$title}\" dihapus.");
    }

    /* ------------------------------------------------------------------ */

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title.id'           => ['required', 'string', 'max:200'],
            'title.en'           => ['nullable', 'string', 'max:200'],
            'description.id'     => ['nullable', 'string', 'max:600'],
            'description.en'     => ['nullable', 'string', 'max:600'],
            'year'               => ['required', 'integer', 'min:1990', 'max:' . (now()->year + 1)],
            'is_visible'         => ['required', 'boolean'],
            'is_featured'        => ['required', 'boolean'],
            'translation_status' => ['required', Rule::in(array_keys(Award::TRANSLATION))],
            'image'              => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:' . ImageStore::MAX_KB],
            'image_alt.id'       => ['nullable', 'string', 'max:200'],
            'image_alt.en'       => ['nullable', 'string', 'max:200'],
        ], [], [
            'title.id'       => 'judul (ID)', 'title.en' => 'judul (EN)',
            'description.id' => 'deskripsi (ID)', 'description.en' => 'deskripsi (EN)',
            'year'           => 'tahun', 'translation_status' => 'status terjemahan', 'image' => 'foto atau piagam',
        ]);

        $clean = fn(array $pair) => ['id' => trim((string) ($pair['id'] ?? '')), 'en' => trim((string) ($pair['en'] ?? ''))];

        return [
            'title'              => $clean($data['title']),
            'description'        => $clean($data['description'] ?? []),
            'year'               => (int) $data['year'],
            'image_alt'          => $clean($data['image_alt'] ?? []),
            'is_visible'         => (bool) $data['is_visible'],
            'is_featured'        => (bool) $data['is_featured'],
            'translation_status' => $data['translation_status'],
        ];
    }

    /** Hanya satu penghargaan unggulan: yang lain dilepas bila $award ditandai. */
    protected function keepSingleFeatured(Award $award): void
    {
        if ($award->is_featured) {
            Award::where('id', '!=', $award->id)->where('is_featured', true)->update(['is_featured' => false]);
        }
    }

    /** Hapus berkas lama bila sudah tidak dipakai penghargaan mana pun. */
    protected function dropOldImage(?string $old, ?string $current): void
    {
        if ($old && $old !== $current && ! Award::where('image', $old)->exists()) {
            ImageStore::delete($old);
        }
    }

    protected function visibilityNote(Award $award): string
    {
        return $award->is_visible ? 'Penghargaan tampil di situs.' : 'Penghargaan disembunyikan, belum tampil di situs.';
    }
}
