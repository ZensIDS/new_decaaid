<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Admin (/admin)
|--------------------------------------------------------------------------
| Tanpa prefiks bahasa; antarmuka admin selalu Bahasa Indonesia.
| Dimuat dari RouteServiceProvider dengan middleware grup "web".
| Nama rute: admin.*  (contoh: route('admin.dashboard'))
*/

Route::middleware(['admin.locale', 'admin.noindex'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Tamu
        Route::get('login', [LoginController::class, 'show'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');

        // Sudah login (akun harus aktif)
        Route::middleware(['auth', 'admin.active'])->group(function () {
            Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

            // Ganti password (juga dipakai untuk ganti password wajib)
            Route::get('password', [PasswordController::class, 'edit'])->name('password.edit');
            Route::put('password', [PasswordController::class, 'update'])->name('password.update');

            // Halaman yang baru bisa dibuka setelah password wajib diganti
            Route::middleware('admin.password')->group(function () {
                Route::get('/', DashboardController::class)->name('dashboard');

                // Profil sendiri (semua peran)
                Route::get('profil', [ProfileController::class, 'edit'])->name('profile.edit');
                Route::put('profil', [ProfileController::class, 'update'])->name('profile.update');

                // Khusus Super Admin: pengguna dan log aktivitas
                Route::middleware('admin.role:super_admin')->group(function () {
                    Route::get('pengguna', [UserController::class, 'index'])->name('users.index');
                    Route::get('pengguna/tambah', [UserController::class, 'create'])->name('users.create');
                    Route::post('pengguna', [UserController::class, 'store'])->name('users.store');
                    Route::get('pengguna/{user}/ubah', [UserController::class, 'edit'])->name('users.edit');
                    Route::put('pengguna/{user}', [UserController::class, 'update'])->name('users.update');
                    Route::patch('pengguna/{user}/status', [UserController::class, 'toggle'])->name('users.toggle');
                    Route::put('pengguna/{user}/password', [UserController::class, 'resetPassword'])->name('users.password');
                    Route::delete('pengguna/{user}', [UserController::class, 'destroy'])->name('users.destroy');

                    Route::get('log-aktivitas', [LogController::class, 'index'])->name('logs.index');
                });
            });
        });
    });