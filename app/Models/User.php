<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;
use Throwable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_EDITOR = 'editor';

    /** Daftar peran dan labelnya (urutan = dari akses terluas). */
    public const ROLES = [
        self::ROLE_SUPER_ADMIN => 'Super Admin',
        self::ROLE_ADMIN => 'Admin',
        self::ROLE_EDITOR => 'Editor',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
        'last_login_at' => 'datetime',
        'two_factor_secret' => 'encrypted',
        'two_factor_confirmed_at' => 'datetime',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isEditor(): bool
    {
        return $this->role === self::ROLE_EDITOR;
    }

    /** Apakah pengguna memiliki salah satu peran yang diberikan. */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Lupa password (K70): dipanggil broker Laravel dengan token sekali pakai. Email teks biasa seperti email lain
     * di situs ini. Kegagalan kirim tidak dilempar ke pengunjung (jawaban layar selalu sama); hanya dilaporkan ke log.
     */
    public function sendPasswordResetNotification($token): void
    {
        $url  = route('admin.reset.show', ['token' => $token, 'email' => $this->email]);
        $mins = (int) config('auth.passwords.users.expire', 60);
        $site = config('site.name', 'DECAA.ID');

        $body = "Halo {$this->name},\n\n"
            ."Kami menerima permintaan mengatur ulang password panel admin {$site} untuk akun ini.\n\n"
            ."Buka tautan berikut untuk membuat password baru (berlaku {$mins} menit, hanya bisa dipakai sekali):\n{$url}\n\n"
            ."Jika Anda tidak merasa memintanya, abaikan email ini; password Anda tidak berubah.\n"
            ."Akun yang memakai verifikasi 2 langkah tetap memerlukan kode dari aplikasi autentikator saat masuk.\n\n"
            ."{$site}";

        try {
            Mail::raw($body, function ($mail) use ($site) {
                $mail->to($this->email)->subject("Atur ulang password panel admin {$site}");
            });
        } catch (Throwable $e) {
            report($e);
        }
    }

    /** Verifikasi 2 langkah sudah aktif (rahasia tersimpan dan sudah dikonfirmasi dengan satu kode). */
    public function hasTwoFactor(): bool
    {
        // Memakai nilai mentah (tanpa dekripsi) agar akun tetap dikenali bila APP_KEY berubah; kegagalan dekripsi ditangani saat verifikasi.
        return $this->two_factor_confirmed_at !== null && ! empty($this->getAttributes()['two_factor_secret'] ?? null);
    }

    /** Peran yang wajib memakai verifikasi 2 langkah (K29): Super Admin dan Admin. Editor opsional. */
    public function requiresTwoFactor(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN);
    }

    /** Sisa kode pemulihan yang belum dipakai. */
    public function recoveryCodesLeft(): int
    {
        $codes = json_decode((string) $this->two_factor_recovery_codes, true);

        return is_array($codes) ? count($codes) : 0;
    }

    /** Jumlah Super Admin yang akunnya aktif (dipakai untuk menjaga minimal satu tersisa). */
    public static function activeSuperAdminCount(): int
    {
        return static::where('role', self::ROLE_SUPER_ADMIN)->where('is_active', true)->count();
    }

    /** Apakah pengguna ini satu-satunya Super Admin aktif. */
    public function isLastActiveSuperAdmin(): bool
    {
        return $this->isSuperAdmin() && $this->is_active && static::activeSuperAdminCount() <= 1;
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? ucfirst((string) $this->role);
    }
}