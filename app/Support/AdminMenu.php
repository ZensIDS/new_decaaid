<?php
namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Menu sidebar admin, disaring menurut peran (K29 / QA5):
 *  - Super Admin : semua + Pengguna + Pengaturan Sistem
 *  - Admin       : semua konten + pesan masuk + pengaturan situs
 *  - Editor      : Berita, Proyek, Penghargaan
 *
 * Item dengan 'route' => null tampil nonaktif berlabel "Segera" sampai modulnya dibuat.
 * Saat modul jadi, cukup isi 'route' dan 'active' (pola nama rute).
 */
class AdminMenu
{
    protected const S = User::ROLE_SUPER_ADMIN;
    protected const A = User::ROLE_ADMIN;
    protected const E = User::ROLE_EDITOR;

    /** @return array<int, array{title: ?string, items: array<int, array<string, mixed>>}> */
    public static function for(User $user): array
    {
        $S      = self::S;
        $A      = self::A;
        $E      = self::E;
        $semua  = [$S, $A, $E];
        $kelola = [$S, $A];

        $groups = [
            ['title' => null, 'items' => [
                self::item('Dashboard', 'squares', 'admin.dashboard', 'admin.dashboard', $semua),
            ]],
            ['title' => 'Konten', 'items' => [
                self::item('Beranda', 'home', null, 'admin.home.*', $kelola),
                self::item('Tentang Kami', 'book', null, 'admin.about.*', $kelola),
                self::item('Pilar & Brand', 'cube', null, 'admin.pillars.*', $kelola),
                self::item('Proyek', 'briefcase', null, 'admin.projects.*', $semua),
                self::item('Klien & Mitra', 'users', null, 'admin.clients.*', $kelola),
                self::item('Penghargaan', 'trophy', null, 'admin.awards.*', $semua),
                self::item('Berita', 'newspaper', 'admin.news.index', 'admin.news.*', $semua),
                self::item('Kategori Berita', 'tag', 'admin.newscat.index', 'admin.newscat.*', $kelola),
                self::item('Keberlanjutan', 'heart', null, 'admin.sustainability.*', $kelola),
                self::item('Karir', 'academic', null, 'admin.careers.*', $kelola),
                self::item('Halaman Legal', 'document', null, 'admin.pages.*', $kelola),
            ]],
            ['title' => 'Interaksi', 'items' => [
                self::item('Pesan Masuk', 'mail', null, 'admin.messages.*', $kelola),
            ]],
            ['title' => 'Situs', 'items' => [
                self::item('Media Library', 'photo', null, 'admin.media.*', $kelola),
                self::item('Menu', 'menu', null, 'admin.menus.*', $kelola),
                self::item('SEO & Redirect', 'link', null, 'admin.seo.*', $kelola),
                self::item('Pengaturan Situs', 'sliders', null, 'admin.settings.*', $kelola),
            ]],
            ['title' => 'Sistem', 'items' => [
                self::item('Pengguna', 'user-circle', 'admin.users.index', 'admin.users.*', [$S]),
                self::item('Log Aktivitas', 'clock', 'admin.logs.index', 'admin.logs.*', [$S]),
                self::item('Pengaturan Sistem', 'shield', null, 'admin.system.*', [$S]),
            ]],
        ];

        $result = [];

        foreach ($groups as $group) {
            $items = array_values(array_filter(
                $group['items'],
                fn(array $item) => in_array($user->role, $item['roles'], true)
            ));

            if ($items) {
                $result[] = ['title' => $group['title'], 'items' => $items];
            }
        }

        return $result;
    }

    /** @param array<int, string> $roles */
    protected static function item(string $label, string $icon, ?string $route, string $active, array $roles): array
    {
        $ready = $route !== null && Route::has($route);

        return [
            'label'  => $label,
            'icon'   => $icon,
            'url'    => $ready ? route($route) : null,
            'soon'   => ! $ready,
            'active' => $active,
            'roles'  => $roles,
        ];
    }
}
