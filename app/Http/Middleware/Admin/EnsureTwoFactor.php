<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Super Admin dan Admin wajib mengaktifkan verifikasi 2 langkah (K29). Sebelum aktif, semua layar admin
 * dialihkan ke Keamanan Akun; hanya Keamanan Akun, Ganti Password, dan Keluar yang bisa dibuka.
 * Password sementara tetap diganti lebih dulu (ditangani EnsurePasswordChanged).
 */
class EnsureTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && ! $user->must_change_password
            && $user->requiresTwoFactor()
            && ! $user->hasTwoFactor()
            && ! $request->routeIs('admin.security.*', 'admin.password.*', 'admin.logout')
        ) {
            return redirect()->route('admin.security.edit');
        }

        return $next($request);
    }
}
