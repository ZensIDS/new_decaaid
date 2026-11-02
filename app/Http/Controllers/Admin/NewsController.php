<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsRevision;
use App\Support\Html;
use App\Support\ImageStore;
use App\Support\NewsAutoTranslate;
use App\Support\Translator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

/**
 * B3d: modul Berita. Super Admin, Admin, dan Editor boleh menulis dan mengubah;
 * hanya Super Admin dan Admin yang boleh menghapus (lihat routes/admin.php).
 */
class NewsController extends Controller
{
    protected const PER_PAGE = 10;

    /** Diisi validated(): arah terjemahan otomatis ('id'|'en') dan peringatan bila gagal. */
    protected ?string $translatedFrom = null;
    protected ?string $translateWarning = null;

    public function index(Request $request): View
    {
        $q        = trim((string) $request->query('q'));
        $status   = $request->query('status');
        $category = $request->query('kategori');

        $items = News::query()->with('category')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $query->where(fn($w) => $w->where('title', 'like', $like)->orWhere('slug', 'like', $like));
            })
            ->when(array_key_exists((string) $status, News::STATUSES), fn($query) => $query->where('status', $status))
            ->when($category, fn($query) => $query->where('news_category_id', $category))
            ->orderByDesc('published_at')->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('pages.admin.news.index', [
            'items'      => $items,
            'categories' => NewsCategory::orderBy('sort_order')->orderBy('id')->get(),
            'q'          => $q,
            'status'     => $status,
            'category'   => $category,
            'filtered'   => $q !== '' || $status || $category,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.news.form', [
            'news'       => null,
            'categories' => $this->categories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);

        $news             = new News($data);
        $news->created_by = $request->user()->id;
        $news->updated_by = $request->user()->id;

        if ($request->hasFile('image')) {
            $news->image = ImageStore::store($request->file('image'), 'news');
        }

        $news->save();

        ActivityLog::record('news.created', "menambah berita \"{$news->t('title', 'id')}\" ({$news->statusLabel()})", $news);

        return redirect()->route('admin.news.edit', $news)
            ->with('status', 'Berita dibuat. ' . $this->translationNote() . $this->visibilityNote($news))
            ->with('error', $this->translateWarning);
    }

    public function edit(News $news): View
    {
        return view('pages.admin.news.form', [
            'news'       => $news->load(['category', 'author']),
            'categories' => $this->categories(),
            'revisions'  => $news->revisions()->limit(NewsRevision::KEEP)->get(),
        ]);
    }

    public function update(Request $request, News $news): RedirectResponse
    {
        $data = $this->validated($request, $news);

        $this->remember($news, $request->user()->id, $request->user()->name);

        $news->fill($data);
        $news->updated_by = $request->user()->id;
        $news->is_dummy   = false; // sudah disentuh admin: tidak ikut tertimpa --refresh

        if ($request->hasFile('image')) {
            $news->image = ImageStore::store($request->file('image'), 'news');
        } elseif ($request->boolean('remove_image')) {
            $news->image = null;
        }

        $news->save();

        ActivityLog::record('news.updated', "mengubah berita \"{$news->t('title', 'id')}\" ({$news->statusLabel()})", $news);

        return redirect()->route('admin.news.edit', $news)
            ->with('status', 'Perubahan disimpan. ' . $this->translationNote() . $this->visibilityNote($news))
            ->with('error', $this->translateWarning);
    }

    public function destroy(News $news): RedirectResponse
    {
        $title = $news->t('title', 'id');

        $paths = collect([$news->image])
            ->merge($news->revisions()->get()->pluck('snapshot.image'))
            ->filter(fn($p) => $p && str_starts_with($p, 'storage/'))
            ->unique();

        $news->delete();

        // Hapus berkas unggahan hanya bila tidak dipakai berita lain.
        foreach ($paths as $path) {
            if (! News::where('image', $path)->exists()) {
                ImageStore::delete($path);
            }
        }

        ActivityLog::record('news.deleted', "menghapus berita \"{$title}\"");

        return redirect()->route('admin.news.index')->with('status', "Berita \"{$title}\" dihapus.");
    }

    /** Kembalikan isi ke salah satu revisi lama (status dan slug tidak diubah). */
    public function restore(Request $request, News $news, NewsRevision $revision): RedirectResponse
    {
        abort_unless($revision->news_id === $news->id, 404);

        $this->remember($news, $request->user()->id, $request->user()->name);

        $snap = $revision->snapshot;
        $news->fill(collect($snap)->only(['news_category_id', 'title', 'excerpt', 'body', 'image_alt', 'seo_title', 'seo_description'])->all());

        $image = $snap['image'] ?? null;
        if ($image === null || ! str_starts_with($image, 'storage/') || Storage::disk('public')->exists(substr($image, 8))) {
            $news->image = $image;
        }

        $news->updated_by = $request->user()->id;
        $news->save();

        ActivityLog::record('news.restored', "mengembalikan berita \"{$news->t('title', 'id')}\" ke versi {$revision->created_at->translatedFormat('j M Y, H:i')}", $news);

        return redirect()->route('admin.news.edit', $news)
            ->with('status', 'Isi dikembalikan ke versi yang dipilih. Versi sebelumnya tetap tersimpan di Riwayat Revisi.');
    }

    /** Unggah gambar dari editor teks kaya. */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:' . ImageStore::MAX_KB],
        ], [], ['image' => 'gambar']);

        return response()->json(['path' => ImageStore::store($request->file('image'), 'news/body')]);
    }

    /* ------------------------------------------------------------------ */

    protected function categories()
    {
        return NewsCategory::orderBy('sort_order')->orderBy('id')->get();
    }

    protected function validated(Request $request, ?News $news): array
    {
        $this->autoTranslate($request, $news);

        // Slug: dari isian, atau dibuat dari judul Indonesia dan dijamin unik.
        $given     = trim((string) $request->input('slug'));
        $generated = $given === '';
        $slug      = Str::limit(Str::slug($generated ? (string) $request->input('title.id') : $given), 100, '');

        if ($generated && $slug !== '') {
            $base = $slug;
            $n    = 2;
            while (News::where('slug', $slug)->when($news, fn($q) => $q->whereKeyNot($news->id))->exists()
                || in_array($slug, News::RESERVED_SLUGS, true)) {
                $slug = $base . '-' . $n++;
            }
        }

        $request->merge(['slug' => $slug]);

        $data = $request->validate([
            'title.id'           => ['required', 'string', 'max:200'],
            'title.en'           => ['nullable', 'string', 'max:200'],
            'excerpt.id'         => ['required', 'string', 'max:400'],
            'excerpt.en'         => ['nullable', 'string', 'max:400'],
            'body.id'            => ['required', 'string', 'max:200000'],
            'body.en'            => ['nullable', 'string', 'max:200000'],
            'news_category_id'   => ['required', 'exists:news_categories,id'],
            'slug'               => [
                'required', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::notIn(News::RESERVED_SLUGS),
                Rule::unique('news', 'slug')->ignore($news?->id),
            ],
            'status'             => ['required', Rule::in(array_keys(News::STATUSES))],
            'published_at'       => ['nullable', 'date', 'required_if:status,' . News::STATUS_SCHEDULED],
            'translation_status' => ['required', Rule::in(array_keys(News::TRANSLATION))],
            'image'              => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:' . ImageStore::MAX_KB],
            'image_alt.id'       => ['nullable', 'string', 'max:200'],
            'image_alt.en'       => ['nullable', 'string', 'max:200'],
            'seo_title.id'       => ['nullable', 'string', 'max:120'],
            'seo_title.en'       => ['nullable', 'string', 'max:120'],
            'seo_description.id' => ['nullable', 'string', 'max:300'],
            'seo_description.en' => ['nullable', 'string', 'max:300'],
        ], [
            'slug.regex'               => 'Slug hanya boleh huruf kecil, angka, dan tanda hubung (contoh: kunjungan-sekolah-mitra).',
            'slug.not_in'              => 'Slug ini dicadangkan sistem. Pakai slug lain.',
            'slug.unique'              => 'Slug ini sudah dipakai berita lain.',
            'published_at.required_if' => 'Isi tanggal dan jam tayang untuk berita Terjadwal.',
        ], [
            'title.id'           => 'judul (ID)', 'title.en'       => 'judul (EN)',
            'excerpt.id'         => 'ringkasan (ID)', 'excerpt.en' => 'ringkasan (EN)',
            'body.id'            => 'isi berita (ID)', 'body.en'   => 'isi berita (EN)',
            'news_category_id'   => 'kategori', 'published_at'     => 'waktu tayang',
            'translation_status' => 'status terjemahan', 'image'   => 'gambar utama',
        ]);

        // Saring isi editor, lalu pastikan versi Indonesia tidak kosong.
        $body = ['id' => Html::clean($data['body']['id']), 'en' => Html::clean($data['body']['en'] ?? '')];

        if ($body['id'] === '' || Html::text($body['id']) === '' && ! str_contains($body['id'], '<img')) {
            throw ValidationException::withMessages(['body.id' => 'Isi berita (ID) wajib diisi.']);
        }

        // Status Terbit tanpa tanggal = tayang sekarang. Draf menyimpan tanggal bila ada.
        $publishedAt = $data['published_at'] ?? null;
        if ($data['status'] === News::STATUS_PUBLISHED && ! $publishedAt) {
            $publishedAt = $news?->published_at ?? now();
        }

        // Terjemahan otomatis terjadi pada simpanan ini: tandai sebagai belum ditinjau.
        $auto = $this->translatedFrom
            ? ['translation_status' => 'auto', 'translated_from' => $this->translatedFrom]
            : [];

        $clean = fn(array $pair) => ['id' => trim((string) ($pair['id'] ?? '')), 'en' => trim((string) ($pair['en'] ?? ''))];

        return $auto + [
            'slug'               => $data['slug'],
            'news_category_id'   => $data['news_category_id'],
            'title'              => $clean($data['title']),
            'excerpt'            => $clean($data['excerpt']),
            'body'               => $body,
            'image_alt'          => $clean($data['image_alt'] ?? []),
            'status'             => $data['status'],
            'published_at'       => $publishedAt,
            'translation_status' => $data['translation_status'],
            'seo_title'          => $clean($data['seo_title'] ?? []),
            'seo_description'    => $clean($data['seo_description'] ?? []),
        ];
    }

    /**
     * Isi bahasa yang kosong secara otomatis (ID <-> EN) sebelum validasi.
     * Dilewati bila saklar dimatikan atau penyedia terjemahan belum diatur.
     */
    protected function autoTranslate(Request $request, ?News $news): void
    {
        $this->translatedFrom   = null;
        $this->translateWarning = null;

        if (! $request->boolean('auto_translate') || ! Translator::enabled()) {
            return;
        }

        $input = [];
        foreach (News::LOCALIZED as $field) {
            $input[$field] = ['id' => $request->input("{$field}.id"), 'en' => $request->input("{$field}.en")];
        }

        try {
            [$filled, $direction, $warning] = NewsAutoTranslate::fill($input, $news);
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

    /** Simpan kondisi sekarang ke Riwayat Revisi sebelum diubah; simpan paling banyak KEEP terakhir. */
    protected function remember(News $news, int $userId, string $userName): void
    {
        $news->revisions()->create([
            'user_id'   => $userId,
            'user_name' => $userName,
            'snapshot'  => $news->snapshot(),
        ]);

        $old = $news->revisions()->skip(NewsRevision::KEEP)->take(100)->pluck('id');
        if ($old->isNotEmpty()) {
            NewsRevision::whereIn('id', $old)->delete();
        }
    }

    protected function visibilityNote(News $news): string
    {
        return match (true) {
            $news->status === News::STATUS_DRAFT => 'Statusnya Draf, belum tampil di situs.',
            $news->isVisible()                   => 'Berita sudah tampil di situs.',
            default                              => 'Akan tampil di situs pada ' . $news->published_at->translatedFormat('j F Y, H:i') . '.',
        };
    }
}