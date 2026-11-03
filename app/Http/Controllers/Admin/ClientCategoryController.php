<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ClientCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** B3h: kategori klien dikelola penuh (QA9). Kategori yang masih punya klien tidak bisa dihapus. */
class ClientCategoryController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.clients.categories.index', [
            'categories' => ClientCategory::withCount('clients')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.clients.categories.form', ['category' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);

        if (! $request->filled('sort_order')) {
            $data['sort_order'] = ((int) ClientCategory::max('sort_order')) + 1;
        }

        $category = ClientCategory::create($data);

        ActivityLog::record('clientcat.created', "menambah kategori klien \"{$category->label('id')}\"", $category);

        return redirect()->route('admin.clientcat.index')->with('status', "Kategori \"{$category->label('id')}\" dibuat.");
    }

    public function edit(ClientCategory $category): View
    {
        return view('pages.admin.clients.categories.form', ['category' => $category->loadCount('clients')]);
    }

    public function update(Request $request, ClientCategory $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        ActivityLog::record('clientcat.updated', "mengubah kategori klien \"{$category->label('id')}\"", $category);

        return redirect()->route('admin.clientcat.index')->with('status', "Kategori \"{$category->label('id')}\" disimpan.");
    }

    public function destroy(ClientCategory $category): RedirectResponse
    {
        $count = $category->clients()->count();

        if ($count > 0) {
            return back()->with('error', "Kategori ini masih berisi {$count} klien. Pindahkan atau hapus klien-kliennya dulu.");
        }

        $name = $category->label('id');
        $category->delete();

        ActivityLog::record('clientcat.deleted', "menghapus kategori klien \"{$name}\"");

        return redirect()->route('admin.clientcat.index')->with('status', "Kategori \"{$name}\" dihapus.");
    }

    protected function validated(Request $request, ?ClientCategory $category): array
    {
        $given = trim((string) $request->input('slug'));
        $request->merge(['slug' => Str::slug($given !== '' ? $given : (string) $request->input('name.id'))]);

        $data = $request->validate([
            'name.id'    => ['required', 'string', 'max:60'],
            'name.en'    => ['nullable', 'string', 'max:60'],
            'slug'       => [
                'required', 'max:40', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::notIn(ClientCategory::RESERVED_SLUGS),
                Rule::unique('client_categories', 'slug')->ignore($category?->id),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [
            'slug.regex'  => 'Slug hanya boleh huruf kecil, angka, dan tanda hubung.',
            'slug.not_in' => 'Slug ini dicadangkan sistem. Pakai slug lain.',
            'slug.unique' => 'Slug ini sudah dipakai kategori lain.',
        ], ['name.id' => 'nama (ID)', 'name.en' => 'nama (EN)', 'sort_order' => 'urutan']);

        return [
            'slug'       => $data['slug'],
            'name'       => ['id' => trim($data['name']['id']), 'en' => trim((string) ($data['name']['en'] ?? ''))],
            'sort_order' => (int) ($data['sort_order'] ?? ($category?->sort_order ?? 0)),
        ];
    }
}
