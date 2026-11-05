<?php
namespace App\Support;

use App\Models\Project;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * B3g: sumber data proyek untuk halaman publik. Mengembalikan array dengan BENTUK YANG SAMA seperti
 * data dummy lama (slug, unit, unit_name, title, client, year, image, summary, description, scope),
 * sehingga view tidak perlu ditulis ulang. Kolom EN yang kosong otomatis memakai Bahasa Indonesia.
 *
 * Bila tabel belum ada (migrasi belum dijalankan), available() bernilai false dan
 * App\Support\Content memakai data dummy bawaan kode agar situs tidak error.
 */
class ProjectRepo
{
    protected static ?bool $available = null;

    public static function available(): bool
    {
        if (self::$available === null) {
            try {
                self::$available = Schema::hasTable('projects') && Schema::hasTable('pillars');
            } catch (Throwable) {
                self::$available = false;
            }
        }

        return self::$available;
    }

    /** Semua proyek yang tampil, tahun terbaru dulu. */
    public static function all(): array
    {
        return Project::query()->visible()->with(['pillar', 'brand'])
            ->orderByDesc('year')->orderByDesc('id')
            ->get()
            ->map(fn(Project $p) => self::shape($p))
            ->all();
    }

    /**
     * Proyek unggulan untuk beranda: yang dicentang (maks. HOME_LIMIT); bila kurang dari itu,
     * dilengkapi proyek terbaru agar baris kartu tetap penuh.
     */
    public static function featured(): array
    {
        $limit = Project::HOME_LIMIT;

        $picked = Project::query()->visible()->where('is_featured', true)->with(['pillar', 'brand'])
            ->orderByDesc('year')->orderByDesc('id')->limit($limit)->get();

        if ($picked->count() < $limit) {
            $fill = Project::query()->visible()->whereNotIn('id', $picked->modelKeys())->with(['pillar', 'brand'])
                ->orderByDesc('year')->orderByDesc('id')->limit($limit - $picked->count())->get();
            $picked = $picked->concat($fill);
        }

        return $picked->map(fn(Project $p) => self::shape($p))->values()->all();
    }

    protected static function shape(Project $p): array
    {
        $title = $p->t('title');

        return [
            'slug'        => $p->slug,
            // B5: isi bahasa ini hasil mesin dan belum ditinjau.
            'machine_translated' => $p->machineTranslated(app()->getLocale()),
            'unit'        => $p->pillar?->slug ?? '',
            'unit_name'   => $p->pillar?->name ?? '',
            'brand'       => $p->brand?->name,
            'title'       => $title,
            'client'      => (string) $p->client,
            'year'        => $p->year,
            'image'       => $p->image,
            'image_alt'   => $p->t('image_alt') ?: $title,
            'gallery'     => $p->galleryImages(), // B3g v79: foto galeri dari Media Library
            'summary'     => $p->t('summary'),
            'description' => $p->t('description'),
            'scope'       => $p->workItems(),
            'featured'    => $p->is_featured,
            'seo_title'   => $p->t('seo_title'),
            'seo_description' => $p->t('seo_description'),
        ];
    }
}