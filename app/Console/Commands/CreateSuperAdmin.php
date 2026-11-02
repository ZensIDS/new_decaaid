<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    protected $signature = 'admin:create-super
                            {--email=decaa@decaa.id : Email Super Admin}
                            {--name=Super Admin DECAA : Nama tampilan}
                            {--password= : Password awal (bila kosong, akan ditanyakan)}
                            {--reset-password : Atur ulang password bila akun sudah ada}';

    protected $description = 'Membuat (atau memastikan) akun Super Admin awal. Aman dijalankan berulang.';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->option('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Format email tidak valid.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user && ! $this->option('reset-password')) {
            $user->forceFill(['role' => User::ROLE_SUPER_ADMIN, 'is_active' => true])->save();
            $this->info("Akun {$email} sudah ada: dipastikan berperan Super Admin dan aktif (password tidak diubah).");
            $this->line('Gunakan --reset-password bila ingin mengatur ulang password.');

            return self::SUCCESS;
        }

        $password = (string) $this->option('password');

        if ($password === '') {
            $password = (string) $this->secret('Password awal');

            if ($password === '') {
                $this->error('Password tidak boleh kosong.');

                return self::FAILURE;
            }
        }

        // Password awal wajib diganti saat login pertama (must_change_password).
        $attributes = [
            'password' => Hash::make($password),
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
            'must_change_password' => true,
        ];

        if ($user) {
            $user->forceFill($attributes)->save();
            $this->info("Password {$email} diatur ulang; wajib diganti saat login pertama.");
        } else {
            User::create(['name' => (string) $this->option('name'), 'email' => $email] + $attributes);
            $this->info("Super Admin {$email} dibuat; wajib ganti password saat login pertama.");
        }

        return self::SUCCESS;
    }
}