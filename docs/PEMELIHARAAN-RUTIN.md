# Pemeliharaan Rutin DECAA.ID (Tahap E: Pasca Live)

Dokumen untuk tim teknis setelah situs tayang di `decaa.id`: memantau situs, jadwal perawatan, uji pemulihan cadangan, evaluasi 2-4 minggu, dan rencana naik versi Laravel. Bagian bertanda **(sesuaikan)** bergantung hosting produksi yang belum diketahui pengembang; cocokkan lalu kabari bila tidak sesuai.

Dokumen terkait: `RENCANA-GO-LIVE-DAN-ROLLBACK.md` (hari H dan mundur), `PERFORMA-DAN-SERVER.md` (cache, OPcache, Lighthouse), `PANDUAN-ADMIN.md` (untuk pengelola konten).

---

## 1. Monitoring uptime

**Titik pemeriksaan: `GET /health`** (sudah ada di aplikasi). Jawaban:

```json
{"status":"ok","checks":{"database":true,"storage":true},"maintenance":false}
```

- **200** bila aplikasi, database, dan folder `storage/logs` sehat; **503** bila salah satu gagal (`"status":"error"`).
- Tidak membocorkan versi, lingkungan, atau pesan galat. Dibatasi 60 permintaan per menit per IP, jadi interval pemeriksaan **minimal 1 menit** (disarankan 5 menit).
- **Tetap menjawab saat mode pemeliharaan** dengan `"maintenance":true` (status HTTP tetap 200 bila sehat). Jadi pemeliharaan terencana tidak memicu alarm "situs mati"; kalau mau diberi tahu, buat peringatan terpisah pada kata `"maintenance":true`.

**Monitor yang dibuat (alat gratis: UptimeRobot, Better Stack, atau Uptime Kuma di server sendiri):**

| Monitor | Alamat | Cek | Interval |
|---|---|---|---|
| Aplikasi sehat | `https://decaa.id/health` | HTTP 200 **dan** teks `"status":"ok"` | 5 menit |
| Beranda | `https://decaa.id/id` | HTTP 200 dan sebuah kata khas beranda (mis. `DECAA`) | 5 menit |
| Sitemap | `https://decaa.id/sitemap.xml` | HTTP 200 dan teks `<urlset` | 30 menit |
| Sertifikat HTTPS | `decaa.id` | peringatan 30, 14, dan 7 hari sebelum kedaluwarsa | harian |
| Domain | `decaa.id` | peringatan kedaluwarsa domain (bila alatnya mendukung) | harian |

**Penerima peringatan (sesuaikan):** minimal dua orang dan dua jalur (email + WhatsApp/Telegram). Catat siapa yang bertanggung jawab di tabel kontak `RENCANA-GO-LIVE-DAN-ROLLBACK.md` bagian 7.

**Saat alarm berbunyi:** (1) buka `/health` di peramban; (2) bila `database:false`, cek layanan MySQL dan kredensial `.env`; bila `storage:false`, cek izin folder `storage/`; (3) lihat log (bagian 2); (4) bila tidak teratasi dalam 30 menit dan penyebabnya rilis terakhir, pertimbangkan mundur (rencana rollback).

---

## 2. Monitoring galat

Aplikasi mencatat galat ke `storage/logs` (di luar folder publik).

**Pengaturan produksi (sesuaikan di `.env`):**

- `LOG_CHANNEL=daily` (satu berkas per hari, **14 hari** terakhir disimpan otomatis).
- `LOG_LEVEL=warning` agar log tidak membengkak oleh pesan informasi.
- `APP_DEBUG=false` (diperiksa `php artisan site:check --production`).

**Tinjau seminggu sekali** (perintah dari folder proyek):

```bash
# jumlah galat per hari
grep -c "\.ERROR" storage/logs/laravel-*.log
# galat terbaru
grep -h "\.ERROR" storage/logs/laravel-$(date +%F).log | tail -20
```

**Galat yang wajar dicatat aplikasi** (bukan berarti situs rusak, tetapi perlu ditindaklanjuti bila sering): kegagalan kirim email notifikasi Kontak, email "lupa password", dan balasan otomatis (SMTP bermasalah); kegagalan penyedia terjemahan otomatis; gagal membuat varian gambar `srcset`; kegagalan cadangan (`site:backup`). Pesan Kontak tetap tersimpan di admin walaupun email gagal, jadi **tinjau juga Pesan Masuk** untuk status "Notifikasi email: Gagal".

**Opsional nanti:** layanan pelacak galat (mis. Sentry) memberi peringatan langsung dan konteks galat. Belum dipasang; putuskan setelah 1-2 bulan live, bila log mingguan terasa kurang.

---

## 3. Jadwal rutin

| Kapan | Tugas | Cara |
|---|---|---|
| **Harian (otomatis)** | Cadangan 03:00, pembersihan pesan lama 02:00 | Cron `* * * * * php /path/artisan schedule:run` harus aktif **(sesuaikan)**; periksa lewat `php artisan site:check` (bagian Jadwal dan cadangan) |
| **Mingguan** | Tinjau log galat; baca Pesan Masuk; lihat monitor uptime (riwayat gangguan) | Bagian 1 dan 2 |
| **Mingguan** | Cek cadangan terbaru ada dan **disalin ke luar server** | `ls -lh storage/app/backups`; unduh/sinkronkan folder itu ke penyimpanan lain **(sesuaikan)**; salinan di server yang sama bukan cadangan sesungguhnya |
| **Mingguan** | Search Console: laporan Pengindeksan dan galat 404 (setelah verifikasi di Pengaturan Sistem) | Perbaiki dengan Redirect di admin SEO & Redirect |
| **Bulanan** | Pemeriksaan kesehatan penuh | `php artisan site:check --production` dan `php artisan site:links` (semua harus tanpa GAGAL) |
| **Bulanan** | Pembaruan dependensi dan keamanan | `composer audit`, `composer outdated --direct`, `npm audit`; terapkan pembaruan **patch/minor** di staging dulu, jalankan pemeriksaan di atas, baru produksi |
| **Bulanan** | Lighthouse mobile (3 kali, median) beranda dan satu halaman dalam | Cara di `PERFORMA-DAN-SERVER.md` bagian 6; target >= 90, LCP < 2,5 dtk, CLS < 0,1 |
| **Bulanan** | Tinjau pengguna admin | Menu Pengguna: nonaktifkan akun orang yang sudah tidak bertugas; pastikan semua Super Admin/Admin berstatus **2FA Aktif** |
| **Bulanan** | Tinjau Log Aktivitas untuk tindakan yang tidak dikenali | Menu Log Aktivitas |
| **Bulanan** | Kesegaran konten | Berita terbaru ada, Proyek/Penghargaan baru ditambahkan, tidak ada teks contoh (Dummy) tersisa |
| **Triwulan** | **Uji pemulihan cadangan** | Bagian 4 |
| **Triwulan** | Tinjau Kebijakan Privasi dan tanggal pembaruannya; uji alur Lupa password dan 2FA dengan akun uji | Panduan Admin |
| **Tahunan** | Perpanjang domain dan sertifikat (bila tidak otomatis); putar kata sandi database dan email. **Jangan mengganti `APP_KEY` secara rutin**: sesi dan data terenkripsi (termasuk kunci 2FA pengguna) menjadi tidak terbaca; ganti hanya bila kunci bocor, dan siapkan reset 2FA semua pengguna | Catat tanggalnya di kalender tim |
| **Tahunan** | Tinjau rencana naik versi Laravel | Bagian 6 |

---

### 3a. Terjemahan massal berita (sekali jalan setelah impor, atau bila ada berita lama tanpa EN)

Berita hasil impor WordPress hanya berbahasa Indonesia; pengunjung EN melihat teks Indonesia. Perintah `translate:news` mengisi bahasa yang **kosong** memakai penyedia terjemahan (butuh `TRANSLATE_DRIVER` dan kuncinya di `.env`, lalu `php artisan config:clear`).

```bash
php artisan translate:news --dry-run            # daftar calon + perkiraan karakter, tidak memanggil penyedia
php artisan translate:news --limit=10           # terjemahkan 10 berita terbaru yang membutuhkan
php artisan translate:news --limit=0            # semua calon (hati-hati biaya)
php artisan translate:news --id=12 --id=15      # berita tertentu
php artisan translate:news --status=all         # termasuk Draf (bawaan: Terbit dan Terjadwal)
php artisan translate:news --include-reviewed   # termasuk yang berstatus Ditinjau (bawaan: dilewati)
```

- **Mulai dengan `--dry-run`** untuk melihat jumlah calon dan perkiraan karakter (dasar perhitungan biaya/kuota penyedia), lalu coba `--limit=3` dan periksa hasilnya di admin sebelum menjalankan sisanya.
- Hanya kolom **kosong** yang diisi; tulisan yang sudah ada tidak pernah ditimpa. Berita yang diterjemahkan ditandai **Otomatis (belum ditinjau)**, dan halaman publik menampilkan catatan "belum ditinjau" sampai Anda mengubahnya ke **Ditinjau**.
- Berjalan serempak dalam kelompok kecil dengan jeda 1 detik (`--sleep`), tanpa queue. Bila 3 galat beruntun (kuota habis, kunci salah), perintah berhenti sendiri; ulangi setelah diperbaiki, berita yang sudah selesai tidak diproses lagi.
- Setelah selesai, **tinjau berita terpenting dulu** (yang paling banyak dibaca) di admin Berita. Ringkasan satu baris masuk Log Aktivitas.

---

## 4. Uji pemulihan cadangan (triwulan, di staging)

Cadangan yang belum pernah dipulihkan belum bisa dipercaya. Lakukan di **staging**, bukan produksi.

1. Ambil cadangan terbaru dari penyimpanan **luar server** (bukan dari `storage/app/backups` di server produksi): satu `db-AAAAMMDD-JJMMDD.sql.gz` dan satu `media-AAAAMMDD-JJMMDD.zip`.
2. Buat database kosong baru di staging dan arahkan `.env` staging ke database itu.
3. Pulihkan database:
   ```bash
   gunzip -c db-AAAAMMDD-JJMMDD.sql.gz | mysql -u USER -p NAMA_DATABASE_KOSONG
   ```
4. Pulihkan media: ekstrak `media-*.zip` ke folder sementara dan bandingkan jumlah berkas dengan produksi; salin ke `storage/app/public` dan `public/wp-uploads` staging bila perlu (`php artisan storage:link` bila tautan belum ada).
5. `php artisan optimize:clear`, lalu `php artisan site:check` (tabel inti lengkap, tidak ada migration tertunda) dan `php artisan site:links --no-links --limit=30`.
6. Buka admin: Super Admin bisa masuk (2FA tetap berlaku), jumlah Berita, Proyek, dan Pesan Masuk sesuai, gambar tampil.
7. Catat tanggal uji, ukuran berkas cadangan, waktu pemulihan, dan masalahnya. **Cadangan tidak dienkripsi**, jadi simpan di tempat yang akses-nya dibatasi.

Berkas cadangan lama otomatis dihapus (14 terakhir per jenis). Untuk arsip bulanan, salin satu cadangan tiap bulan ke penyimpanan jangka panjang **(sesuaikan)**.

---

## 5. Evaluasi 2-4 minggu setelah live

Jadwalkan satu pertemuan singkat pada minggu ke-2 dan ke-4. Bahan:

| Aspek | Yang dilihat | Tindakan bila buruk |
|---|---|---|
| Ketersediaan | Riwayat uptime (% dan insiden), waktu respons `/health` | Periksa server, cache, dan log; pertimbangkan Redis hanya bila memenuhi pemicu di `PERFORMA-DAN-SERVER.md` bagian 4 |
| Kecepatan | Lighthouse mobile; data lapangan Core Web Vitals di Search Console (butuh beberapa minggu data) | Tabel temuan dan tindakan di `PERFORMA-DAN-SERVER.md` bagian 6 |
| Pengindeksan | Search Console: halaman terindeks vs sitemap, galat 404, "ditemukan tetapi belum diindeks" | Kirim ulang sitemap; tambah Redirect untuk URL WordPress lama yang masih dicari |
| Redirect | Admin SEO & Redirect: jumlah pemakaian per aturan, aturan yang tidak pernah terpakai | Hapus aturan mati; tambah aturan bagi 404 yang sering |
| Trafik | Analitik (GA4/Plausible/Umami): halaman teratas, bahasa (ID/EN), perangkat | Sesuaikan konten dan Menu; pastikan versi EN halaman teratas sudah ditinjau |
| Konten | Pesan Masuk: jenis pertanyaan; berita terbit sejak live | Perbaiki halaman yang memicu banyak pertanyaan |
| Keamanan | Log Aktivitas, percobaan login gagal (log), status 2FA semua admin | Cabut akun tak dikenal; ganti kata sandi bila perlu |
| Email | Status "Notifikasi email" di Pesan Masuk; SPF/DKIM/DMARC `decaa.id` | Perbaiki DNS/SMTP bila ada yang Gagal atau masuk spam |

---

## 6. Rencana naik versi Laravel

**Kondisi sekarang:** aplikasi memakai **Laravel 9 dan PHP 8.3** (keputusan K1, risiko EOL diterima). Laravel 9 sudah tidak menerima perbaikan apa pun (keamanan berakhir 6 Februari 2024) dan secara resmi mendukung PHP 8.0-8.2, jadi PHP 8.3 belum teruji resmi untuk versi ini.

**Kebijakan dukungan per 5 Nov 2026** (sumber: laravel.com/docs/master/releases dan laravel-news.com/laravel-releases; **cek ulang saat akan mengerjakan**):

| Versi | PHP | Perbaikan bug sampai | Perbaikan keamanan sampai |
|---|---|---|---|
| 10 | 8.1 - 8.3 | Agu 2024 | Feb 2025 (**EOL**) |
| 11 | 8.2 - 8.4 | Sep 2025 | Mar 2026 (**EOL**) |
| 12 | 8.2 - 8.5 | 13 Agu 2026 (**selesai**) | 24 Feb 2027 |
| **13** | **8.3 - 8.5** | Q3 2027 | **17 Mar 2028** |
| 14 | 8.4+ | (perkiraan rilis Q1 2027) | Q1 2029 |

**Sasaran: Laravel 13.** PHP 8.3 di server sudah dalam rentang resminya, dan masa keamanannya paling panjang di antara yang tersedia. Jangan berhenti di 10, 11, atau 12 (semuanya sudah EOL atau segera EOL). Jangan langsung ke 14 saat rilis pertama; tunggu rilis minornya matang dan kebutuhan PHP 8.4 terpenuhi.

**Jalur:** naik **satu versi mayor per langkah** (9 > 10 > 11 > 12 > 13), tiap langkah di cabang sendiri dengan pengujian, mengikuti panduan "Upgrade Guide" resmi tiap versi. Alternatif berbayar: Laravel Shift yang mengotomatiskan sebagian besar perubahan dan membuka pull request per langkah.

**Kapan:** mulai perencanaan **setelah situs stabil 4-8 minggu pasca live**, bukan sebelum go-live (menghindari dua perubahan besar sekaligus). Targetkan selesai dalam 1-2 kuartal pertama setelah live. Sampai saat itu, mitigasi di Bagian 10 todolist berlaku: `composer audit` rutin, HTTPS dan security headers, rate limiting, dan WAF (mis. Cloudflare).

**Bagian aplikasi ini yang paling terdampak (periksa saat naik):**

| Bagian | Catatan |
|---|---|
| `app/Http/Kernel.php` (alias middleware `admin.*`, middleware global/grup web termasuk `SiteMaintenance`, `SecurityHeaders`) | Laravel 11 memindahkan konfigurasi middleware ke `bootstrap/app.php`; aplikasi yang naik biasanya boleh mempertahankan struktur lama, **cek panduan upgrade resmi** |
| `app/Providers/RouteServiceProvider.php` (pembatas laju bernama, pola rute) | Sama: pastikan `throttle:<nama>` (mis. `contact-form`, `health`) tetap berfungsi setelah tiap langkah |
| `app/Exceptions/Handler.php` (redirect lewat 404, tampilan error `errors/` dan `pages/errors/`) | Penanganan pengecualian berubah di Laravel 11; uji 404, 429, 503 |
| `app/Console/Kernel.php` (jadwal `messages:prune`, `site:backup`) | Penjadwalan pindah ke `routes/console.php` di Laravel 11 (struktur lama masih boleh) |
| `config/*.php`, `.env` | Beberapa kunci konfigurasi berubah antarversi; bandingkan dengan skeleton baru |
| Perintah sendiri (`site:check`, `site:links`, `site:backup`, impor, `admin:reset-2fa`) | Uji tiap perintah; `site:links` memakai kernel HTTP dalam proses |
| Autentikasi admin (login, 2FA TOTP buatan sendiri, lupa password lewat `password_resets`) | Pembaruan hashing/password broker; uji alur penuh |
| `Mail::raw` (Kontak, lupa password), pengaturan SMTP `tls` port 465 | Perilaku Symfony Mailer berubah antarversi |
| Paket di `composer.json` (mis. `laravel/sanctum`) | Tiap paket harus punya versi yang kompatibel; cek sebelum mulai. `composer.json` tidak ikut zip yang dikirim, jadi daftar paket perlu kamu tinjau sendiri |

**Daftar uji tiap langkah (di staging):**

1. `composer update` di cabang, `php artisan optimize:clear`, `php artisan migrate --pretend` (tinjau, jangan dijalankan di produksi sebelum lolos).
2. `php artisan site:check --production`: tidak ada GAGAL.
3. `php artisan site:links`: semua halaman sitemap dan tautan internal sehat.
4. Uji manual admin: masuk dengan 2FA, lupa password, tambah/ubah Berita sampai terbit, Media Library (unggah dan hapus), Redirect, Pengaturan Situs, Pesan Masuk (kirim satu dari form Kontak), mode pemeliharaan (nyalakan dan matikan), ekspor CSV.
5. Uji halaman publik ID dan EN, pencarian, sitemap, `robots.txt`, `/health`.
6. Periksa log (`storage/logs`) tidak memuat peringatan deprecation baru yang berulang.
7. Lighthouse sebelum dan sesudah: skor tidak turun.

**Rilis dan mundur:** sama seperti rilis biasa di `RENCANA-GO-LIVE-DAN-ROLLBACK.md` (cadangan database dan kode lebih dulu, jendela waktu sepi, `site:check` dan `site:links` sesudahnya). Setiap langkah mayor dirilis ke produksi sendiri-sendiri bila memungkinkan, jangan menumpuk beberapa versi dalam satu rilis.

---

## 7. Catatan perubahan dokumen

| Tanggal | Perubahan |
|---|---|
| 5 Nov 2026 | Dokumen dibuat bersama titik pemeriksaan `/health` (todolist v96, K93). Kebijakan dukungan Laravel dicek dari laravel.com pada tanggal itu. |
