<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\News;
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
            ['label' => 'Pesan Baru', 'value' => 0, 'icon' => 'mail', 'tone' => 'brand', 'note' => 'Belum dibaca (modul B3l)'],
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
     * Sumber: "Daftar DUMMY yang harus diganti lewat admin sebelum go-live" di todolist.
     * Status 'done' dihitung otomatis dari data asli setelah modulnya ada.
     *
     * @return array<int, array{label: string, done: bool}>
     */
    protected function checklist(): array
    {
        return [
            ['label' => 'Telepon, WhatsApp, dan 6 URL sosial media', 'done' => false],
            ['label' => 'Chairman Message: nama, foto, isi pesan', 'done' => false],
            ['label' => 'Teks Beranda: 4 nilai perusahaan dan blok Keberlanjutan', 'done' => false],
            ['label' => 'Program Komunitas Peduli Digital (4 contoh)', 'done' => false],
            ['label' => 'Kebijakan Privasi (wajib ditinjau pihak hukum)', 'done' => false],
            ['label' => 'Brand Commercial Technology dan 10 logo brand baru', 'done' => $this->brandsReady()],
            ['label' => 'Misi dan foto Perjalanan Kami per tahun', 'done' => false],
            ['label' => 'Foto dummy: hero, tentang, berita, proyek, penghargaan', 'done' => false],
            ['label' => 'Proyek contoh (ganti dengan proyek asli)', 'done' => $this->projectsReady()],
            ['label' => 'Klien dan penghargaan contoh', 'done' => false],
        ];
    }
}