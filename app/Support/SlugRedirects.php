<?php
namespace App\Support;

use App\Models\UrlRedirect;
use Throwable;

/**
 * B3n (bagian 2): membuat Redirect 301 otomatis saat alamat (slug) konten berubah, untuk kedua bahasa (ID dan EN).
 * Dipanggil dari admin Berita, Proyek, Pilar, Brand, dan Halaman Kustom setelah penyimpanan berhasil.
 *
 * Aturan:
 *  - alamat lama -> alamat baru, 301, catatan "Otomatis: ..." (terlihat di layar Redirect, boleh dihapus/dinonaktifkan);
 *  - aturan otomatis lama yang menunjuk ke alamat lama diteruskan ke alamat baru (tidak berantai);
 *  - aturan otomatis yang berasal dari alamat baru dihapus (mis. slug dikembalikan ke semula), agar tidak berputar;
 *  - aturan buatan admin sendiri tidak pernah dihapus;
 *  - aman bila tabel redirects belum ada, dan kegagalan tidak pernah menggagalkan penyimpanan konten.
 */
class SlugRedirects
{
    public const NOTE_PREFIX = 'Otomatis: ';

    /**
     * @param string               $key  kunci rute (mis. "news.show", "portfolio.show", "units.show", "units.brand") atau "custom"
     * @param array<string,string> $old  parameter alamat lama (mis. ['slug' => 'lama'])
     * @param array<string,string> $new  parameter alamat baru
     * @return int jumlah aturan yang dibuat atau diperbarui
     */
    public static function changed(string $key, array $old, array $new, string $label = ''): int
    {
        if ($old === $new || ! RedirectRepo::available()) {
            return 0;
        }

        $count = 0;

        try {
            foreach (config('site.locales') as $locale) {
                $from = self::path($key, $old, $locale);
                $to   = self::path($key, $new, $locale);

                if ($from === null || $to === null || UrlRedirect::normalizeFrom($from) === UrlRedirect::normalizeFrom($to)) {
                    continue;
                }

                $fromKey = UrlRedirect::normalizeFrom($from);

                if ($fromKey === '' || UrlRedirect::isBlocked($fromKey)) {
                    continue;
                }

                // Alamat baru tidak boleh dialihkan oleh aturan otomatis lama (mencegah putaran).
                UrlRedirect::where('from_path', UrlRedirect::normalizeFrom($to))
                    ->where('note', 'like', self::NOTE_PREFIX . '%')->delete();

                // Aturan otomatis yang menuju alamat lama diteruskan ke alamat baru.
                UrlRedirect::where('to_path', $from)->where('note', 'like', self::NOTE_PREFIX . '%')
                    ->update(['to_path' => $to]);

                $existing = UrlRedirect::where('from_path', $fromKey)->first();

                // Aturan buatan admin untuk alamat yang sama dibiarkan apa adanya.
                if ($existing && ! str_starts_with((string) $existing->note, self::NOTE_PREFIX)) {
                    continue;
                }

                UrlRedirect::updateOrCreate(
                    ['from_path' => $fromKey],
                    [
                        'to_path'     => $to,
                        'status_code' => 301,
                        'is_active'   => true,
                        'note'        => mb_substr(self::NOTE_PREFIX . ($label !== '' ? $label : 'alamat berubah'), 0, 200),
                        'created_by'  => $existing?->created_by ?? auth()->id(),
                        'updated_by'  => auth()->id(),
                    ]
                );

                $count++;
            }
        } catch (Throwable $e) {
            report($e);
        }

        return $count;
    }

    /** Kalimat tambahan untuk pesan sukses admin. */
    public static function note(int $count): string
    {
        return $count > 0 ? ' Redirect 301 dari alamat lama dibuat otomatis (lihat SEO & Redirect).' : '';
    }

    /** Path relatif ("/id/berita/x") dari kunci rute + parameter; null bila rute atau parameter tidak lengkap. */
    protected static function path(string $key, array $params, string $locale): ?string
    {
        // Kunci rute memuat titik ("news.show"), jadi dibaca lewat array, bukan notasi titik config().
        $template = $key === 'custom' ? '{slug}' : (config('site.routes')[$key]['slug'][$locale] ?? null);

        if (! is_string($template)) {
            return null;
        }

        foreach ($params as $name => $value) {
            $template = str_replace('{' . $name . '}', (string) $value, $template);
        }

        if (str_contains($template, '{')) {
            return null;
        }

        return '/' . $locale . '/' . ltrim($template, '/');
    }
}
