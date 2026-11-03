<?php
namespace App\Support;

use App\Models\PageContent;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * B3j: membaca isi halaman tunggal dari tabel page_contents. Mengembalikan array mentah (pasangan id/en)
 * dengan isian EN yang kosong sudah diganti teks Indonesia, sehingga Content::localize() langsung bisa dipakai.
 * Bila tabel atau barisnya belum ada, get() mengembalikan null dan Content memakai teks contoh kode.
 */
class PageContentRepo
{
    protected static ?bool $available = null;

    /** @var array<string, ?array> */
    protected static array $cache = [];

    public static function available(): bool
    {
        if (self::$available === null) {
            try {
                self::$available = Schema::hasTable('page_contents');
            } catch (Throwable) {
                self::$available = false;
            }
        }

        return self::$available;
    }

    public static function row(string $page): ?PageContent
    {
        return self::available() ? PageContent::where('page', $page)->first() : null;
    }

    public static function get(string $page): ?array
    {
        if (! array_key_exists($page, self::$cache)) {
            $row = null;

            try {
                $row = self::row($page);
            } catch (Throwable) {
            }

            self::$cache[$page] = $row && is_array($row->content) ? self::fillEnglish($row->content) : null;
        }

        return self::$cache[$page];
    }

    /** Pasangan {id, en}: EN kosong memakai ID. Berlaku rekursif untuk daftar (repeater). */
    public static function fillEnglish(array $value): array
    {
        $keys = array_keys($value);
        sort($keys);

        if ($keys === ['en', 'id']) {
            $value['en'] = trim((string) $value['en']) === '' ? $value['id'] : $value['en'];

            return $value;
        }

        return array_map(fn($item) => is_array($item) ? self::fillEnglish($item) : $item, $value);
    }
}
