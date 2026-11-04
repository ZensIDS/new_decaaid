<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * Lupa password lewat email (K70). Memakai pialang token bawaan Laravel (tabel password_resets, berlaku 60 menit,
 * sekali pakai, jeda 60 detik antar permintaan per akun). Jawaban layar SELALU sama, ada atau tidaknya akun,
 * agar email yang terdaftar tidak bisa ditebak. Akun nonaktif tidak dikirimi tautan. Verifikasi 2 langkah tidak
 * dilewati: setelah password diganti, masuk tetap meminta kode.
 */
class ForgotPasswordController extends Controller
{
    protected const SAME_ANSWER = 'Bila email itu terdaftar sebagai akun aktif, tautan untuk membuat password baru sudah dikirim. Periksa kotak masuk (dan folder spam); tautan berlaku 60 menit.';

    public function show(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('pages.admin.auth.forgot');
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $data['email'])->where('is_active', true)->first();

        if ($user) {
            $status = Password::sendResetLink(['email' => $user->email]);

            if ($status === Password::RESET_LINK_SENT) {
                ActivityLog::record('password.reset_requested', 'meminta tautan atur ulang password', $user, $user);
            }
        }

        return back()->with('status', self::SAME_ANSWER);
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('pages.admin.auth.reset', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(10)->letters()->numbers()],
        ], [], ['password' => 'password baru']);

        $status = Password::reset(
            ['email' => $data['email'], 'password' => $data['password'], 'password_confirmation' => $request->input('password_confirmation'), 'token' => $data['token']],
            function (User $user, string $password) {
                abort_unless($user->is_active, 403);

                $user->forceFill([
                    'password'             => Hash::make($password),
                    'must_change_password' => false,
                    // Mencabut cookie "ingat saya" yang masih beredar.
                    'remember_token'       => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                ActivityLog::record('password.reset', 'mengatur ulang password lewat email', $user, $user);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Tautan tidak valid atau sudah kedaluwarsa. Minta tautan baru dari halaman Lupa Password.',
            ]);
        }

        return redirect()->route('admin.login')->with('status', 'Password berhasil diganti. Silakan masuk dengan password baru.');
    }
}
