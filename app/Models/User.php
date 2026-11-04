<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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