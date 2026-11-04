<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tahap C: header keamanan untuk semua respons.
 * - Selalu: X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy, COOP.
 * - HSTS: hanya bila permintaan lewat HTTPS dan SECURITY_HSTS=true (jangan aktif di lokal/staging sebelum yakin HTTPS stabil).
 * - CSP: SECURITY_CSP=off|report|enforce. Bawaan "report" (Content-Security-Policy-Report-Only), jadi tidak ada yang
 *   diblokir; setelah konsol browser bersih dari pelanggaran, ganti ke "enforce".
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! $response instanceof Response) {
            return $response;
        }

        $h = $response->headers;
        $set = fn (string $k, string $v) => $h->has($k) ? null : $h->set($k, $v);

        $set('X-Content-Type-Options', 'nosniff');
        $set('X-Frame-Options', 'SAMEORIGIN');
        $set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $set('Cross-Origin-Opener-Policy', 'same-origin');

        if ($request->isSecure() && config('security.hsts')) {
            $set('Strict-Transport-Security', 'max-age=' . (int) config('security.hsts_max_age') . '; includeSubDomains');
        }

        $mode = config('security.csp');
        if (in_array($mode, ['report', 'enforce'], true)) {
            $set($mode === 'enforce' ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only', $this->policy($request));
        }

        return $response;
    }

    protected function policy(Request $request): string
    {
        // Vite dev server (hanya saat lokal, ada file public/hot) perlu diizinkan.
        $dev = file_exists(public_path('hot')) ? ' http://localhost:5173 http://127.0.0.1:5173 http://[::1]:5173 ws://localhost:5173' : '';
        // Alpine.js memakai ekspresi inline: butuh 'unsafe-eval' (bawaan build standar) dan 'unsafe-inline' untuk skrip kecil di halaman.
        $analytics = ' https://www.googletagmanager.com https://www.google-analytics.com https://plausible.io https://cloud.umami.is';

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'" . $analytics . $dev,
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com" . $dev,
            "font-src 'self' data: https://fonts.gstatic.com",
            "img-src 'self' data: blob: https:",
            "connect-src 'self'" . $analytics . $dev,
            "frame-src 'self' https://www.google.com https://maps.google.com https://www.youtube.com https://www.youtube-nocookie.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]);
    }
}
