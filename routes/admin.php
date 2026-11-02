<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\NewsCategoryController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\PillarController;
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

                // Berita (B3d): semua peran boleh menulis dan mengubah
                Route::middleware('admin.role:super_admin,admin,editor')->group(function () {
                    Route::get('berita', [NewsController::class, 'index'])->name('news.index');
                    Route::get('berita/tambah', [NewsController::class, 'create'])->name('news.create');
                    Route::post('berita', [NewsController::class, 'store'])->name('news.store');
                    Route::post('berita/unggah', [NewsController::class, 'upload'])->middleware('throttle:60,1')->name('news.upload');
                    Route::get('berita/{news}/ubah', [NewsController::class, 'edit'])->name('news.edit');
                    Route::put('berita/{news}', [NewsController::class, 'update'])->name('news.update');
                    Route::post('berita/{news}/revisi/{revision}', [NewsController::class, 'restore'])->name('news.restore');
                });

                // Hapus berita dan kelola kategori: Super Admin dan Admin saja (Editor tidak bisa menghapus)
                Route::middleware('admin.role:super_admin,admin')->group(function () {
                    Route::delete('berita/{news}', [NewsController::class, 'destroy'])->name('news.destroy');

                    Route::get('kategori-berita', [NewsCategoryController::class, 'index'])->name('newscat.index');
                    Route::get('kategori-berita/tambah', [NewsCategoryController::class, 'create'])->name('newscat.create');
                    Route::post('kategori-berita', [NewsCategoryController::class, 'store'])->name('newscat.store');
                    Route::get('kategori-berita/{category}/ubah', [NewsCategoryController::class, 'edit'])->name('newscat.edit');
                    Route::put('kategori-berita/{category}', [NewsCategoryController::class, 'update'])->name('newscat.update');
                    Route::delete('kategori-berita/{category}', [NewsCategoryController::class, 'destroy'])->name('newscat.destroy');
                });

                // Pilar & Brand (B3c, QA8): tambah, ubah, urut, sembunyikan = Super Admin dan Admin
                Route::middleware('admin.role:super_admin,admin')->prefix('pilar-bisnis')->name('pillars.')->group(function () {
                    Route::get('/', [PillarController::class, 'index'])->name('index');
                    Route::get('tambah', [PillarController::class, 'create'])->name('create');
                    Route::post('/', [PillarController::class, 'store'])->name('store');
                    Route::post('urutan', [PillarController::class, 'sort'])->middleware('throttle:60,1')->name('sort');

                    Route::get('brand/tambah', [BrandController::class, 'create'])->name('brands.create');
                    Route::post('brand', [BrandController::class, 'store'])->name('brands.store');
                    Route::post('brand/urutan', [BrandController::class, 'sort'])->middleware('throttle:60,1')->name('brands.sort');
                    Route::get('brand/{brand}/ubah', [BrandController::class, 'edit'])->whereNumber('brand')->name('brands.edit');
                    Route::put('brand/{brand}', [BrandController::class, 'update'])->whereNumber('brand')->name('brands.update');
                    Route::patch('brand/{brand}/status', [BrandController::class, 'toggle'])->whereNumber('brand')->name('brands.toggle');

                    Route::get('{pillar}/ubah', [PillarController::class, 'edit'])->whereNumber('pillar')->name('edit');
                    Route::put('{pillar}', [PillarController::class, 'update'])->whereNumber('pillar')->name('update');
                    Route::patch('{pillar}/status', [PillarController::class, 'toggle'])->whereNumber('pillar')->name('toggle');
                });

                // Hapus pilar dan brand: hanya Super Admin (QA8)
                Route::middleware('admin.role:super_admin')->prefix('pilar-bisnis')->name('pillars.')->group(function () {
                    Route::delete('brand/{brand}', [BrandController::class, 'destroy'])->whereNumber('brand')->name('brands.destroy');
                    Route::delete('{pillar}', [PillarController::class, 'destroy'])->whereNumber('pillar')->name('destroy');
                });

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