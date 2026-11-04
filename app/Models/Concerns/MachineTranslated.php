<?php
namespace App\Models\Concerns;

use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * B5: bantuan model yang punya kolom 'title' dwibahasa + 'translation_status' + 'translated_from'.
 * Aman dipakai sebelum `php artisan migrate`: tanpa kolom 'translated_from', fitur dianggap belum ada.
 */
trait MachineTranslated
{
    protected static ?bool $hasTranslatedFrom = null;

    public static function hasTranslatedFrom(): bool
    {
        if (self::$hasTranslatedFrom === null) {
            try {
                self::$hasTranslatedFrom = Schema::hasColumn((new static)->getTable(), 'translated_from');
            } catch (Throwable) {
                self::$hasTranslatedFrom = false;
            }
        }

        return self::$hasTranslatedFrom;
    }

    /** Isi bahasa $locale hasil mesin dan belum ditinjau (tampil sebagai catatan di halaman publik). */
    public function machineTranslated(string $locale): bool
    {
        return $this->translation_status === 'auto'
            && filled($this->translated_from)
            && $this->translated_from !== $locale
            && filled(trim(strip_tags((string) ($this->title[$locale] ?? ''))));
    }
}
