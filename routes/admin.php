<?php

use App\Http\Controllers\Admin\AboutController;
use App\Http\Controllers\Admin\AwardController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CareerController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\ClientCategoryController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CustomPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HomePageController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\NewsCategoryController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\PillarController;
use App\Http\Controllers\Admin\PrivacyController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\SeoPageController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\SustainabilityController;
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

                // Proyek / Portofolio (B3g): semua peran boleh menambah dan mengubah; hapus hanya Super Admin dan Admin
                Route::middleware('admin.role:super_admin,admin,editor')->prefix('proyek')->name('projects.')->group(function () {
                    Route::get('/', [ProjectController::class, 'index'])->name('index');
                    Route::get('tambah', [ProjectController::class, 'create'])->name('create');
                    Route::post('/', [ProjectController::class, 'store'])->name('store');
                    Route::get('{project}/ubah', [ProjectController::class, 'edit'])->whereNumber('project')->name('edit');
                    Route::put('{project}', [ProjectController::class, 'update'])->whereNumber('project')->name('update');
                    Route::patch('{project}/status', [ProjectController::class, 'toggle'])->whereNumber('project')->name('toggle');
                    Route::patch('{project}/unggulan', [ProjectController::class, 'feature'])->whereNumber('project')->name('feature');
                });

                Route::middleware('admin.role:super_admin,admin')->prefix('proyek')->name('projects.')->group(function () {
                    Route::delete('{project}', [ProjectController::class, 'destroy'])->whereNumber('project')->name('destroy');
                });

                // Media Library (B3b): pemilih (JSON) untuk semua peran admin; perpustakaan lengkap untuk Super Admin dan Admin
                Route::middleware('admin.role:super_admin,admin,editor')->prefix('media')->name('media.')->group(function () {
                    Route::get('pilih', [MediaController::class, 'picker'])->name('picker');
                    Route::post('pilih', [MediaController::class, 'pickerUpload'])->middleware('throttle:30,1')->name('picker.upload');
                });

                Route::middleware('admin.role:super_admin,admin')->prefix('media')->name('media.')->group(function () {
                    Route::get('/', [MediaController::class, 'index'])->name('index');
                    Route::post('/', [MediaController::class, 'store'])->middleware('throttle:20,1')->name('store');
                    Route::get('{media}', [MediaController::class, 'show'])->whereNumber('media')->name('show');
                    Route::put('{media}', [MediaController::class, 'update'])->whereNumber('media')->name('update');
                    Route::delete('{media}', [MediaController::class, 'destroy'])->whereNumber('media')->name('destroy');
                });

                // Penghargaan (B3i): semua peran boleh menambah dan mengubah; hapus hanya Super Admin dan Admin
                Route::middleware('admin.role:super_admin,admin,editor')->prefix('penghargaan')->name('awards.')->group(function () {
                    Route::get('/', [AwardController::class, 'index'])->name('index');
                    Route::get('tambah', [AwardController::class, 'create'])->name('create');
                    Route::post('/', [AwardController::class, 'store'])->name('store');
                    Route::get('{award}/ubah', [AwardController::class, 'edit'])->whereNumber('award')->name('edit');
                    Route::put('{award}', [AwardController::class, 'update'])->whereNumber('award')->name('update');
                    Route::patch('{award}/status', [AwardController::class, 'toggle'])->whereNumber('award')->name('toggle');
                    Route::patch('{award}/unggulan', [AwardController::class, 'feature'])->whereNumber('award')->name('feature');
                });

                Route::middleware('admin.role:super_admin,admin')->prefix('penghargaan')->name('awards.')->group(function () {
                    Route::delete('{award}', [AwardController::class, 'destroy'])->whereNumber('award')->name('destroy');
                });

                // Keberlanjutan (B3j): satu halaman, Super Admin dan Admin
                Route::middleware('admin.role:super_admin,admin')->prefix('keberlanjutan')->name('sustainability.')->group(function () {
                    Route::get('/', [SustainabilityController::class, 'edit'])->name('edit');
                    Route::put('/', [SustainabilityController::class, 'update'])->name('update');
                    Route::delete('/', [SustainabilityController::class, 'reset'])->name('reset');
                });

                // Beranda (B3e): satu halaman, Super Admin dan Admin
                Route::middleware('admin.role:super_admin,admin')->prefix('beranda')->name('home.')->group(function () {
                    Route::get('/', [HomePageController::class, 'edit'])->name('edit');
                    Route::put('/', [HomePageController::class, 'update'])->name('update');
                    Route::delete('/', [HomePageController::class, 'reset'])->name('reset');
                });

                // Tentang Kami (B3f): satu halaman (Profil + Chairman, Visi & Misi, Perjalanan), Super Admin dan Admin
                Route::middleware('admin.role:super_admin,admin')->prefix('tentang-kami')->name('about.')->group(function () {
                    Route::get('/', [AboutController::class, 'edit'])->name('edit');
                    Route::put('/', [AboutController::class, 'update'])->name('update');
                    Route::delete('/', [AboutController::class, 'reset'])->name('reset');
                });

                // Karir (B3k): satu halaman, Super Admin dan Admin
                Route::middleware('admin.role:super_admin,admin')->prefix('karir')->name('careers.')->group(function () {
                    Route::get('/', [CareerController::class, 'edit'])->name('edit');
                    Route::put('/', [CareerController::class, 'update'])->name('update');
                    Route::delete('/', [CareerController::class, 'reset'])->name('reset');
                });

                // Menu (B3o): satu halaman, Super Admin dan Admin
                Route::middleware('admin.role:super_admin,admin')->prefix('menu')->name('menus.')->group(function () {
                    Route::get('/', [MenuController::class, 'edit'])->name('edit');
                    Route::put('/', [MenuController::class, 'update'])->name('update');
                    Route::delete('/', [MenuController::class, 'reset'])->name('reset');
                });

                // Pengaturan Situs (B3a): satu halaman, Super Admin dan Admin
                Route::middleware('admin.role:super_admin,admin')->prefix('pengaturan-situs')->name('settings.')->group(function () {
                    Route::get('/', [SiteSettingsController::class, 'edit'])->name('edit');
                    Route::put('/', [SiteSettingsController::class, 'update'])->name('update');
                    Route::delete('/', [SiteSettingsController::class, 'reset'])->name('reset');
                });

                // Halaman Legal (B3m): Kebijakan Privasi, Super Admin dan Admin
                Route::middleware('admin.role:super_admin,admin')->prefix('halaman-legal')->name('pages.')->group(function () {
                    Route::get('privasi', [PrivacyController::class, 'edit'])->name('privacy.edit');
                    Route::put('privasi', [PrivacyController::class, 'update'])->name('privacy.update');
                    Route::delete('privasi', [PrivacyController::class, 'reset'])->name('privacy.reset');
                });

                // Pesan Masuk (B3l): Super Admin dan Admin
                Route::middleware('admin.role:super_admin,admin')->prefix('pesan')->name('messages.')->group(function () {
                    Route::get('/', [MessageController::class, 'index'])->name('index');
                    Route::get('ekspor', [MessageController::class, 'export'])->middleware('throttle:10,1')->name('export');
                    Route::get('{message}', [MessageController::class, 'show'])->whereNumber('message')->name('show');
                    Route::patch('{message}/dibaca', [MessageController::class, 'toggle'])->whereNumber('message')->name('toggle');
                    Route::delete('{message}', [MessageController::class, 'destroy'])->whereNumber('message')->name('destroy');
                });

                // SEO Halaman (B3n bagian 2b): Super Admin dan Admin
                Route::middleware('admin.role:super_admin,admin')->prefix('seo-halaman')->name('seo.')->group(function () {
                    Route::get('/', [SeoPageController::class, 'edit'])->name('edit');
                    Route::put('/', [SeoPageController::class, 'update'])->name('update');
                    Route::delete('/', [SeoPageController::class, 'reset'])->name('reset');
                });

                // Redirect 301 (B3n): Super Admin dan Admin
                Route::middleware('admin.role:super_admin,admin')->prefix('redirect')->name('redirects.')->group(function () {
                    Route::get('/', [RedirectController::class, 'index'])->name('index');
                    Route::get('tambah', [RedirectController::class, 'create'])->name('create');
                    Route::post('/', [RedirectController::class, 'store'])->name('store');
                    Route::get('impor', [RedirectController::class, 'importForm'])->name('import');
                    Route::post('impor', [RedirectController::class, 'importStore'])->middleware('throttle:10,1')->name('import.store');
                    Route::get('{redirect}/ubah', [RedirectController::class, 'edit'])->whereNumber('redirect')->name('edit');
                    Route::put('{redirect}', [RedirectController::class, 'update'])->whereNumber('redirect')->name('update');
                    Route::patch('{redirect}/status', [RedirectController::class, 'toggle'])->whereNumber('redirect')->name('toggle');
                    Route::delete('{redirect}', [RedirectController::class, 'destroy'])->whereNumber('redirect')->name('destroy');
                });

                // Halaman Kustom (B3m, QA1): Super Admin dan Admin
                Route::middleware('admin.role:super_admin,admin')->prefix('halaman-kustom')->name('custompages.')->group(function () {
                    Route::get('/', [CustomPageController::class, 'index'])->name('index');
                    Route::get('tambah', [CustomPageController::class, 'create'])->name('create');
                    Route::post('/', [CustomPageController::class, 'store'])->name('store');
                    Route::get('{page}/ubah', [CustomPageController::class, 'edit'])->whereNumber('page')->name('edit');
                    Route::put('{page}', [CustomPageController::class, 'update'])->whereNumber('page')->name('update');
                    Route::patch('{page}/status', [CustomPageController::class, 'toggle'])->whereNumber('page')->name('toggle');
                    Route::delete('{page}', [CustomPageController::class, 'destroy'])->whereNumber('page')->name('destroy');
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

                // Klien & Mitra (B3h): Super Admin dan Admin (Editor tidak punya akses)
                Route::middleware('admin.role:super_admin,admin')->group(function () {
                    Route::prefix('klien')->name('clients.')->group(function () {
                        Route::get('/', [ClientController::class, 'index'])->name('index');
                        Route::get('tambah', [ClientController::class, 'create'])->name('create');
                        Route::post('/', [ClientController::class, 'store'])->name('store');
                        Route::post('unggah-massal', [ClientController::class, 'bulk'])->middleware('throttle:20,1')->name('bulk');
                        Route::post('urutan', [ClientController::class, 'sort'])->middleware('throttle:60,1')->name('sort');
                        Route::get('{client}/ubah', [ClientController::class, 'edit'])->whereNumber('client')->name('edit');
                        Route::put('{client}', [ClientController::class, 'update'])->whereNumber('client')->name('update');
                        Route::patch('{client}/status', [ClientController::class, 'toggle'])->whereNumber('client')->name('toggle');
                        Route::patch('{client}/beranda', [ClientController::class, 'home'])->whereNumber('client')->name('home');
                        Route::delete('{client}', [ClientController::class, 'destroy'])->whereNumber('client')->name('destroy');
                    });

                    Route::prefix('kategori-klien')->name('clientcat.')->group(function () {
                        Route::get('/', [ClientCategoryController::class, 'index'])->name('index');
                        Route::get('tambah', [ClientCategoryController::class, 'create'])->name('create');
                        Route::post('/', [ClientCategoryController::class, 'store'])->name('store');
                        Route::get('{category}/ubah', [ClientCategoryController::class, 'edit'])->whereNumber('category')->name('edit');
                        Route::put('{category}', [ClientCategoryController::class, 'update'])->whereNumber('category')->name('update');
                        Route::delete('{category}', [ClientCategoryController::class, 'destroy'])->whereNumber('category')->name('destroy');
                    });
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

                    // Pengaturan Sistem (B3p): hanya Super Admin
                    Route::get('pengaturan-sistem', [SystemSettingsController::class, 'edit'])->name('system.edit');
                    Route::put('pengaturan-sistem', [SystemSettingsController::class, 'update'])->name('system.update');
                });
            });
        });
    });