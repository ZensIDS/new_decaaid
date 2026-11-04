<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\RecoveryCodes;
use App\Support\Totp;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Langkah kedua masuk admin (K69): kode 6 digit dari aplikasi autentikator, atau satu kode pemulihan.
 * Password sudah benar tetapi sesi BELUM dibuat; yang tersimpan hanya penanda sementara (maks. 10 menit).
 */
class TwoFactorChallengeController extends Controller
{
    protected const MAX_ATTEMPTS = 5;
    protected const TTL = 600;

    public function show(Request $request): View|RedirectResponse
    {
        if (! $this->pending($request)) {
            return $this->expired();
        }

        return view('pages.admin.auth.two-factor');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->pending($request);

        if (! $user) {
            return $this->expired();
        }

        $data = $request->validate(['code' => ['required', 'string', 'max:32']], [], ['code' => 'kode']);
        $key  = '2fa|'.$user->getKey().'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'code' => trans('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]),
            ]);
        }

        try {
            $secret = (string) $user->two_factor_secret;
        } catch (DecryptException) {
            throw ValidationException::withMessages([
                'code' => 'Data verifikasi akun ini tidak terbaca. Minta Super Admin mengatur ulang 2FA Anda.',
            ]);
        }

        $code = trim($data['code']);
        $step = Totp::verify($secret, $code, 1);
        $usedRecovery = false;

        if ($step !== null && $step > (int) $user->two_factor_last_step) {
            // Kode yang sama tidak bisa dipakai dua kali.
            $user->forceFill(['two_factor_last_step' => $step])->saveQuietly();
        } elseif ($step === null && RecoveryCodes::consume($user, $code)) {
            $usedRecovery = true;
        } else {
            RateLimiter::hit($key, 300);

            throw ValidationException::withMessages([
                'code' => $step !== null
                    ? 'Kode ini baru saja dipakai. Tunggu sekitar 30 detik sampai kode baru muncul, lalu coba lagi.'
                    : 'Kode tidak cocok. Pastikan jam ponsel Anda akurat, atau pakai salah satu kode pemulihan.',
            ]);
        }

        RateLimiter::clear($key);

        $remember = (bool) ($request->session()->get('admin_2fa.remember'));
        $request->session()->forget('admin_2fa');

        $response = LoginController::finish($request, $user, $remember, true);

        if ($usedRecovery) {
            ActivityLog::record('2fa.recovery_used', 'masuk memakai kode pemulihan', $user, $user);
            $left = $user->recoveryCodesLeft();

            return $response->with('status', "Anda masuk dengan kode pemulihan. Sisa {$left} kode; buat kode baru di Keamanan Akun.");
        }

        return $response;
    }

    /** Batal: kembali ke halaman masuk. */
    public function cancel(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_2fa');

        return redirect()->route('admin.login');
    }

    protected function pending(Request $request): ?User
    {
        $p = $request->session()->get('admin_2fa');

        if (! is_array($p) || (time() - (int) ($p['at'] ?? 0)) > self::TTL) {
            $request->session()->forget('admin_2fa');

            return null;
        }

        $user = User::find($p['id'] ?? 0);

        return $user && $user->is_active && $user->hasTwoFactor() ? $user : null;
    }

    protected function expired(): RedirectResponse
    {
        return redirect()->route('admin.login')->with('status', 'Sesi verifikasi berakhir. Silakan masuk lagi.');
    }
}
