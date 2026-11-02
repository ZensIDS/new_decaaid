<?php
namespace App\Support;

use App\Models\Pillar;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * B3c/B4: sumber data Pilar Bisnis dan Brand untuk halaman publik.
 *
 * Mengembalikan array dengan BENTUK YANG SAMA seperti App\Support\Pillars::all()
 * (slug, name, icon, brands[slug, name, logo, description, instagram, tiktok, url, services]),
 * sehingga view tidak perlu diubah. Nilai dwibahasa dikembalikan ['id' => .., 'en' => ..]
 * dan di-resolve oleh Content::localize(). Kolom EN yang kosong otomatis memakai teks ID.
 * Pilar dan brand yang disembunyikan tidak ikut.
 *
 * Bila tabel belum ada atau masih kosong (migrasi/impor belum dijalankan), available()
 * bernilai false dan App\Support\Content memakai data bawaan kode (Pillars::all()),
 * sehingga situs tidak pernah kosong.
 */
class PillarRepo
{
    public const DEFAULT_SERVICES_TITLE = [
        'id' => 'Fokus dan Layanan Utama',
        'en' => 'Main Focus and Services',
    ];

    protected static ?bool $available = null;

    /** @var array<int, array>|null cache per permintaan */
    protected static ?array $memo = null;

    public static function available(): bool
    {
        if (self::$available === null) {
            try {
                self::$available = Schema::hasTable('pillars')
                    && Schema::hasTable('brands')
                    && Pillar::query()->exists();
            } catch (Throwable) {
                self::$available = false;
            }
        }

        return self::$available;
    }

    /** Semua pilar yang tampil beserta brand yang tampil, berurutan. */
    public static function all(): array
    {
        if (self::$memo !== null) {
            return self::$memo;
        }

        $pillars = Pillar::query()
            ->where('is_visible', true)
            ->with(['brands' => fn($q) => $q->where('is_visible', true)])
            ->orderBy('sort_order')->orderBy('id')
            ->get();

        return self::$memo = $pillars->map(fn(Pillar $p) => [
            'slug'   => $p->slug,
            'name'   => $p->name,
            'icon'   => $p->icon,
            'brands' => $p->brands->map(fn($b) => self::brand($b))->all(),
        ])->all();
    }

    protected static function brand($b): array
    {
        $row = [
            'slug'        => $b->slug,
            'name'        => $b->name,
            'logo'        => $b->logo ?: null,
            'description' => self::bilingual($b->description),
            'instagram'   => $b->instagram ?: null,
            'tiktok'      => $b->tiktok ?: null,
            'url'         => $b->url ?: null,
        ];

        $items = [];
        foreach ($b->services['items'] ?? [] as $item) {
            $title = self::bilingual($item['title'] ?? null);
            if ($title === null) {
                continue;
            }
            $items[] = ['title' => $title, 'text' => self::bilingual($item['text'] ?? null) ?? ['id' => '', 'en' => '']];
        }

        if ($items) {
            $row['services'] = [
                'title' => self::bilingual($b->services['title'] ?? null) ?? self::DEFAULT_SERVICES_TITLE,
                'items' => $items,
            ];
        }

        return $row;
    }

    /** ['id' => x, 'en' => y] dengan EN jatuh ke ID bila kosong; null bila ID kosong. */
    protected static function bilingual(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $id = trim((string) ($value['id'] ?? ''));
        $en = trim((string) ($value['en'] ?? ''));

        if ($id === '') {
            return null;
        }

        return ['id' => $id, 'en' => $en !== '' ? $en : $id];
    }
}