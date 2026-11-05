<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AutoTranslatesOnSave;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\Pillar;
use App\Models\Project;
use App\Support\ImageStore;
use App\Support\SlugRedirects;
use App\Support\MediaStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * B3g: modul Proyek (Portofolio). Super Admin, Admin, dan Editor boleh menambah dan mengubah;
 * hanya Super Admin dan Admin yang boleh menghapus (lihat routes/admin.php).
 */
class ProjectController extends Controller
{
    use AutoTranslatesOnSave;

    protected const PER_PAGE = 10;

    /** Maksimal foto di galeri proyek (v79). */
    protected const GALLERY_MAX = 12;

    public function index(Request $request): View
    {
        $q      = trim((string) $request->query('q'));
        $pillar = $request->query('pilar');
        $year   = $request->query('tahun');
        $state  = $request->query('status');

        $items = Project::query()->with('pillar')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $query->where(fn($w) => $w->where('title', 'like', $like)->orWhere('slug', 'like', $like)->orWhere('client', 'like', $like));
            })
            ->when($pillar, fn($query) => $query->where('pillar_id', $pillar))
            ->when($year, fn($query) => $query->where('year', $year))
            ->when($state === 'tampil', fn($query) => $query->where('is_visible', true))
            ->when($state === 'sembunyi', fn($query) => $query->where('is_visible', false))
            ->when($state === 'unggulan', fn($query) => $query->where('is_featured', true))
            ->orderByDesc('year')->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('pages.admin.projects.index', [
            'items'         => $items,
            'pillars'       => Pillar::orderBy('sort_order')->orderBy('id')->get(),
            'years'         => Project::query()->whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year'),
            'featuredCount' => Project::where('is_visible', true)->where('is_featured', true)->count(),
            'q'             => $q,
            'pillar'        => $pillar,
            'year'          => $year,
            'state'         => $state,
            'filtered'      => $q !== '' || $pillar || $year || $state,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.projects.form', $this->formData(null));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->autoTranslateRequest($request, null, Project::AUTO_FIELDS, Project::class);
        $data = $this->validated($request, null);

        $project             = new Project($data);
        $project->created_by = $request->user()->id;
        $project->updated_by = $request->user()->id;

        if ($request->hasFile('image')) {
            $project->image = ImageStore::store($request->file('image'), 'projects');
        } elseif ($picked = MediaStore::pick($request->input('image_path'))) {
            $project->image = $picked; // dipilih dari Media Library
        }

        $project->save();

        ActivityLog::record('project.created', "menambah proyek \"{$project->t('title', 'id')}\"", $project);

        return redirect()->route('admin.projects.edit', $project)
            ->with('status', 'Proyek dibuat. ' . $this->translationNote() . $this->visibilityNote($project))
            ->with('error', $this->translateWarning);
    }

    public function edit(Project $project): View
    {
        return view('pages.admin.projects.form', $this->formData($project->load(['pillar', 'brand', 'author'])));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->autoTranslateRequest($request, $project, Project::AUTO_FIELDS, Project::class);
        $data = $this->validated($request, $project);

        $oldSlug = $project->slug;

        $project->fill($data);
        $project->updated_by = $request->user()->id;
        $project->is_dummy   = false; // sudah disentuh admin: tidak ikut tertimpa --refresh

        if ($request->hasFile('image')) {
            $project->image = ImageStore::store($request->file('image'), 'projects');
        } elseif ($request->boolean('remove_image')) {
            $project->image = null;
        } elseif ($picked = MediaStore::pick($request->input('image_path'))) {
            $project->image = $picked; // dipilih dari Media Library
        }

        $project->save();

        // B3n: slug berubah pada proyek yang tampil -> Redirect 301 otomatis (ID dan EN).
        $redirected = $oldSlug !== $project->slug && $project->is_visible
            ? SlugRedirects::changed('portfolio.show', ['slug' => $oldSlug], ['slug' => $project->slug], 'slug proyek berubah')
            : 0;

        ActivityLog::record('project.updated', "mengubah proyek \"{$project->t('title', 'id')}\"", $project);

        return redirect()->route('admin.projects.edit', $project)
            ->with('status', 'Perubahan disimpan. ' . $this->translationNote() . $this->visibilityNote($project) . SlugRedirects::note($redirected))
            ->with('error', $this->translateWarning);
    }

    /** Tampilkan atau sembunyikan dari situs. */
    public function toggle(Project $project): RedirectResponse
    {
        $project->is_visible = ! $project->is_visible;
        $project->updated_by = auth()->id();
        $project->save();

        ActivityLog::record('project.updated', ($project->is_visible ? 'menampilkan' : 'menyembunyikan') . " proyek \"{$project->t('title', 'id')}\"", $project);

        return back()->with('status', "Proyek \"{$project->t('title', 'id')}\" " . ($project->is_visible ? 'ditampilkan di situs.' : 'disembunyikan dari situs.'));
    }

    /** Centang atau lepas tanda unggulan (beranda). */
    public function feature(Project $project): RedirectResponse
    {
        $project->is_featured = ! $project->is_featured;
        $project->updated_by  = auth()->id();
        $project->save();

        ActivityLog::record('project.updated', ($project->is_featured ? 'menandai unggulan' : 'melepas tanda unggulan') . " proyek \"{$project->t('title', 'id')}\"", $project);

        return back()->with('status', $project->is_featured
            ? "\"{$project->t('title', 'id')}\" ditandai unggulan." . $this->featuredNote()
            : "Tanda unggulan \"{$project->t('title', 'id')}\" dilepas.");
    }

    public function destroy(Project $project): RedirectResponse
    {
        $title = $project->t('title', 'id');
        $image = $project->image;

        $project->delete();

        // Hapus berkas unggahan hanya bila tidak dipakai proyek lain (ImageStore hanya menyentuh storage/).
        if ($image && ! Project::where('image', $image)->exists()) {
            ImageStore::delete($image);
        }

        ActivityLog::record('project.deleted', "menghapus proyek \"{$title}\"");

        return redirect()->route('admin.projects.index')->with('status', "Proyek \"{$title}\" dihapus.");
    }

    /* ------------------------------------------------------------------ */

    protected function formData(?Project $project): array
    {
        return [
            'project' => $project,
            'pillars' => Pillar::orderBy('sort_order')->orderBy('id')->get(),
            'brands'  => Brand::orderBy('sort_order')->orderBy('id')->get(['id', 'pillar_id', 'name']),
        ];
    }

    protected function validated(Request $request, ?Project $project): array
    {
        // Slug: dari isian, atau dibuat dari judul Indonesia dan dijamin unik.
        $given     = trim((string) $request->input('slug'));
        $generated = $given === '';
        $slug      = Str::limit(Str::slug($generated ? (string) $request->input('title.id') : $given), 100, '');

        if ($generated && $slug !== '') {
            $base = $slug;
            $n    = 2;
            while (Project::where('slug', $slug)->when($project, fn($q) => $q->whereKeyNot($project->id))->exists()
                || in_array($slug, Project::RESERVED_SLUGS, true)) {
                $slug = $base . '-' . $n++;
            }
        }

        $request->merge(['slug' => $slug]);

        $data = $request->validate([
            'title.id'           => ['required', 'string', 'max:200'],
            'title.en'           => ['nullable', 'string', 'max:200'],
            'summary.id'         => ['required', 'string', 'max:300'],
            'summary.en'         => ['nullable', 'string', 'max:300'],
            'description.id'     => ['required', 'string', 'max:5000'],
            'description.en'     => ['nullable', 'string', 'max:5000'],
            'scope.id'           => ['nullable', 'string', 'max:3000'],
            'scope.en'           => ['nullable', 'string', 'max:3000'],
            'pillar_id'          => ['required', 'exists:pillars,id'],
            'brand_id'           => ['nullable', 'exists:brands,id'],
            'client'             => ['required', 'string', 'max:160'],
            'year'               => ['required', 'integer', 'min:1990', 'max:' . (now()->year + 1)],
            'slug'               => [
                'required', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::notIn(Project::RESERVED_SLUGS),
                Rule::unique('projects', 'slug')->ignore($project?->id),
            ],
            'is_visible'         => ['required', 'boolean'],
            'is_featured'        => ['required', 'boolean'],
            'translation_status' => ['required', Rule::in(array_keys(Project::TRANSLATION))],
            'image'              => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:' . ImageStore::MAX_KB, ImageStore::DIMENSIONS],
            'gallery'            => ['nullable', 'array', 'max:' . self::GALLERY_MAX],
            'gallery.*'          => ['nullable', 'string', 'max:255'],
            'image_alt.id'       => ['nullable', 'string', 'max:200'],
            'image_alt.en'       => ['nullable', 'string', 'max:200'],
            'seo_title.id'       => ['nullable', 'string', 'max:120'],
            'seo_title.en'       => ['nullable', 'string', 'max:120'],
            'seo_description.id' => ['nullable', 'string', 'max:300'],
            'seo_description.en' => ['nullable', 'string', 'max:300'],
        ], [
            'slug.regex'  => 'Slug hanya boleh huruf kecil, angka, dan tanda hubung (contoh: jaringan-kantor-dinas).',
            'slug.not_in' => 'Slug ini dicadangkan sistem. Pakai slug lain.',
            'slug.unique' => 'Slug ini sudah dipakai proyek lain.',
        ], [
            'title.id'       => 'judul (ID)', 'title.en'       => 'judul (EN)',
            'summary.id'     => 'ringkasan (ID)', 'summary.en' => 'ringkasan (EN)',
            'description.id' => 'deskripsi (ID)', 'description.en' => 'deskripsi (EN)',
            'scope.id'       => 'lingkup kerja (ID)', 'scope.en' => 'lingkup kerja (EN)',
            'pillar_id'      => 'pilar bisnis', 'brand_id' => 'brand', 'client' => 'klien', 'year' => 'tahun',
            'translation_status' => 'status terjemahan', 'image' => 'gambar utama',
        ]);

        // Brand harus milik pilar yang dipilih.
        if (! empty($data['brand_id']) && ! Brand::where('id', $data['brand_id'])->where('pillar_id', $data['pillar_id'])->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['brand_id' => 'Brand ini bukan milik pilar yang dipilih.']);
        }

        $clean = fn(array $pair) => ['id' => trim((string) ($pair['id'] ?? '')), 'en' => trim((string) ($pair['en'] ?? ''))];

        // Terjemahan otomatis terjadi pada simpanan ini: status menjadi Otomatis (belum ditinjau).
        return $this->translationAttributes() + [
            'slug'               => $data['slug'],
            'pillar_id'          => $data['pillar_id'],
            'brand_id'           => $data['brand_id'] ?? null,
            'title'              => $clean($data['title']),
            'client'             => trim($data['client']),
            'year'               => (int) $data['year'],
            'summary'            => $clean($data['summary']),
            'description'        => $clean($data['description']),
            'scope'              => ['id' => $this->lines($data['scope']['id'] ?? ''), 'en' => $this->lines($data['scope']['en'] ?? '')],
            'image_alt'          => $clean($data['image_alt'] ?? []),
            // Galeri: hanya path Media Library yang sah dan berkasnya ada; urutan input dipertahankan, tanpa duplikat.
            'gallery'            => collect((array) ($data['gallery'] ?? []))->map(fn($p) => MediaStore::pick($p))->filter()->unique()->take(self::GALLERY_MAX)->values()->all(),
            'is_visible'         => (bool) $data['is_visible'],
            'is_featured'        => (bool) $data['is_featured'],
            'translation_status' => $data['translation_status'],
            'seo_title'          => $clean($data['seo_title'] ?? []),
            'seo_description'    => $clean($data['seo_description'] ?? []),
        ];
    }

    /** Satu baris = satu butir lingkup kerja (maks. 12 butir, 150 karakter per butir). */
    protected function lines(?string $text): array
    {
        $items = preg_split('/\R+/u', (string) $text) ?: [];
        $items = array_map(fn($line) => Str::limit(trim((string) preg_replace('/^[\s\-\*•]+/u', '', $line)), 150, ''), $items);

        return array_slice(array_values(array_filter($items, 'strlen')), 0, 12);
    }

    protected function visibilityNote(Project $project): string
    {
        return $project->is_visible ? 'Proyek tampil di situs.' : 'Proyek disembunyikan, belum tampil di situs.';
    }

    protected function featuredNote(): string
    {
        $count = Project::where('is_visible', true)->where('is_featured', true)->count();

        return $count > Project::HOME_LIMIT
            ? " Perhatian: ada {$count} proyek unggulan, beranda hanya menampilkan " . Project::HOME_LIMIT . ' yang terbaru.'
            : '';
    }
}