<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\News;
use App\Models\Award;
use App\Models\PageContent;
use App\Models\Project;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    /**
     * Kartu ringkasan masih DUMMY (angka asli menyusul bersama modulnya: pesan B3l,
     * berita B3d, terjemahan B5). Aktivitas Terbaru sejak BA3 dibaca dari tabel activity_logs.
     */
    public function __invoke(): View
    {
        $stats = [
            ['label' => 'Pesan Baru', 'value' => $this->unreadMessages(), 'icon' => 'mail', 'tone' => 'brand', 'note' => 'Belum dibaca'],
            ['label' => 'Berita Draf', 'value' => $this->draftCount(), 'icon' => 'newspaper', 'tone' => 'amber', 'note' => 'Menunggu terbit'],
            ['label' => 'Terjemahan Belum Ditinjau', 'value' => 0, 'icon' => 'translate', 'tone' => 'violet', 'note' => 'Status Otomatis (modul B5)'],
            ['label' => 'Item Dummy Tersisa', 'value' => count(array_filter($this->checklist(), fn (array $item) => ! $item['done'])), 'icon' => 'warning', 'tone' => 'red', 'note' => 'Wajib diganti sebelum go-live'],
        ];

        $activities = ActivityLog::query()
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (ActivityLog $log) => [
                'who' => $log->user_name ?: 'Sistem',
                'what' => $log->description,
                'at' => $log->created_at,
            ])
            ->all();

        $shortcuts = [
            ['label' => 'Tambah Berita', 'icon' => 'newspaper', 'url' => route('admin.news.create')],
            ['label' => 'Tambah Proyek', 'icon' => 'briefcase'],
            ['label' => 'Unggah Media', 'icon' => 'photo'],
        ];

        return view('pages.admin.dashboard', [
            'stats' => $stats,
            'activities' => $activities,
            'shortcuts' => $shortcuts,
            'checklist' => $this->checklist(),
        ]);
    }

    protected function unreadMessages(): int
    {
        try {
            return Schema::hasTable('contact_messages') ? \App\Models\ContactMessage::unread()->count() : 0;
        } catch (Throwable) {
            return 0;
        }
    }

    protected function draftCount(): int
    {
        return Schema::hasTable('news') ? News::where('status', News::STATUS_DRAFT)->count() : 0;
    }

    /**
     * B3c: selesai bila brand sudah ada di database, tidak ada lagi yang berstatus Dummy,
     * dan setiap brand yang tampil punya berkas logo.
     */
    protected function brandsReady(): bool
    {
        try {
            if (! Schema::hasTable('brands') || ! Brand::exists() || Brand::where('is_dummy', true)->exists()) {
                return false;
            }

            foreach (Brand::where('is_visible', true)->pluck('logo') as $logo) {
                if (! $logo || ! file_exists(public_path($logo))) {
                    return false;
                }
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * B3g: selesai bila proyek sudah ada di database dan tidak ada lagi yang berstatus Dummy.
     */
    protected function projectsReady(): bool
    {
        try {
            return Schema::hasTable('projects') && Project::exists() && ! Project::where('is_dummy', true)->exists();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * B3i: selesai bila penghargaan sudah ada di database dan tidak ada lagi yang berstatus Dummy.
     */
    protected function awardsReady(): bool
    {
        try {
            return Schema::hasTable('awards') && Award::exists() && ! Award::where('is_dummy', true)->exists();
        } catch (Throwable) {
            return false;
        }
    }

    /** B3j: selesai bila halaman Keberlanjutan pernah disimpan lewat admin. */
    protected function sustainabilityReady(): bool
    {
        try {
            return Schema::hasTable('page_contents') && PageContent::where('page', 'sustainability')->exists();
        } catch (Throwable) {
            return false;
        }
    }

    /** B3a: selesai bila Pengaturan Situs pernah disimpan dengan telepon dan WhatsApp terisi. */
    protected function siteSettingsReady(): bool
    {
        try {
            if (! Schema::hasTable('page_contents')) {
                return false;
            }

            $c = PageContent::where('page', 'site_settings')->value('content');

            return is_array($c) && filled($c['phone'] ?? null) && filled($c['whatsapp_number'] ?? null);
        } catch (Throwable) {
            return false;
        }
    }

    /** B3e: selesai bila Beranda pernah disimpan lewat admin (teks nilai perusahaan dan poin Keberlanjutan ikut tersimpan). */
    protected function homeReady(): bool
    {
        try {
            return Schema::hasTable('page_contents') && PageContent::where('page', 'home')->exists();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * B3f: selesai bila Tentang Kami pernah disimpan lewat admin. Dengan $chairman = true, nama Chairman
     * juga harus sudah bukan nama contoh ("Nama Chairman"); foto menunggu Media Library (B3b).
     */
    protected function aboutReady(bool $chairman = false): bool
    {
        try {
            if (! Schema::hasTable('page_contents')) {
                return false;
            }

            $content = PageContent::where('page', 'about')->value('content');

            if (! is_array($content)) {
                return false;
            }

            if ($chairman) {
                $name = trim((string) data_get($content, 'profile.chairman.name', ''));

                return $name !== '' && $name !== 'Nama Chairman';
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /** B3k: selesai bila halaman Karir pernah disimpan lewat admin. */
    protected function careersReady(): bool
    {
        try {
            return Schema::hasTable('page_contents') && PageContent::where('page', 'careers')->exists();
        } catch (Throwable) {
            return false;
        }
    }

    /** B3m: selesai bila Kebijakan Privasi pernah disimpan lewat admin (tinjauan hukum tetap tanggung jawab pemilik). */
    protected function privacyReady(): bool
    {
        try {
            return Schema::hasTable('page_contents') && PageContent::where('page', 'privacy')->exists();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Sumber: "Daftar DUMMY yang harus diganti lewat admin sebelum go-live" di todolist.
     * Status 'done' dihitung otomatis dari data asli setelah modulnya ada.
     *
     * @return array<int, array{label: string, done: bool}>
     */
    protected function checklist(): array
    {
        return [
            ['label' => 'Telepon, WhatsApp, dan 6 URL sosial media', 'done' => $this->siteSettingsReady()],
            ['label' => 'Chairman Message: nama dan isi pesan (foto menunggu Media Library)', 'done' => $this->aboutReady(true)],
            ['label' => 'Teks Beranda: 4 nilai perusahaan dan blok Keberlanjutan', 'done' => $this->homeReady()],
            ['label' => 'Program Komunitas Peduli Digital (4 contoh)', 'done' => $this->sustainabilityReady()],
            ['label' => 'Teks halaman Karir (alasan bergabung dan alur seleksi)', 'done' => $this->careersReady()],
            ['label' => 'Kebijakan Privasi (wajib ditinjau pihak hukum)', 'done' => $this->privacyReady()],
            ['label' => 'Brand Commercial Technology dan 10 logo brand baru', 'done' => $this->brandsReady()],
            ['label' => 'Misi dan teks Perjalanan Kami (foto per tahun menunggu Media Library)', 'done' => $this->aboutReady()],
            ['label' => 'Foto dummy: hero, tentang, berita, proyek, penghargaan', 'done' => false],
            ['label' => 'Proyek contoh (ganti dengan proyek asli)', 'done' => $this->projectsReady()],
            ['label' => 'Penghargaan contoh (ganti dengan penghargaan asli)', 'done' => $this->awardsReady()],
        ];
    }
}