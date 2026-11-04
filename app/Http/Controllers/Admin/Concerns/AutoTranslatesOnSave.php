<?php
namespace App\Http\Controllers\Admin\Concerns;

use App\Support\NewsAutoTranslate;
use App\Support\Translator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * B5: terjemahan otomatis saat simpan, dipakai bersama oleh kontroler admin (Penghargaan, Proyek, ...).
 * Pemakai memanggil autoTranslateRequest() SEBELUM validasi, lalu menyisipkan translationAttributes()
 * ke data yang disimpan, dan translationNote() / translateWarning ke pesan.
 */
trait AutoTranslatesOnSave
{
    protected ?string $translatedFrom = null;
    protected ?string $translateWarning = null;

    /**
     * Isi bahasa yang kosong (ID <-> EN) dan gabungkan hasilnya ke $request. Dilewati bila saklar mati,
     * penyedia belum diatur, atau migration kolom 'translated_from' belum dijalankan.
     *
     * @param  array<string,array>  $fields  kolom => [maks karakter, HTML?, per baris?]
     * @param  class-string<Model>  $modelClass  harus memakai App\Models\Concerns\MachineTranslated
     */
    protected function autoTranslateRequest(Request $request, ?Model $record, array $fields, string $modelClass): void
    {
        $this->translatedFrom   = null;
        $this->translateWarning = null;

        if (! $request->boolean('auto_translate') || ! Translator::enabled() || ! $modelClass::hasTranslatedFrom()) {
            return;
        }

        $input = [];
        foreach (array_keys($fields) as $field) {
            $input[$field] = ['id' => $request->input("{$field}.id"), 'en' => $request->input("{$field}.en")];
        }

        try {
            [$filled, $direction, $warning] = NewsAutoTranslate::fill($input, $record, $fields);
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['title.id' => $e->getMessage()]);
        }

        foreach ($filled as $field => $pair) {
            $request->merge([$field => ['id' => $pair['id'] ?? null, 'en' => $pair['en'] ?? null]]);
        }

        $this->translatedFrom   = $direction;
        $this->translateWarning = $warning;
    }

    /** Terjemahan terjadi pada simpanan ini: tandai belum ditinjau dan catat bahasa sumbernya. */
    protected function translationAttributes(): array
    {
        return $this->translatedFrom
            ? ['translation_status' => 'auto', 'translated_from' => $this->translatedFrom]
            : [];
    }

    protected function translationNote(): string
    {
        return $this->translatedFrom
            ? 'Terjemahan ' . ($this->translatedFrom === 'id' ? 'Inggris' : 'Indonesia') . ' diisi otomatis (status: belum ditinjau). '
            : '';
    }
}
