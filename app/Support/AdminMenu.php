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
                self::item('Beranda', 'home', 'admin.home.edit', 'admin.home.*', $kelola),
                self::item('Tentang Kami', 'book', 'admin.about.edit', 'admin.about.*', $kelola),
                self::item('Pilar & Brand', 'cube', 'admin.pillars.index', 'admin.pillars.*', $kelola),
                self::item('Proyek', 'briefcase', 'admin.projects.index', 'admin.projects.*', $semua),
                self::item('Klien & Mitra', 'users', 'admin.clients.index', 'admin.clients.*', $kelola),
                self::item('Kategori Klien', 'tag', 'admin.clientcat.index', 'admin.clientcat.*', $kelola),
                self::item('Penghargaan', 'trophy', 'admin.awards.index', 'admin.awards.*', $semua),
                self::item('Berita', 'newspaper', 'admin.news.index', 'admin.news.*', $semua),
                self::item('Kategori Berita', 'tag', 'admin.newscat.index', 'admin.newscat.*', $kelola),
                self::item('Keberlanjutan', 'heart', 'admin.sustainability.edit', 'admin.sustainability.*', $kelola),
                self::item('Karir', 'academic', 'admin.careers.edit', 'admin.careers.*', $kelola),
                self::item('Halaman Legal', 'document', 'admin.pages.privacy.edit', 'admin.pages.*', $kelola),
                self::item('Halaman Kustom', 'document', 'admin.custompages.index', 'admin.custompages.*', $kelola),
            ]],
            ['title' => 'Interaksi', 'items' => [
                self::item('Pesan Masuk', 'mail', 'admin.messages.index', 'admin.messages.*', $kelola),
            ]],
            ['title' => 'Situs', 'items' => [
                self::item('Media Library', 'photo', 'admin.media.index', 'admin.media.*', $kelola),
                self::item('Menu', 'menu', 'admin.menus.edit', 'admin.menus.*', $kelola),
                self::item('SEO Halaman', 'search', 'admin.seo.edit', 'admin.seo.*', $kelola),
                self::item('SEO & Redirect', 'link', 'admin.redirects.index', 'admin.redirects.*', $kelola),
                self::item('Pengaturan Situs', 'sliders', 'admin.settings.edit', 'admin.settings.*', $kelola),
            ]],
            ['title' => 'Sistem', 'items' => [
                self::item('Pengguna', 'user-circle', 'admin.users.index', 'admin.users.*', [$S]),
                self::item('Log Aktivitas', 'clock', 'admin.logs.index', 'admin.logs.*', [$S]),
                self::item('Pengaturan Sistem', 'shield', 'admin.system.edit', 'admin.system.*', [$S]),
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