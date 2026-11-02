<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\View\View;

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
            ['label' => 'Berita Draf', 'value' => 0, 'icon' => 'newspaper', 'tone' => 'amber', 'note' => 'Menunggu terbit (modul B3d)'],
            ['label' => 'Terjemahan Belum Ditinjau', 'value' => 0, 'icon' => 'translate', 'tone' => 'violet', 'note' => 'Status Otomatis (modul B5)'],
            ['label' => 'Item Dummy Tersisa', 'value' => count($this->checklist()), 'icon' => 'warning', 'tone' => 'red', 'note' => 'Wajib diganti sebelum go-live'],
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
            ['label' => 'Tambah Berita', 'icon' => 'newspaper'],
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
            ['label' => 'Brand Commercial Technology dan 10 logo brand baru', 'done' => false],
            ['label' => 'Misi dan foto Perjalanan Kami per tahun', 'done' => false],
            ['label' => 'Foto dummy: hero, tentang, berita, proyek, penghargaan', 'done' => false],
            ['label' => 'Proyek, klien, dan penghargaan contoh', 'done' => false],
        ];
    }
}