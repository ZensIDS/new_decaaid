<?php
namespace App\Http\Middleware;

use App\Support\SystemSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * B3p (QA17): mode pemeliharaan yang dinyalakan dari admin (bukan `artisan down`).
 * Saat aktif, pengunjung melihat halaman 503. Tetap bisa masuk: alamat /admin/*, siapa pun yang sudah login admin,
 * alamat IP yang didaftarkan sebagai pengecualian, dan siapa pun yang membuka tautan rahasia /{rahasia} (cookie 12 jam).
 */
class SiteMaintenance
{
    public const COOKIE = 'maint_bypass';

    public function handle(Request $request, Closure $next): Response
    {
        if (! SystemSettings::maintenanceOn()) {
            return $next($request);
        }

        $secret = (string) SystemSettings::get()['maintenance']['secret'];
        $path   = trim($request->path(), '/');
        $token  = SystemSettings::bypassToken($secret);

        if ($path === $secret) {
            return redirect('/')->withCookie(cookie(self::COOKIE, $token, 60 * 12));
        }

        if ($path === 'admin' || str_starts_with($path, 'admin/')) {
            return $next($request);
        }

        if (hash_equals($token, (string) $request->cookie(self::COOKIE)) || $request->user() || SystemSettings::ipAllowed($request->ip())) {
            return $next($request);
        }

        return response()->view('pages.errors.503', [], 503)
            ->header('Retry-After', '3600')
            ->header('X-Robots-Tag', 'noindex');
    }
}
