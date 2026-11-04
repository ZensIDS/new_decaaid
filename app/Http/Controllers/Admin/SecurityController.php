<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\RecoveryCodes;
use App\Support\Totp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Keamanan Akun (K69): aktifkan/nonaktifkan verifikasi 2 langkah, buat ulang kode pemulihan.
 * Wajib untuk Super Admin dan Admin (middleware admin.2fa mengalihkan ke sini sampai aktif); Editor opsional.
 */
class SecurityController extends Controller
{
    protected const SETUP_KEY = 'two_factor_setup_secret';

    public function edit(Request $request): View
    {
        $user   = $request->user();
        $secret = $request->session()->get(self::SETUP_KEY);
        $setup  = null;

        if (! $user->hasTwoFactor() && is_string($secret) && $secret !== '') {
            $setup = [
                'secret'    => $secret,
                'formatted' => Totp::format($secret),
                'uri'       => Totp::uri($secret, $user->email, config('site.name', 'DECAA.ID')),
            ];
        }

        return view('pages.admin.security', [
            'user'     => $user,
            'setup'    => $setup,
            'forced'   => $user->requiresTwoFactor() && ! $user->hasTwoFactor(),
            'codes'    => session('recovery_codes'),
            'left'     => $user->recoveryCodesLeft(),
        ]);
    }

    /** Mulai penyiapan: buat rahasia baru (belum disimpan ke akun sampai kode pertama benar). */
    public function start(Request $request): RedirectResponse
    {
        if ($request->user()->hasTwoFactor()) {
            return redirect()->route('admin.security.edit');
        }

        $request->session()->put(self::SETUP_KEY, Totp::generateSecret());

        return redirect()->route('admin.security.edit');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SETUP_KEY);

        return redirect()->route('admin.security.edit');
    }

    /** Konfirmasi dengan satu kode dari aplikasi; baru setelah itu 2FA aktif dan kode pemulihan dibuat. */
    public function confirm(Request $request): RedirectResponse
    {
        $user   = $request->user();
        $secret = $request->session()->get(self::SETUP_KEY);

        if ($user->hasTwoFactor() || ! is_string($secret) || $secret === '') {
            return redirect()->route('admin.security.edit');
        }

        $data = $request->validate(['code' => ['required', 'string', 'max:16']], [], ['code' => 'kode']);
        $step = Totp::verify($secret, $data['code'], 1);

        if ($step === null) {
            throw ValidationException::withMessages([
                'code' => 'Kode tidak cocok. Pastikan akun di aplikasi adalah yang baru ditambahkan, jam ponsel akurat, lalu coba kode terbaru.',
            ]);
        }

        $codes = RecoveryCodes::generate();

        $user->forceFill([
            'two_factor_secret'       => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_last_step'    => $step,
        ]);
        RecoveryCodes::assign($user, $codes);
        $user->save();

        $request->session()->forget(self::SETUP_KEY);

        ActivityLog::record('2fa.enabled', 'mengaktifkan verifikasi 2 langkah', $user, $user);

        return redirect()->route('admin.security.edit')
            ->with('status', 'Verifikasi 2 langkah aktif. Simpan kode pemulihan di bawah sekarang.')
            ->with('recovery_codes', $codes);
    }

    /** Buat ulang kode pemulihan (kode lama langsung tidak berlaku). Butuh password. */
    public function recovery(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validateWithBag('recovery', ['current_password' => ['required', 'current_password']], [], ['current_password' => 'password']);

        if (! $user->hasTwoFactor()) {
            return redirect()->route('admin.security.edit');
        }

        $codes = RecoveryCodes::generate();
        RecoveryCodes::assign($user, $codes);
        $user->save();

        ActivityLog::record('2fa.recovery_regenerated', 'membuat ulang kode pemulihan 2FA', $user, $user);

        return redirect()->route('admin.security.edit')
            ->with('status', 'Kode pemulihan baru dibuat. Kode lama tidak berlaku lagi.')
            ->with('recovery_codes', $codes);
    }

    /** Nonaktifkan (hanya Editor; Super Admin dan Admin wajib memakainya). Butuh password. */
    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validateWithBag('disable', ['current_password' => ['required', 'current_password']], [], ['current_password' => 'password']);

        if ($user->requiresTwoFactor()) {
            return redirect()->route('admin.security.edit')->with('error', 'Peran Anda wajib memakai verifikasi 2 langkah, jadi tidak bisa dinonaktifkan.');
        }

        $user->forceFill([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
            'two_factor_last_step'      => null,
        ])->save();

        ActivityLog::record('2fa.disabled', 'menonaktifkan verifikasi 2 langkah', $user, $user);

        return redirect()->route('admin.security.edit')->with('status', 'Verifikasi 2 langkah dinonaktifkan.');
    }
}
