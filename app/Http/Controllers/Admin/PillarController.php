<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\SlugRedirects;
use App\Models\Pillar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * B3c: Pilar Bisnis (QA8). Super Admin dan Admin boleh menambah, mengubah, mengurutkan,
 * dan menyembunyikan; hanya Super Admin yang boleh menghapus (lihat routes/admin.php).
 */
class PillarController extends Controller
{
    public function index(): View
    {
        $pillars = Pillar::query()
            ->with('brands')
            ->orderBy('sort_order')->orderBy('id')
            ->get();

        return view('pages.admin.pillars.index', ['pillars' => $pillars]);
    }

    public function create(): View
    {
        return view('pages.admin.pillars.form', ['pillar' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);
        $data['sort_order'] = (int) Pillar::max('sort_order') + 1;

        $pillar = Pillar::create($data);

        ActivityLog::record('pillar.created', "menambah pilar bisnis \"{$pillar->name}\"", $pillar);

        return redirect()->route('admin.pillars.index')
            ->with('status', "Pilar \"{$pillar->name}\" dibuat. Tambahkan brand pertamanya.");
    }

    public function edit(Pillar $pillar): View
    {
        return view('pages.admin.pillars.form', ['pillar' => $pillar->loadCount('brands')]);
    }

    public function update(Request $request, Pillar $pillar): RedirectResponse
    {
        $oldSlug = $pillar->slug;

        $pillar->update($this->validated($request, $pillar));

        // B3n: slug pilar berubah -> alamat pilar dan semua brand di dalamnya berpindah; Redirect 301 otomatis.
        $redirected = 0;

        if ($oldSlug !== $pillar->slug && $pillar->is_visible) {
            $redirected += SlugRedirects::changed('units.show', ['slug' => $oldSlug], ['slug' => $pillar->slug], 'slug pilar berubah');

            foreach ($pillar->brands()->get(['slug']) as $brand) {
                $redirected += SlugRedirects::changed(
                    'units.brand',
                    ['slug' => $oldSlug, 'brand' => $brand->slug],
                    ['slug' => $pillar->slug, 'brand' => $brand->slug],
                    'slug pilar berubah'
                );
            }
        }

        ActivityLog::record('pillar.updated', "mengubah pilar bisnis \"{$pillar->name}\"", $pillar);

        return redirect()->route('admin.pillars.index')->with('status', "Pilar \"{$pillar->name}\" disimpan." . SlugRedirects::note($redirected));
    }

    /** Tampilkan atau sembunyikan pilar di situs. */
    public function toggle(Pillar $pillar): RedirectResponse
    {
        $pillar->update(['is_visible' => ! $pillar->is_visible]);

        ActivityLog::record(
            'pillar.toggled',
            ($pillar->is_visible ? 'menampilkan' : 'menyembunyikan') . " pilar bisnis \"{$pillar->name}\"",
            $pillar
        );

        return back()->with('status', $pillar->is_visible
            ? "Pilar \"{$pillar->name}\" kini tampil di situs."
            : "Pilar \"{$pillar->name}\" disembunyikan dari situs.");
    }

    /** Hapus pilar (hanya Super Admin). Pilar yang masih punya brand atau pilar terakhir ditolak. */
    public function destroy(Pillar $pillar): RedirectResponse
    {
        $count = $pillar->brands()->count();

        if ($count > 0) {
            return back()->with('error', "Pilar ini masih memuat {$count} brand. Pindahkan atau hapus brand-nya dulu.");
        }

        if (Pillar::count() <= 1) {
            return back()->with('error', 'Pilar terakhir tidak bisa dihapus. Minimal harus tersisa satu pilar.');
        }

        $name = $pillar->name;
        $pillar->delete();

        ActivityLog::record('pillar.deleted', "menghapus pilar bisnis \"{$name}\"");

        return redirect()->route('admin.pillars.index')->with('status', "Pilar \"{$name}\" dihapus.");
    }

    /** Simpan urutan pilar hasil seret-dan-lepas. Menerima {"ids": [3, 1, 2, ...]}. */
    public function sort(Request $request): JsonResponse
    {
        $ids = $this->ids($request);

        $known = Pillar::whereIn('id', $ids)->pluck('id')->all();
        $order = 0;

        foreach ($ids as $id) {
            if (in_array($id, $known, true)) {
                Pillar::whereKey($id)->update(['sort_order' => ++$order]);
            }
        }

        ActivityLog::record('pillar.sorted', 'mengubah urutan pilar bisnis');

        return response()->json(['ok' => true]);
    }

    /** @return array<int, int> */
    protected function ids(Request $request): array
    {
        $request->validate(['ids' => ['required', 'array', 'max:200'], 'ids.*' => ['integer']]);

        return array_values(array_unique(array_map('intval', $request->input('ids'))));
    }

    protected function validated(Request $request, ?Pillar $pillar): array
    {
        $given = trim((string) $request->input('slug'));
        $request->merge(['slug' => Str::slug($given !== '' ? $given : (string) $request->input('name'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('pillars', 'slug')->ignore($pillar?->id),
            ],
            'icon'       => ['required', Rule::in(array_keys(Pillar::ICONS))],
            'is_visible' => ['nullable', 'boolean'],
        ], [
            'slug.regex'  => 'Slug hanya boleh huruf kecil, angka, dan tanda hubung.',
            'slug.unique' => 'Slug ini sudah dipakai pilar lain.',
            'icon.in'     => 'Pilih ikon dari daftar yang tersedia.',
        ], ['name' => 'nama pilar', 'icon' => 'ikon']);

        return [
            'name'       => trim($data['name']),
            'slug'       => $data['slug'],
            'icon'       => $data['icon'],
            'is_visible' => $request->boolean('is_visible'),
        ];
    }
}