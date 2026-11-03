<?php
namespace App\Support;

use App\Models\Award;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * B3i: sumber data penghargaan untuk halaman publik (Penghargaan dan carousel beranda).
 * Mengembalikan array dengan BENTUK YANG SAMA seperti data contoh lama (slug, year, featured, title, text, image),
 * sehingga view tidak perlu ditulis ulang. Kolom EN yang kosong otomatis memakai Bahasa Indonesia.
 *
 * Bila tabel belum ada (migrasi belum dijalankan), available() bernilai false dan
 * App\Support\Content memakai data contoh bawaan kode agar situs tidak error.
 */
class AwardRepo
{
    protected static ?bool $available = null;

    public static function available(): bool
    {
        if (self::$available === null) {
            try {
                self::$available = Schema::hasTable('awards');
            } catch (Throwable) {
                self::$available = false;
            }
        }

        return self::$available;
    }

    /** Semua penghargaan yang tampil: tahun terbaru dulu, lalu yang terakhir dibuat. */
    public static function all(): array
    {
        return Award::query()->visible()
            ->orderByDesc('year')->orderByDesc('id')
            ->get()
            ->map(fn(Award $a) => self::shape($a))
            ->all();
    }

    protected static function shape(Award $a): array
    {
        $title = $a->t('title');

        return [
            'slug'      => 'penghargaan-' . $a->id,
            'year'      => (string) $a->year,
            'featured'  => $a->is_featured,
            'title'     => $title,
            'text'      => $a->t('description'),
            'image'     => $a->image,
            'image_alt' => $a->t('image_alt') ?: $title,
        ];
    }
}
