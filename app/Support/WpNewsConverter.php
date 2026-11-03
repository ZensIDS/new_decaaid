<?php
namespace App\Support;

/**
 * Pengubah data berita WordPress -> format berita baru (tanpa dependensi Laravel agar mudah diuji).
 * Tidak ada terjemahan di sini: kolom EN sengaja dikosongkan, pengunjung EN melihat teks Indonesia.
 */
class WpNewsConverter
{
    /** Folder (relatif ke public/) tempat isi wp-content/uploads disalin apa adanya. */
    public const UPLOADS_DIR = 'wp-uploads';

    /** Urutan prioritas bila satu berita punya beberapa kategori WordPress. "terbaru" diabaikan. */
    public const CATEGORY_PRIORITY = ['training-research-centre', 'software-robotics', 'hardware-networking-services', 'event'];

    public const CATEGORY_NAMES = [
        'training-research-centre'     => 'Training & Research Centre',
        'software-robotics'            => 'Software & Robotics',
        'hardware-networking-services' => 'Hardware & Networking Services',
        'event'                        => 'Event',
    ];

    public static function title(string $title): string
    {
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $title));
    }

    /** Slug aman: huruf kecil, angka, tanda hubung; maks. 120 karakter; unik terhadap $taken. */
    public static function slug(string $slug, string $title, array $taken, array $reserved = []): string
    {
        $slug = mb_strtolower(rawurldecode($slug));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = trim(preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($title)), '-') ?: 'berita';
        }

        if (strlen($slug) > 120) {
            $slug = substr($slug, 0, 120);
            $slug = rtrim(($pos = strrpos($slug, '-')) !== false && $pos > 60 ? substr($slug, 0, $pos) : $slug, '-');
        }

        if (in_array($slug, $reserved, true)) {
            $slug .= '-berita';
        }

        $base = $slug;
        $i = 2;
        while (in_array($slug, $taken, true)) {
            $suffix = '-' . $i++;
            $slug = substr($base, 0, 120 - strlen($suffix)) . $suffix;
        }

        return $slug;
    }

    /** Isi WordPress -> HTML bersih. Alamat gambar diarahkan ke /wp-uploads/ (nama file tidak diubah). */
    public static function body(string $content): string
    {
        $html = str_replace(["\r\n", "\r"], "\n", $content);

        // Shortcode klasik: buang pembungkusnya, pertahankan isinya ([caption] berisi gambar).
        $html = preg_replace('#\[/?(?:caption|gallery|embed|audio|video|playlist)[^\]]*\]#i', '', $html);

        // Penulisan klasik WordPress tanpa <p>: baris kosong = paragraf, baris tunggal = <br>.
        if (! preg_match('#<(p|h[1-6]|ul|ol|blockquote|div|table)[\s>]#i', $html)) {
            $paras = preg_split('/\n{2,}/', trim($html));
            $html  = implode('', array_map(fn ($p) => '<p>' . nl2br(trim($p), false) . '</p>', array_filter($paras, 'strlen')));
        }

        // https://decaa.id/wp-content/uploads/2024/05/foto.jpg  ->  /wp-uploads/2024/05/foto.jpg
        $html = preg_replace(
            '#(?:https?:)?//(?:www\.)?decaa\.id/wp-content/uploads/#i',
            '/' . self::UPLOADS_DIR . '/',
            $html
        );

        return Html::clean($html);
    }

    /** WordPress tidak punya ringkasan di data ini; buat dari awal isi (maks. 400 karakter, berhenti di batas kata). */
    public static function excerpt(string $wpExcerpt, string $bodyHtml, int $max = 280): string
    {
        $text = trim($wpExcerpt) !== '' ? Html::text($wpExcerpt) : Html::text($bodyHtml);

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        $cut = mb_substr($text, 0, $max);
        $pos = mb_strrpos($cut, ' ');

        return rtrim(($pos !== false && $pos > $max * 0.6) ? mb_substr($cut, 0, $pos) : $cut, " ,.;:-") . '…';
    }

    /** Satu kategori untuk berita ini (kolom berita hanya menyimpan satu). */
    public static function category(array $wpSlugs): ?string
    {
        foreach (self::CATEGORY_PRIORITY as $slug) {
            if (in_array($slug, $wpSlugs, true)) {
                return $slug;
            }
        }

        return null;
    }

    /** Semua path gambar lokal (/wp-uploads/...) yang dipakai di isi berita. */
    public static function localImages(string $bodyHtml): array
    {
        preg_match_all('#<img[^>]+src="/' . preg_quote(self::UPLOADS_DIR, '#') . '/([^"]+)"#i', $bodyHtml, $m);

        return array_values(array_unique(array_map('rawurldecode', $m[1] ?? [])));
    }
}