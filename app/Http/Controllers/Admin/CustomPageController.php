<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\MediaStore;
use App\Support\SlugRedirects;
use App\Models\CustomPage;
use App\Models\CustomPageRevision;
use App\Support\CustomPageRepo;
use App\Support\Html;
use App\Support\NewsAutoTranslate;
use App\Support\Translator;
use RuntimeException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * B3m (QA1): Halaman Kustom. Daftar + form dengan tab ID | EN (judul, isi editor teks kaya), slug, status
 * Draf/Terbit, centang tampil di footer, dan SEO. Super Admin dan Admin (Editor tanpa akses).
 */
class CustomPageController extends Controller
{
    protected const PER_PAGE = 10;

    protected ?string $translatedFrom = null;
    protected ?string $translateWarning = null;

    public function index(Request $request): View
    {
        $q      = trim((string) $request->query('q'));
        $status = $request->query('status');

        $items = collect();
        $paginator = null;

        if (CustomPageRepo::available()) {
            $paginator = CustomPage::query()
                ->when($q !== '', function ($query) use ($q) {
                    $like = '%' . addcslashes($q, '%_\\') . '%';
                    $query->where(fn($w) => $w->where('title', 'like', $like)->orWhere('slug', 'like', $like));
                })
                ->when(in_array($status, ['published', 'draft'], true), fn($query) => $query->where('is_published', $status === 'published'))
                ->orderByDesc('updated_at')->orderByDesc('id')
                ->paginate(self::PER_PAGE)
                ->withQueryString();
        }

        return view('pages.admin.custom-pages.index', [
            'items'     => $paginator,
            'available' => CustomPageRepo::available(),
            'q'         => $q,
            'status'    => $status,
            'filtered'  => $q !== '' || in_array($status, ['published', 'draft'], true),
        ]);
    }

    public function create(): View
    {
        $this->ensureTable();

        return view('pages.admin.custom-pages.form', ['page' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureTable();

        $this->autoTranslate($request, null);

        $page = new CustomPage($this->validated($request, null));
        $page->created_by = $request->user()->id;
        $page->updated_by = $request->user()->id;
        $page->save();

        ActivityLog::record('custompage.created', "menambah halaman kustom \"{$page->t('title', 'id')}\" (/{$page->slug})", $page);

        return redirect()->route('admin.custompages.edit', $page)
            ->with('status', 'Halaman dibuat. ' . $this->translationNote() . $this->visibilityNote($page))
            ->with('error', $this->translateWarning);
    }

    public function edit(CustomPage $page): View
    {
        return view('pages.admin.custom-pages.form', [
            'page'      => $page->load(['author', 'editor']),
            'revisions' => CustomPageRevision::available() ? $page->revisions()->limit(CustomPageRevision::KEEP)->get() : collect(),
        ]);
    }

    public function update(Request $request, CustomPage $page): RedirectResponse
    {
        $oldSlug = $page->slug;
        $this->autoTranslate($request, $page);
        $data    = $this->validated($request, $page);

        $this->remember($page, $request->user()->id, $request->user()->name);

        $page->fill($data);
        $page->updated_by = $request->user()->id;
        $page->save();

        ActivityLog::record('custompage.updated', "mengubah halaman kustom \"{$page->t('title', 'id')}\" (/{$page->slug})", $page);

        // B3n: slug berubah pada halaman yang terbit -> Redirect 301 otomatis (ID dan EN).
        $redirected = $oldSlug !== $page->slug && $page->is_published
            ? SlugRedirects::changed('custom', ['slug' => $oldSlug], ['slug' => $page->slug], 'slug halaman berubah')
            : 0;

        $note = $oldSlug !== $page->slug && $page->is_published
            ? ' Alamat berubah dari /' . $oldSlug . ' menjadi /' . $page->slug . '.' . SlugRedirects::note($redirected)
            : '';

        return redirect()->route('admin.custompages.edit', $page)
            ->with('status', 'Perubahan disimpan. ' . $this->translationNote() . $this->visibilityNote($page) . $note)
            ->with('error', $this->translateWarning);
    }

    /** Tombol cepat Terbit / Draf dari daftar. */
    public function toggle(CustomPage $page): RedirectResponse
    {
        $page->is_published = ! $page->is_published;
        $page->updated_by   = auth()->id();
        $page->save();

        ActivityLog::record(
            'custompage.toggled',
            ($page->is_published ? 'menerbitkan' : 'mengembalikan ke draf') . " halaman kustom \"{$page->t('title', 'id')}\"",
            $page
        );

        return back()->with('status', $page->is_published
            ? "Halaman \"{$page->t('title', 'id')}\" diterbitkan."
            : "Halaman \"{$page->t('title', 'id')}\" dikembalikan ke Draf dan tidak tampil di situs.");
    }

    /** Kembalikan judul, isi, dan SEO ke salah satu revisi lama (slug, status, dan footer tidak diubah). */
    public function restore(Request $request, CustomPage $page, CustomPageRevision $revision): RedirectResponse
    {
        abort_unless($revision->custom_page_id === $page->id, 404);

        $this->remember($page, $request->user()->id, $request->user()->name);

        $page->fill(collect($revision->snapshot)->only(CustomPageRevision::RESTORABLE)->all());
        $page->updated_by = $request->user()->id;
        $page->save();

        ActivityLog::record('custompage.restored', "mengembalikan halaman kustom \"{$page->t('title', 'id')}\" ke versi {$revision->created_at->translatedFormat('j M Y, H:i')}", $page);

        return redirect()->route('admin.custompages.edit', $page)
            ->with('status', 'Isi dikembalikan ke versi yang dipilih. Versi sebelumnya tetap tersimpan di Riwayat Revisi.');
    }

    public function destroy(CustomPage $page): RedirectResponse
    {
        $title = $page->t('title', 'id');
        $slug  = $page->slug;

        $page->delete();

        ActivityLog::record('custompage.deleted', "menghapus halaman kustom \"{$title}\" (/{$slug})");

        return redirect()->route('admin.custompages.index')->with('status', "Halaman \"{$title}\" dihapus.");
    }

    /* ------------------------------------------------------------------ */

    /** Simpan kondisi sekarang ke Riwayat Revisi sebelum diubah; simpan paling banyak KEEP terakhir. */
    protected function remember(CustomPage $page, int $userId, string $userName): void
    {
        if (! CustomPageRevision::available()) {
            return;
        }

        $page->revisions()->create([
            'user_id'   => $userId,
            'user_name' => $userName,
            'snapshot'  => $page->snapshot(),
        ]);

        $old = $page->revisions()->skip(CustomPageRevision::KEEP)->take(100)->pluck('id');

        if ($old->isNotEmpty()) {
            CustomPageRevision::whereIn('id', $old)->delete();
        }
    }

    /**
     * Isi bahasa yang kosong secara otomatis (ID <-> EN) sebelum validasi.
     * Dilewati bila saklar dimatikan, penyedia terjemahan belum diatur, atau migration belum dijalankan.
     */
    protected function autoTranslate(Request $request, ?CustomPage $page): void
    {
        $this->translatedFrom   = null;
        $this->translateWarning = null;

        if (! $request->boolean('auto_translate') || ! Translator::enabled() || ! CustomPage::hasTranslationColumns()) {
            return;
        }

        $input = [];
        foreach (array_keys(CustomPage::AUTO_FIELDS) as $field) {
            $input[$field] = ['id' => $request->input("{$field}.id"), 'en' => $request->input("{$field}.en")];
        }

        try {
            [$filled, $direction, $warning] = NewsAutoTranslate::fill($input, $page, CustomPage::AUTO_FIELDS);
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['title.id' => $e->getMessage()]);
        }

        foreach ($filled as $field => $pair) {
            $request->merge([$field => ['id' => $pair['id'] ?? null, 'en' => $pair['en'] ?? null]]);
        }

        $this->translatedFrom   = $direction;
        $this->translateWarning = $warning;
    }

    protected function translationNote(): string
    {
        return $this->translatedFrom
            ? 'Terjemahan ' . ($this->translatedFrom === 'id' ? 'Inggris' : 'Indonesia') . ' diisi otomatis (status: belum ditinjau). '
            : '';
    }

    protected function ensureTable(): void
    {
        if (! CustomPageRepo::available()) {
            throw ValidationException::withMessages(['title.id' => 'Tabel belum ada. Jalankan dulu: php artisan migrate']);
        }
    }

    protected function validated(Request $request, ?CustomPage $page): array
    {
        $reserved = CustomPage::reservedSlugs();

        // Slug: dari isian, atau dibuat dari judul Indonesia dan dijamin unik.
        $given     = trim((string) $request->input('slug'));
        $generated = $given === '';
        $slug      = Str::limit(Str::slug($generated ? (string) $request->input('title.id') : $given), 100, '');

        if ($generated && $slug !== '') {
            $base = $slug;
            $n    = 2;
            while (CustomPage::where('slug', $slug)->when($page, fn($q) => $q->whereKeyNot($page->id))->exists()
                || in_array($slug, $reserved, true)) {
                $slug = $base . '-' . $n++;
            }
        }

        $request->merge(['slug' => $slug]);

        $data = $request->validate([
            'title.id'           => ['required', 'string', 'max:150'],
            'title.en'           => ['nullable', 'string', 'max:150'],
            'body.id'            => ['required', 'string', 'max:200000'],
            'body.en'            => ['nullable', 'string', 'max:200000'],
            'slug'               => [
                'required', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::notIn($reserved),
                Rule::unique('custom_pages', 'slug')->ignore($page?->id),
            ],
            'is_published'       => ['required', 'boolean'],
            'show_in_footer'     => ['required', 'boolean'],
            'sort_order'         => ['nullable', 'integer', 'between:0,999'],
            'translation_status' => ['nullable', Rule::in(array_keys(CustomPage::TRANSLATION))],
            'seo_title.id'       => ['nullable', 'string', 'max:120'],
            'seo_title.en'       => ['nullable', 'string', 'max:120'],
            'seo_description.id' => ['nullable', 'string', 'max:300'],
            'seo_description.en' => ['nullable', 'string', 'max:300'],
            'seo_image'          => ['nullable', 'string', 'max:255'],
        ], [
            'slug.required'   => 'Slug wajib diisi (atau kosongkan agar dibuat dari judul, dengan judul yang berisi huruf atau angka).',
            'slug.regex'      => 'Slug hanya boleh huruf kecil, angka, dan tanda hubung (contoh: syarat-ketentuan).',
            'slug.not_in'     => 'Slug ini dicadangkan sistem (dipakai halaman bawaan). Pakai slug lain.',
            'slug.unique'     => 'Slug ini sudah dipakai halaman kustom lain.',
            'sort_order.between' => 'Urutan footer antara 0 dan 999.',
        ], [
            'title.id' => 'judul (ID)', 'title.en' => 'judul (EN)',
            'body.id'  => 'isi halaman (ID)', 'body.en' => 'isi halaman (EN)',
            'sort_order' => 'urutan footer', 'translation_status' => 'status terjemahan',
        ]);

        // Saring isi editor, lalu pastikan versi Indonesia tidak kosong.
        $body = ['id' => Html::clean($data['body']['id']), 'en' => Html::clean($data['body']['en'] ?? '')];

        if ($body['id'] === '' || (Html::text($body['id']) === '' && ! str_contains($body['id'], '<img'))) {
            throw ValidationException::withMessages(['body.id' => 'Isi halaman (ID) wajib diisi.']);
        }

        $clean = fn(array $pair) => ['id' => trim((string) ($pair['id'] ?? '')), 'en' => trim((string) ($pair['en'] ?? ''))];

        // Terjemahan otomatis terjadi pada simpanan ini: tandai belum ditinjau. Selain itu pakai pilihan form.
        $translation = [];

        if (CustomPage::hasTranslationColumns()) {
            $translation = $this->translatedFrom
                ? ['translation_status' => 'auto', 'translated_from' => $this->translatedFrom]
                : ['translation_status' => $data['translation_status'] ?? 'manual'];
        }

        // Gambar share dari Media Library (hanya path sah yang berkasnya ada); kolom baru ada setelah migrate.
        $seoImage = CustomPage::hasSeoImageColumn()
            ? ['seo_image' => MediaStore::pick($data['seo_image'] ?? null)]
            : [];

        return $translation + $seoImage + [
            'slug'            => $data['slug'],
            'title'           => $clean($data['title']),
            'body'            => $body,
            'seo_title'       => $clean($data['seo_title'] ?? []),
            'seo_description' => $clean($data['seo_description'] ?? []),
            'is_published'    => (bool) $data['is_published'],
            'show_in_footer'  => (bool) $data['show_in_footer'],
            'sort_order'      => (int) ($data['sort_order'] ?? 0),
        ];
    }

    protected function visibilityNote(CustomPage $page): string
    {
        if (! $page->is_published) {
            return 'Statusnya Draf, belum tampil di situs.';
        }

        return $page->show_in_footer
            ? 'Halaman sudah tampil di situs dan di footer.'
            : 'Halaman sudah tampil di situs (tidak di footer).';
    }
}
