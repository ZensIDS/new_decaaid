<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Catatan aktivitas admin. Pakai ActivityLog::record('user.created', 'menambah pengguna ...', $user).
 * Kalimat deskripsi ditulis sebagai predikat ("masuk ke panel admin") karena tampil setelah nama pelaku.
 */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'user_name', 'action', 'description',
        'subject_type', 'subject_id', 'ip_address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** Kelompok aksi (awalan sebelum titik) untuk filter di layar Log Aktivitas. */
    public const GROUPS = [
        'auth'     => 'Masuk dan Keluar',
        'password' => 'Password',
        'profile'  => 'Profil',
        'user'     => 'Pengguna',
        'news'     => 'Berita',
        'newscat'  => 'Kategori Berita',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mencatat aktivitas. Tidak pernah melempar galat: kegagalan log tidak boleh
     * menggagalkan aksi utama (mis. login).
     */
    public static function record(string $action, string $description, ?Model $subject = null, ?User $actor = null): void
    {
        try {
            $actor ??= Auth::user();

            static::create([
                'user_id'      => $actor?->getKey(),
                'user_name'    => $actor?->name ?? 'Sistem',
                'action'       => $action,
                'description'  => mb_substr($description, 0, 255),
                'subject_type' => $subject ? $subject->getMorphClass() : null,
                'subject_id'   => $subject?->getKey(),
                'ip_address'   => request()?->ip(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
