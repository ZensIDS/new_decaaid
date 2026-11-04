<?php
namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Jalan darurat bila satu-satunya Super Admin kehilangan ponsel DAN kode pemulihan:
 *   php artisan admin:reset-2fa decaa@decaa.id
 * Menghapus 2FA akun itu; saat masuk berikutnya ia diminta menyiapkan 2FA baru. Butuh akses server.
 */
class ResetTwoFactor extends Command
{
    protected $signature = 'admin:reset-2fa {email : Email akun admin}';

    protected $description = 'Hapus verifikasi 2 langkah sebuah akun admin (akun diminta menyiapkan ulang saat masuk)';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('Akun tidak ditemukan.');

            return self::FAILURE;
        }

        $user->forceFill([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
            'two_factor_last_step'      => null,
            'remember_token'            => Str::random(60),
        ])->save();

        ActivityLog::record('2fa.reset', "mengatur ulang verifikasi 2 langkah {$user->name} (perintah server)", $user);

        $this->info("Verifikasi 2 langkah {$user->email} dihapus. Akun diminta menyiapkan ulang saat masuk berikutnya.");

        return self::SUCCESS;
    }
}
