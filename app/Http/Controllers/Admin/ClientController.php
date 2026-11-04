<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ClientCategory;
use App\Support\ImageStore;
use App\Support\MediaStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * B3h: Klien & Mitra. Super Admin dan Admin mengelola semuanya (menu tidak tampil untuk Editor).
 * Logo diproses seperti logo brand: ruang kosong dipangkas, WebP, maks. 5 MB.
 */
class ClientController extends Controller
{
    /** Batas unggah massal; PHP bawaan membatasi 20 berkas per permintaan (max_file_uploads). */
    protected const BULK_MAX = 20;

    public function index(Request $request): View
    {
        $q          = trim((string) $request->query('q'));
        $categoryId = $request->query('kategori');
        $status     = $request->query('status');   // tampil | tersembunyi | beranda
        $permission = $request->query('izin');     // belum | sudah

        $categories = ClientCategory::withCount('clients')->orderBy('sort_order')->orderBy('id')->get();

        $clients = Client::query()
            ->when($q !== '', fn($query) => $query->where('name', 'like', '%' . addcslashes($q, '%_\\') . '%'))
            ->when($categoryId, fn($query) => $query->where('client_category_id', $categoryId))
            ->when($status === 'tampil', fn($query) => $query->where('is_visible', true))
            ->when($status === 'tersembunyi', fn($query) => $query->where('is_visible', false))
            ->when($status === 'beranda', fn($query) => $query->where('show_on_home', true))
            ->when($permission === 'belum', fn($query) => $query->where('permission_ok', false))
            ->when($permission === 'sudah', fn($query) => $query->where('permission_ok', true))
            ->orderBy('sort_order')->orderBy('id')
            ->get()
            ->groupBy('client_category_id');

        $filtered = $q !== '' || $categoryId || $status || $permission;

        return view('pages.admin.clients.index', [
            'categories' => $categories,
            'groups'     => $clients,
            'total'      => Client::count(),
            'homeCount'  => Client::where('show_on_home', true)->count(),
            'q'          => $q,
            'category'   => $categoryId,
            'status'     => $status,
            'permission' => $permission,
            'filtered'   => $filtered,
            'bulkMax'    => self::BULK_MAX,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (ClientCategory::count() === 0) {
            return redirect()->route('admin.clientcat.create')
                ->with('error', 'Buat kategori klien dulu sebelum menambah logo.');
        }

        return view('pages.admin.clients.form', [
            'client'     => null,
            'categories' => $this->categories(),
            'preset'     => $request->query('kategori'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);

        $client = new Client($data);
        $client->logo       = $this->chosenLogo($request);
        $client->sort_order = ((int) Client::where('client_category_id', $client->client_category_id)->max('sort_order')) + 1;
        $client->created_by = $request->user()->id;
        $client->updated_by = $request->user()->id;
        $client->save();

        ActivityLog::record('client.created', 'menambah klien ' . $this->label($client), $client);

        return redirect()->route('admin.clients.index', ['kategori' => $client->client_category_id])
            ->with('status', 'Klien ' . $this->label($client) . ' ditambahkan.');
    }

    /** Unggah banyak logo sekaligus ke satu kategori (nama dikosongkan; bisa diisi lewat Ubah). */
    public function bulk(Request $request): RedirectResponse
    {
        $request->validate([
            'client_category_id' => ['required', 'exists:client_categories,id'],
            'files'              => ['required', 'array', 'max:' . self::BULK_MAX],
            'files.*'            => ['file', 'mimes:jpg,jpeg,png,webp', 'max:' . ImageStore::MAX_KB, ImageStore::DIMENSIONS],
        ], [
            'files.required' => 'Tidak ada berkas yang terbaca. Bila memilih banyak berkas, total ukurannya mungkin melebihi batas server; kurangi jumlahnya.',
            'files.max'      => 'Maksimal ' . self::BULK_MAX . ' berkas sekali unggah.',
            'files.*.mimes'  => 'Setiap berkas harus JPG, PNG, atau WebP.',
            'files.*.max'    => 'Setiap berkas maksimal 5 MB.',
            'files.*.file'   => 'Salah satu berkas gagal terunggah.',
        ], ['client_category_id' => 'kategori', 'files' => 'berkas logo']);

        $categoryId = (int) $request->input('client_category_id');
        $order      = (int) Client::where('client_category_id', $categoryId)->max('sort_order');
        $made       = 0;

        foreach ($request->file('files') as $file) {
            $client = new Client([
                'client_category_id' => $categoryId,
                'logo'               => ImageStore::storeLogo($file, 'clients'),
                'sort_order'         => ++$order,
                'is_visible'         => true,
                'show_on_home'       => false,
                'permission_ok'      => false,
                'created_by'         => $request->user()->id,
                'updated_by'         => $request->user()->id,
            ]);
            $client->save();
            $made++;
        }

        $category = ClientCategory::find($categoryId);
        ActivityLog::record('client.bulk', "mengunggah {$made} logo klien ke kategori \"{$category?->label('id')}\"", $category);

        return redirect()->route('admin.clients.index', ['kategori' => $categoryId])
            ->with('status', "{$made} logo ditambahkan. Isi nama atau atur tampil di beranda lewat Ubah bila perlu.");
    }

    public function edit(Client $client): View
    {
        return view('pages.admin.clients.form', [
            'client'     => $client->load('category'),
            'categories' => $this->categories(),
            'preset'     => null,
        ]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $this->validated($request, $client);

        $oldCategory = $client->client_category_id;
        $client->fill($data);

        if ($client->client_category_id !== $oldCategory) {
            // Pindah kategori: taruh di urutan paling akhir kategori baru.
            $client->sort_order = ((int) Client::where('client_category_id', $client->client_category_id)->max('sort_order')) + 1;
        }

        $old = null;

        if ($request->hasFile('logo') || filled($request->input('logo_path'))) {
            $old          = $client->getOriginal('logo');
            $client->logo = $this->chosenLogo($request);
        }

        $client->updated_by = $request->user()->id;
        $client->save();

        if ($old && $old !== $client->logo) {
            ImageStore::delete($old); // hanya berkas unggahan (storage/) yang tak dipakai lagi; logo bawaan di public/img tidak disentuh
        }

        ActivityLog::record('client.updated', 'mengubah klien ' . $this->label($client), $client);

        return redirect()->route('admin.clients.index', ['kategori' => $client->client_category_id])
            ->with('status', 'Perubahan klien ' . $this->label($client) . ' disimpan.');
    }

    public function toggle(Client $client): RedirectResponse
    {
        $client->is_visible = ! $client->is_visible;
        $client->updated_by = auth()->id();
        $client->save();

        ActivityLog::record('client.updated', ($client->is_visible ? 'menampilkan' : 'menyembunyikan') . ' klien ' . $this->label($client), $client);

        return back()->with('status', 'Klien ' . $this->label($client) . ($client->is_visible ? ' ditampilkan di situs.' : ' disembunyikan dari situs.'));
    }

    /** Centang atau lepas tanda "tampil di beranda". */
    public function home(Client $client): RedirectResponse
    {
        $client->show_on_home = ! $client->show_on_home;
        $client->updated_by   = auth()->id();
        $client->save();

        ActivityLog::record('client.updated', ($client->show_on_home ? 'menampilkan di beranda' : 'melepas dari beranda') . ' klien ' . $this->label($client), $client);

        return back()->with('status', $client->show_on_home
            ? 'Klien ' . $this->label($client) . ' tampil di strip logo beranda.'
            : 'Klien ' . $this->label($client) . ' dilepas dari beranda.');
    }

    /** Simpan urutan hasil seret-dan-lepas (satu kategori per permintaan). */
    public function sort(Request $request): JsonResponse
    {
        $request->validate(['ids' => ['required', 'array', 'max:300'], 'ids.*' => ['integer']]);

        $ids   = array_values(array_unique(array_map('intval', $request->input('ids'))));
        $known = Client::whereIn('id', $ids)->pluck('id')->all();
        $order = 0;

        foreach ($ids as $id) {
            if (in_array($id, $known, true)) {
                Client::whereKey($id)->update(['sort_order' => ++$order]);
            }
        }

        ActivityLog::record('client.sorted', 'mengubah urutan logo klien');

        return response()->json(['ok' => true]);
    }

    public function destroy(Client $client): RedirectResponse
    {
        $label = $this->label($client);
        $logo  = $client->logo;

        $client->delete();

        // Hapus berkas unggahan hanya bila tidak dipakai klien lain.
        if (! Client::where('logo', $logo)->exists()) {
            ImageStore::delete($logo);
        }

        ActivityLog::record('client.deleted', "menghapus klien {$label}");

        return redirect()->route('admin.clients.index')->with('status', "Klien {$label} dihapus.");
    }

    /* ------------------------------------------------------------------ */

    /**
     * Logo dari unggahan berkas, atau dipilih dari Media Library (B3b). Jalur pilihan memakai berkas yang sudah ada
     * (tidak dipangkas ulang); path divalidasi MediaStore::pick (hanya storage/, img/, wp-uploads/).
     */
    protected function chosenLogo(Request $request): string
    {
        if ($request->hasFile('logo')) {
            return ImageStore::storeLogo($request->file('logo'), 'clients');
        }

        $picked = MediaStore::pick($request->input('logo_path'));

        if (! $picked) {
            throw ValidationException::withMessages(['logo' => 'Logo dari Media Library tidak ditemukan. Pilih lagi atau unggah berkas.']);
        }

        return $picked;
    }

    protected function categories()
    {
        return ClientCategory::orderBy('sort_order')->orderBy('id')->get();
    }

    protected function label(Client $client): string
    {
        return filled($client->name) ? "\"{$client->name}\"" : "(tanpa nama, #{$client->id})";
    }

    protected function validated(Request $request, ?Client $client): array
    {
        $data = $request->validate([
            'client_category_id' => ['required', 'exists:client_categories,id'],
            'name'               => ['nullable', 'string', 'max:120'],
            'logo_path'          => ['nullable', 'string', 'max:255'],
            'logo'               => [$client ? 'nullable' : 'required_without:logo_path', 'file', 'mimes:jpg,jpeg,png,webp', 'max:' . ImageStore::MAX_KB, ImageStore::DIMENSIONS],
            'is_visible'         => ['required', 'boolean'],
            'show_on_home'       => ['required', 'boolean'],
            'permission_ok'      => ['required', 'boolean'],
        ], [
            'logo.required_without' => 'Pilih berkas logo, atau pilih dari Media Library.',
            'logo.mimes'    => 'Logo harus berformat JPG, PNG, atau WebP.',
        ], ['client_category_id' => 'kategori', 'name' => 'nama', 'logo' => 'logo']);

        return [
            'client_category_id' => (int) $data['client_category_id'],
            'name'               => filled($data['name'] ?? null) ? trim($data['name']) : null,
            'is_visible'         => (bool) $data['is_visible'],
            'show_on_home'       => (bool) $data['show_on_home'],
            'permission_ok'      => (bool) $data['permission_ok'],
        ];
    }
}
