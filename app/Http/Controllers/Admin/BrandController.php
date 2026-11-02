<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\Pillar;
use App\Support\ImageStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * B3c: Brand/perusahaan di dalam pilar (QA8). Super Admin dan Admin boleh menambah, mengubah,
 * memindahkan antarpilar, mengurutkan, dan menyembunyikan; hanya Super Admin yang boleh menghapus.
 * Kolom deskripsi, Instagram, TikTok, dan tautan boleh kosong: yang kosong tidak tampil di situs.
 */
class BrandController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $pillars = $this->pillars();

        if ($pillars->isEmpty()) {
            return redirect()->route('admin.pillars.create')->with('error', 'Buat pilar dulu sebelum menambah brand.');
        }

        $pillarId = (int) $request->query('pillar');
        $pillarId = $pillars->contains('id', $pillarId) ? $pillarId : $pillars->first()->id;

        return view('pages.admin.pillars.brand-form', [
            'brand'           => null,
            'pillars'         => $pillars,
            'defaultPillarId' => $pillarId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);

        $brand             = new Brand($data);
        $brand->sort_order = (int) Brand::where('pillar_id', $brand->pillar_id)->max('sort_order') + 1;
        $brand->created_by = $request->user()->id;
        $brand->updated_by = $request->user()->id;

        if ($request->hasFile('logo')) {
            $brand->logo = ImageStore::storeLogo($request->file('logo'), 'brands');
        }

        $brand->save();

        ActivityLog::record('brand.created', "menambah brand \"{$brand->name}\"", $brand);

        return redirect()->route('admin.pillars.index')
            ->with('status', "Brand \"{$brand->name}\" dibuat. " . $this->visibilityNote($brand));
    }

    public function edit(Brand $brand): View
    {
        return view('pages.admin.pillars.brand-form', [
            'brand'           => $brand->load(['pillar', 'author']),
            'pillars'         => $this->pillars(),
            'defaultPillarId' => $brand->pillar_id,
        ]);
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $data = $this->validated($request, $brand);

        $oldLogo     = $brand->logo;
        $oldPillarId = $brand->pillar_id;

        $brand->fill($data);
        $brand->updated_by = $request->user()->id;
        $brand->is_dummy   = false; // sudah disentuh admin: tidak ikut tertimpa --refresh

        if ((int) $brand->pillar_id !== (int) $oldPillarId) {
            // Dipindah ke pilar lain: taruh di urutan paling akhir pilar tujuan.
            $brand->sort_order = (int) Brand::where('pillar_id', $brand->pillar_id)->max('sort_order') + 1;
        }

        if ($request->hasFile('logo')) {
            $brand->logo = ImageStore::storeLogo($request->file('logo'), 'brands');
        } elseif ($request->boolean('remove_logo')) {
            $brand->logo = null;
        }

        $brand->save();

        if ($oldLogo && $oldLogo !== $brand->logo) {
            $this->discardLogo($oldLogo);
        }

        ActivityLog::record('brand.updated', "mengubah brand \"{$brand->name}\"", $brand);

        return redirect()->route('admin.pillars.index')
            ->with('status', "Brand \"{$brand->name}\" disimpan. " . $this->visibilityNote($brand));
    }

    /** Tampilkan atau sembunyikan brand di situs. */
    public function toggle(Brand $brand): RedirectResponse
    {
        $brand->update(['is_visible' => ! $brand->is_visible]);

        ActivityLog::record(
            'brand.toggled',
            ($brand->is_visible ? 'menampilkan' : 'menyembunyikan') . " brand \"{$brand->name}\"",
            $brand
        );

        return back()->with('status', $brand->is_visible
            ? "Brand \"{$brand->name}\" kini tampil di situs."
            : "Brand \"{$brand->name}\" disembunyikan dari situs.");
    }

    /** Hapus brand (hanya Super Admin). Untuk sekadar menyembunyikan, pakai tombol mata. */
    public function destroy(Brand $brand): RedirectResponse
    {
        $name = $brand->name;
        $logo = $brand->logo;

        $brand->delete();
        $this->discardLogo($logo);

        ActivityLog::record('brand.deleted', "menghapus brand \"{$name}\"");

        return redirect()->route('admin.pillars.index')->with('status', "Brand \"{$name}\" dihapus.");
    }

    /** Simpan urutan brand di dalam satu pilar. Menerima {"pillar_id": 1, "ids": [5, 3, 4]}. */
    public function sort(Request $request): JsonResponse
    {
        $request->validate([
            'pillar_id' => ['required', 'integer', Rule::exists('pillars', 'id')],
            'ids'       => ['required', 'array', 'max:200'],
            'ids.*'     => ['integer'],
        ]);

        $pillarId = (int) $request->input('pillar_id');
        $ids      = array_values(array_unique(array_map('intval', $request->input('ids'))));
        $known    = Brand::where('pillar_id', $pillarId)->whereIn('id', $ids)->pluck('id')->all();
        $order    = 0;

        foreach ($ids as $id) {
            if (in_array($id, $known, true)) {
                Brand::whereKey($id)->update(['sort_order' => ++$order]);
            }
        }

        ActivityLog::record('brand.sorted', 'mengubah urutan brand');

        return response()->json(['ok' => true]);
    }

    protected function pillars()
    {
        return Pillar::orderBy('sort_order')->orderBy('id')->get();
    }

    protected function visibilityNote(Brand $brand): string
    {
        return $brand->is_visible ? '' : 'Brand ini disembunyikan dari situs.';
    }

    /** Hapus berkas logo unggahan bila tidak dipakai brand lain. Logo bawaan di public/img tidak disentuh. */
    protected function discardLogo(?string $path): void
    {
        if ($path && str_starts_with($path, 'storage/') && ! Brand::where('logo', $path)->exists()) {
            ImageStore::delete($path);
        }
    }

    protected function validated(Request $request, ?Brand $brand): array
    {
        $given = trim((string) $request->input('slug'));
        $request->merge([
            'slug' => Str::slug($given !== '' ? $given : (string) $request->input('name')),
            'url'  => $this->normalizeUrl((string) $request->input('url')),
            'services' => $this->cleanServices($request),
        ]);

        $handle = 'regex:/^(https?:\/\/\S+|@?[A-Za-z0-9._]+)$/';

        $data = $request->validate([
            'pillar_id' => ['required', Rule::exists('pillars', 'id')],
            'name'      => ['required', 'string', 'max:120'],
            'slug'      => [
                'required', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('brands', 'slug')->ignore($brand?->id),
            ],
            'description.id' => ['nullable', 'string', 'max:4000'],
            'description.en' => ['nullable', 'string', 'max:4000'],
            'instagram'      => ['nullable', 'string', 'max:120', $handle],
            'tiktok'         => ['nullable', 'string', 'max:120', $handle],
            'url'            => ['nullable', 'url', 'max:255'],
            'logo'           => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:' . ImageStore::MAX_KB],
            'is_visible'     => ['nullable', 'boolean'],
            'translation_status' => ['required', Rule::in(array_keys(Brand::TRANSLATION))],

            'services'                    => ['nullable', 'array'],
            'services.title.id'           => ['nullable', 'string', 'max:120'],
            'services.title.en'           => ['nullable', 'string', 'max:120'],
            'services.items'              => ['nullable', 'array', 'max:12'],
            'services.items.*.title.id'   => ['required', 'string', 'max:150'],
            'services.items.*.title.en'   => ['nullable', 'string', 'max:150'],
            'services.items.*.text.id'    => ['nullable', 'string', 'max:1500'],
            'services.items.*.text.en'    => ['nullable', 'string', 'max:1500'],
        ], [
            'slug.regex'    => 'Slug hanya boleh huruf kecil, angka, dan tanda hubung.',
            'slug.unique'   => 'Slug ini sudah dipakai brand lain.',
            'instagram.regex' => 'Isi nama akun (contoh: decaa.id) atau alamat lengkap yang diawali https://.',
            'tiktok.regex'    => 'Isi nama akun (contoh: decaa.id) atau alamat lengkap yang diawali https://.',
            'url.url'       => 'Alamat situs tidak valid. Contoh: https://www.decaa.id',
            'logo.mimetypes' => 'Logo harus berupa gambar JPG, PNG, atau WebP.',
            'logo.max'      => 'Logo terlalu besar (maksimal 5 MB).',
            'services.items.*.title.id.required' => 'Judul layanan (ID) wajib diisi untuk setiap butir.',
        ], [
            'pillar_id'          => 'pilar',
            'name'               => 'nama brand',
            'description.id'     => 'deskripsi (ID)',
            'description.en'     => 'deskripsi (EN)',
            'url'                => 'tautan situs',
            'translation_status' => 'status terjemahan',
        ]);

        $descId = trim((string) ($data['description']['id'] ?? ''));
        $descEn = trim((string) ($data['description']['en'] ?? ''));

        return [
            'pillar_id'   => (int) $data['pillar_id'],
            'name'        => trim($data['name']),
            'slug'        => $data['slug'],
            'description' => ($descId === '' && $descEn === '') ? null : ['id' => $descId, 'en' => $descEn],
            'instagram'   => $this->nullable($data['instagram'] ?? null),
            'tiktok'      => $this->nullable($data['tiktok'] ?? null),
            'url'         => $this->nullable($data['url'] ?? null),
            'services'    => $this->packServices($data['services'] ?? []),
            'is_visible'  => $request->boolean('is_visible'),
            'translation_status' => $data['translation_status'],
        ];
    }

    protected function nullable(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /** Tautan tanpa skema diberi https://, dan http:// dinormalkan jadi https:// (Q15, B3c). */
    protected function normalizeUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (! preg_match('#^[a-z][a-z0-9+.\-]*://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        return preg_replace('#^http://#i', 'https://', $url);
    }

    /** Buang butir layanan yang seluruhnya kosong sebelum divalidasi. */
    protected function cleanServices(Request $request): array
    {
        $items = [];

        foreach ((array) $request->input('services.items', []) as $item) {
            $item = (array) $item;
            $row  = [
                'title' => [
                    'id' => trim((string) ($item['title']['id'] ?? '')),
                    'en' => trim((string) ($item['title']['en'] ?? '')),
                ],
                'text' => [
                    'id' => trim((string) ($item['text']['id'] ?? '')),
                    'en' => trim((string) ($item['text']['en'] ?? '')),
                ],
            ];

            if (implode('', $row['title']) === '' && implode('', $row['text']) === '') {
                continue;
            }

            $items[] = $row;
        }

        return [
            'title' => [
                'id' => trim((string) $request->input('services.title.id', '')),
                'en' => trim((string) $request->input('services.title.en', '')),
            ],
            'items' => $items,
        ];
    }

    /** Bentuk simpanan: null bila tidak ada butir layanan. */
    protected function packServices(array $services): ?array
    {
        $items = [];

        foreach ($services['items'] ?? [] as $item) {
            $items[] = [
                'title' => ['id' => trim((string) $item['title']['id']), 'en' => trim((string) ($item['title']['en'] ?? ''))],
                'text'  => ['id' => trim((string) ($item['text']['id'] ?? '')), 'en' => trim((string) ($item['text']['en'] ?? ''))],
            ];
        }

        if (! $items) {
            return null;
        }

        return [
            'title' => [
                'id' => trim((string) ($services['title']['id'] ?? '')),
                'en' => trim((string) ($services['title']['en'] ?? '')),
            ],
            'items' => $items,
        ];
    }
}
