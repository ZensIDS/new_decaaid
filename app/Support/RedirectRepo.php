<?php
namespace App\Support;

use App\Models\UrlRedirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * B3n: mencocokkan alamat yang tidak ditemukan (404) dengan aturan Redirect. Aman bila tabel belum ada
 * (belum migrate): match() mengembalikan null dan halaman 404 tampil seperti biasa.
 */
class RedirectRepo
{
    protected static ?bool $available = null;

    public static function available(): bool
    {
        if (self::$available === null) {
            try {
                self::$available = Schema::hasTable('redirects');
            } catch (Throwable) {
                self::$available = false;
            }
        }

        return self::$available;
    }

    /** Aturan aktif yang cocok dengan path permintaan, atau null. Hanya untuk GET/HEAD. */
    public static function match(Request $request): ?UrlRedirect
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true) || ! self::available()) {
            return null;
        }

        $from = UrlRedirect::normalizeFrom($request->getPathInfo());

        if ($from === '' || UrlRedirect::isBlocked($from)) {
            return null;
        }

        try {
            $rule = UrlRedirect::where('from_path', $from)->where('is_active', true)->first();

            if (! $rule) {
                return null;
            }

            // Cegah putaran: tujuan internal yang sama dengan asal diabaikan
            if (str_starts_with($rule->to_path, '/') && UrlRedirect::normalizeFrom($rule->to_path) === $from) {
                return null;
            }

            UrlRedirect::where('id', $rule->id)->update(['hits' => $rule->hits + 1, 'last_hit_at' => now()]);

            return $rule;
        } catch (Throwable) {
            return null;
        }
    }
}
