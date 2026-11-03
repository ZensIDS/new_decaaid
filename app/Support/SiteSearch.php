<?php
namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * B4: bantuan pencarian di database untuk kolom dwibahasa (JSON {"id": ..., "en": ...}).
 * Mencocokkan teks pada bahasa aktif; untuk pengunjung EN, isi yang EN-nya kosong dicocokkan lewat teks Indonesia
 * (karena situs menampilkan teks Indonesia di sana). Karakter khusus LIKE (% _ \) di kata kunci di-escape.
 */
class SiteSearch
{
    public const MIN_LENGTH = 2;
    public const LIMIT = 20;

    /** Pola LIKE aman dari kata kunci. */
    public static function like(string $q): string
    {
        return '%' . addcslashes($q, '%_\\') . '%';
    }

    /**
     * Tambah syarat "kolom JSON $column mengandung $q" ke $query (AND dengan syarat lain di $query).
     * Beberapa kolom digabung OR lewat $columns.
     *
     * @param string[] $columns
     */
    public static function whereJsonLike(Builder $query, array $columns, string $q, ?string $locale = null): Builder
    {
        $locale ??= app()->getLocale();
        $like     = self::like($q);

        return $query->where(function (Builder $outer) use ($columns, $like, $locale) {
            foreach ($columns as $col) {
                $outer->orWhere(function (Builder $w) use ($col, $like, $locale) {
                    $w->where("{$col}->{$locale}", 'like', $like);

                    if ($locale !== 'id') {
                        // EN kosong -> situs menampilkan teks Indonesia, jadi cocokkan juga lewat ID.
                        $w->orWhere(function (Builder $fb) use ($col, $like, $locale) {
                            $fb->where(fn(Builder $e) => $e->whereNull("{$col}->{$locale}")->orWhere("{$col}->{$locale}", ''))
                               ->where("{$col}->id", 'like', $like);
                        });
                    }
                });
            }
        });
    }

    /** Cuplikan teks polos (maks. $max karakter), dipusatkan di sekitar kata kunci bila ada. */
    public static function snippet(string $html, string $q, int $max = 160): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', Html::text($html)));

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        $pos   = mb_stripos($text, $q);
        $start = $pos === false ? 0 : max(0, $pos - (int) ($max / 3));
        $out   = mb_substr($text, $start, $max);

        return ($start > 0 ? '…' : '') . trim($out) . '…';
    }
}
