<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /** Batas percobaan login per kombinasi email + IP. */
    protected const MAX_ATTEMPTS = 5;

    public function show(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('pages.admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', ['seconds' => $seconds]),
            ]);
        }

        // Password dicek dulu TANPA masuk: bila akun memakai 2FA, sesi baru dibuat setelah kode benar (K69).
        $provider = Auth::guard()->getProvider();
        $user     = $provider->retrieveByCredentials(['email' => $credentials['email']]);

        if (! $user || ! $provider->validateCredentials($user, $credentials)) {
            RateLimiter::hit($key, 60);

            // Pesan sengaja generik: tidak membocorkan apakah email terdaftar.
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda dinonaktifkan. Hubungi Super Admin.',
            ]);
        }

        RateLimiter::clear($key);

        if ($user->hasTwoFactor()) {
            $request->session()->regenerate();
            $request->session()->put('admin_2fa', [
                'id'       => $user->getKey(),
                'remember' => $request->boolean('remember'),
                'at'       => time(),
            ]);

            return redirect()->route('admin.two-factor.challenge');
        }

        return self::finish($request, $user, $request->boolean('remember'));
    }

    /** Menyelesaikan login (dipakai juga oleh tantangan 2FA): masuk, sesi baru, catat, lanjut ke tujuan. */
    public static function finish(Request $request, User $user, bool $remember, bool $twoFactor = false): RedirectResponse
    {
        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        ActivityLog::record('auth.login', 'masuk ke panel admin'.($twoFactor ? ' (dengan verifikasi 2 langkah)' : ''), $user, $user);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        ActivityLog::record('auth.logout', 'keluar dari panel admin');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'Anda sudah keluar.');
    }
}
