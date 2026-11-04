<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * BA3: CRUD pengguna admin. Semua rute dibatasi Super Admin (lihat routes/admin.php).
 * Pengaman: tidak bisa mengubah/menonaktifkan/menghapus diri sendiri lewat layar ini,
 * dan selalu tersisa minimal satu Super Admin aktif.
 */
class UserController extends Controller
{
    protected const PER_PAGE = 10;

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $role = $request->query('role');
        $status = $request->query('status');

        $users = User::query()
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $like = '%'.addcslashes($q, '%_\\').'%';
                $w->where('name', 'like', $like)->orWhere('email', 'like', $like);
            }))
            ->when(array_key_exists((string) $role, User::ROLES), fn ($query) => $query->where('role', $role))
            ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('pages.admin.users.index', [
            'users' => $users,
            'q' => $q,
            'role' => $role,
            'status' => $status,
            'filtered' => $q !== '' || $role || $status,
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.users.form', ['user' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['required', Password::min(10)->letters()->numbers()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => Str::lower($data['email']),
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
            'must_change_password' => true,
        ]);

        ActivityLog::record('user.created', "menambah pengguna {$user->name} ({$user->roleLabel()})", $user);

        return redirect()->route('admin.users.index')
            ->with('status', "Pengguna {$user->name} dibuat. Sampaikan password sementara kepadanya; ia wajib menggantinya saat masuk pertama kali.");
    }

    public function edit(User $user): View
    {
        return view('pages.admin.users.form', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $self = $user->is($request->user());

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
        ];

        // Akun sendiri: peran dan status tidak bisa diubah dari sini.
        if (! $self) {
            $rules['role'] = ['required', Rule::in(array_keys(User::ROLES))];
            $rules['is_active'] = ['required', 'boolean'];
        }

        $data = $request->validate($rules);

        if (! $self) {
            $losesSuper = $user->isLastActiveSuperAdmin()
                && ($data['role'] !== User::ROLE_SUPER_ADMIN || ! (bool) $data['is_active']);

            if ($losesSuper) {
                return back()->withInput()->with('error', 'Tidak bisa dilakukan: harus tersisa minimal satu Super Admin yang aktif.');
            }
        }

        $before = [$user->role, (bool) $user->is_active];

        $user->fill([
            'name' => $data['name'],
            'email' => Str::lower($data['email']),
        ]);

        if (! $self) {
            $user->role = $data['role'];
            $user->is_active = (bool) $data['is_active'];
        }

        $user->save();

        $note = '';
        if (! $self && $before[0] !== $user->role) {
            $note .= ", peran menjadi {$user->roleLabel()}";
        }
        if (! $self && $before[1] !== (bool) $user->is_active) {
            $note .= $user->is_active ? ', akun diaktifkan' : ', akun dinonaktifkan';
        }

        ActivityLog::record('user.updated', "mengubah data pengguna {$user->name}{$note}", $user);

        return redirect()->route('admin.users.index')->with('status', "Data {$user->name} disimpan.");
    }

    /** Aktifkan atau nonaktifkan akun. */
    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak bisa menonaktifkan akun Anda sendiri.');
        }

        if ($user->isLastActiveSuperAdmin()) {
            return back()->with('error', 'Tidak bisa dilakukan: harus tersisa minimal satu Super Admin yang aktif.');
        }

        $user->forceFill(['is_active' => ! $user->is_active])->save();

        if ($user->is_active) {
            ActivityLog::record('user.activated', "mengaktifkan akun {$user->name}", $user);
            $message = "Akun {$user->name} diaktifkan.";
        } else {
            ActivityLog::record('user.deactivated', "menonaktifkan akun {$user->name}", $user);
            $message = "Akun {$user->name} dinonaktifkan dan tidak bisa masuk lagi.";
        }

        return back()->with('status', $message);
    }

    /** Super Admin mengatur password sementara; pengguna wajib menggantinya saat masuk. */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Untuk akun Anda sendiri, pakai menu Ganti Password.');
        }

        $data = $request->validate([
            'new_password' => ['required', Password::min(10)->letters()->numbers()],
        ], [], ['new_password' => 'password sementara']);

        $user->forceFill([
            'password' => Hash::make($data['new_password']),
            'must_change_password' => true,
            // Mencabut cookie "ingat saya" yang masih beredar.
            'remember_token' => Str::random(60),
        ])->save();

        ActivityLog::record('user.password_reset', "mengatur ulang password {$user->name}", $user);

        return redirect()->route('admin.users.edit', $user)
            ->with('status', 'Password sementara disimpan. Sampaikan kepada pengguna; ia wajib menggantinya saat masuk.');
    }

    /** Super Admin menghapus 2FA pengguna lain yang kehilangan ponsel dan kode pemulihan; ia menyiapkan ulang saat masuk. */
    public function resetTwoFactor(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Untuk akun Anda sendiri, kelola lewat menu Keamanan Akun.');
        }

        if (! $user->hasTwoFactor()) {
            return back()->with('error', 'Pengguna ini belum memakai verifikasi 2 langkah.');
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_last_step' => null,
            // Mencabut cookie "ingat saya" yang masih beredar.
            'remember_token' => Str::random(60),
        ])->save();

        ActivityLog::record('2fa.reset', "mengatur ulang verifikasi 2 langkah {$user->name}", $user);

        return redirect()->route('admin.users.edit', $user)
            ->with('status', $user->requiresTwoFactor()
                ? 'Verifikasi 2 langkah dihapus. Pengguna wajib menyiapkannya lagi saat masuk berikutnya.'
                : 'Verifikasi 2 langkah dihapus.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        if ($user->isLastActiveSuperAdmin()) {
            return back()->with('error', 'Tidak bisa dilakukan: harus tersisa minimal satu Super Admin yang aktif.');
        }

        $name = $user->name;
        $role = $user->roleLabel();

        // Log lama tetap utuh: user_id jadi kosong, nama pelaku sudah tersalin di log.
        $user->delete();

        ActivityLog::record('user.deleted', "menghapus pengguna {$name} ({$role})");

        return redirect()->route('admin.users.index')->with('status', "Pengguna {$name} dihapus.");
    }
}