<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\NewsCategory;
use App\Support\SlugRedirects;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * B3d: kategori berita (QA12): dikelola penuh oleh Super Admin dan Admin.
 * Slug dijaga agar aman terhadap rute dan tidak bentrok dengan slug lain.
 */
class NewsCategoryController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.news.categories.index', [
            'categories' => NewsCategory::withCount('news')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.news.categories.form', ['category' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $category = NewsCategory::create($this->validated($request, null));

        ActivityLog::record('newscat.created', "menambah kategori berita \"{$category->label('id')}\"", $category);

        return redirect()->route('admin.newscat.index')->with('status', "Kategori \"{$category->label('id')}\" dibuat.");
    }

    public function edit(NewsCategory $category): View
    {
        return view('pages.admin.news.categories.form', ['category' => $category->loadCount('news')]);
    }

    public function update(Request $request, NewsCategory $category): RedirectResponse
    {
        $oldSlug = $category->slug;

        $category->update($this->validated($request, $category));

        // B3n: slug berubah -> Redirect 301 otomatis (ID dan EN) dari halaman kategori lama ke yang baru.
        $redirected = $oldSlug !== $category->slug
            ? SlugRedirects::changed('news.category', ['slug' => $oldSlug], ['slug' => $category->slug], 'slug kategori berita berubah')
            : 0;

        ActivityLog::record('newscat.updated', "mengubah kategori berita \"{$category->label('id')}\"", $category);

        return redirect()->route('admin.newscat.index')
            ->with('status', "Kategori \"{$category->label('id')}\" disimpan." . SlugRedirects::note($redirected));
    }

    public function destroy(NewsCategory $category): RedirectResponse
    {
        $count = $category->news()->count();

        if ($count > 0) {
            return back()->with('error', "Kategori ini masih dipakai {$count} berita. Pindahkan beritanya ke kategori lain dulu.");
        }

        $name = $category->label('id');
        $category->delete();

        ActivityLog::record('newscat.deleted', "menghapus kategori berita \"{$name}\"");

        return redirect()->route('admin.newscat.index')->with('status', "Kategori \"{$name}\" dihapus.");
    }

    protected function validated(Request $request, ?NewsCategory $category): array
    {
        $given = trim((string) $request->input('slug'));
        $request->merge(['slug' => Str::slug($given !== '' ? $given : (string) $request->input('name.id'))]);

        $data = $request->validate([
            'name.id'    => ['required', 'string', 'max:60'],
            'name.en'    => ['nullable', 'string', 'max:60'],
            'slug'       => [
                'required', 'max:60', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::notIn(NewsCategory::RESERVED_SLUGS),
                Rule::unique('news_categories', 'slug')->ignore($category?->id),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [
            'slug.regex'  => 'Slug hanya boleh huruf kecil, angka, dan tanda hubung.',
            'slug.not_in' => 'Slug ini dicadangkan sistem karena bisa bentrok dengan rute. Pakai slug lain.',
            'slug.unique' => 'Slug ini sudah dipakai kategori lain.',
        ], ['name.id' => 'nama (ID)', 'name.en' => 'nama (EN)', 'sort_order' => 'urutan']);

        return [
            'slug'       => $data['slug'],
            'name'       => ['id' => trim($data['name']['id']), 'en' => trim((string) ($data['name']['en'] ?? ''))],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }
}
