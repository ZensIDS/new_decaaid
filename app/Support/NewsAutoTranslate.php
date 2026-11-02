<?php
namespace App\Support;

use App\Models\News;
use RuntimeException;

/**
 * B5: isi otomatis bahasa yang kosong pada berita (ID <-> EN).
 *
 * Aturan per kolom (judul, ringkasan, isi, alt gambar, judul SEO, deskripsi SEO):
 *  - ID terisi, EN kosong  -> EN diterjemahkan dari ID
 *  - EN terisi, ID kosong  -> ID diterjemahkan dari EN
 *  - dua-duanya terisi     -> tidak diubah, KECUALI status terjemahan masih "Otomatis" dan admin
 *                             hanya mengubah sisi sumber (sisi hasil mesin dibiarkan): sisi hasil
 *                             diterjemahkan ulang agar tidak basi. Status "Manual" dan "Ditinjau"
 *                             tidak pernah ditimpa.
 */
class NewsAutoTranslate
{
    /** kolom => [maksimal karakter (null = HTML), HTML?] */
    protected const FIELDS = [
        'title'           => [200, false],
        'excerpt'         => [400, false],
        'body'            => [null, true],
        'image_alt'       => [200, false],
        'seo_title'       => [120, false],
        'seo_description' => [300, false],
    ];

    /**
     * @param  array<string,array{id?:?string,en?:?string}>  $input  nilai kiriman form per kolom
     * @return array{0:array,1:?string,2:?string}  [$input baru, arah ('id'|'en'|null), peringatan]
     */
    public static function fill(array $input, ?News $news): array
    {
        $jobs = ['en' => [], 'id' => []]; // target => [kolom => teks sumber]

        foreach (self::FIELDS as $field => [$max, $html]) {
            $id = self::norm($input[$field]['id'] ?? '', $html);
            $en = self::norm($input[$field]['en'] ?? '', $html);

            if ($id !== '' && $en === '') {
                $jobs['en'][$field] = $id;
            } elseif ($en !== '' && $id === '') {
                $jobs['id'][$field] = $en;
            } elseif ($id !== '' && $en !== '' && $news && $news->translation_status === 'auto' && $news->translated_from) {
                $oldId = self::norm($news->{$field}['id'] ?? '', $html);
                $oldEn = self::norm($news->{$field}['en'] ?? '', $html);

                if ($news->translated_from === 'id' && $id !== $oldId && $en === $oldEn) {
                    $jobs['en'][$field] = $id;
                } elseif ($news->translated_from === 'en' && $en !== $oldEn && $id === $oldId) {
                    $jobs['id'][$field] = $en;
                }
            }
        }

        $direction = null;
        $warning   = null;

        foreach (['en' => 'id', 'id' => 'en'] as $to => $from) {
            if (! $jobs[$to]) {
                continue;
            }

            try {
                $items = [];
                foreach ($jobs[$to] as $field => $text) {
                    $items[$field] = ['text' => $text, 'html' => self::FIELDS[$field][1]];
                }

                $done = Translator::translate($items, $from, $to);

                foreach ($done as $field => $text) {
                    $max = self::FIELDS[$field][0];
                    if ($max && mb_strlen($text) > $max) {
                        $text = rtrim(mb_substr($text, 0, $max - 1)) . '…';
                    }
                    $input[$field][$to] = $text;
                }

                $direction = $from;
            } catch (RuntimeException $e) {
                $label   = $to === 'en' ? 'Inggris (EN)' : 'Indonesia (ID)';
                $warning = "Terjemahan otomatis ke {$label} gagal: " . $e->getMessage();

                if ($to === 'id') {
                    // Isi ID wajib: tanpa terjemahan, berita tidak bisa disimpan.
                    throw new RuntimeException($warning . ' Isi Bahasa Indonesia secara manual, lalu simpan lagi.');
                }
            }
        }

        return [$input, $direction, $warning];
    }

    protected static function norm(?string $value, bool $html): string
    {
        $value = trim((string) $value);

        return $html ? (Html::text(Html::clean($value)) === '' && ! str_contains($value, '<img') ? '' : Html::clean($value)) : $value;
    }
}