# Rencana Go-Live dan Rollback DECAA.ID

Dokumen kerja untuk pindah dari `new.decaa.id` (staging) ke `decaa.id` (produksi), beserta cara mundur bila ada masalah.
Perintah memakai `php artisan` dari folder aplikasi di server. Hal yang bergantung pada hosting ditandai **(sesuaikan)**.

## 0. Prinsip

1. **Situs WordPress lama tidak dihapus** sampai situs baru stabil minimal 2 minggu. Itu jalur mundur paling cepat.
2. Sebelum apa pun diubah: **cadangan** (kode, database, media) dan **catatan DNS** (nilai lama disimpan).
3. Turunkan **TTL DNS** ke 300 detik (5 menit) **minimal 24 jam sebelum** hari H, supaya perpindahan dan pemunduran cepat.
4. Pindahkan di jam sepi (mis. malam hari), dengan satu orang pemutus keputusan "lanjut atau mundur".
5. Rencana mundur diputuskan dengan **batas waktu**: bila masalah belum teratasi dalam 30 menit dan menyangkut halaman utama, form Kontak, atau admin, mundur.

## 1. Satu minggu sebelum

- [ ] Staging lolos `php artisan site:check --production` tanpa FAIL (WARN dipahami dan dicatat).
- [ ] Semua DUMMY di todolist sudah diganti lewat admin (telepon, WhatsApp, sosmed, Chairman, foto, berita contoh, Kebijakan Privasi ditinjau pihak hukum).
- [ ] Impor WordPress selesai dan diperiksa sampel (jumlah berita, gambar dalam isi).
- [ ] Peta redirect 301 URL lama ke baru sudah dimuat (`php artisan redirects:import-wp --dry-run`, lalu tanpa `--dry-run`) dan diuji untuk 20 URL lama teratas.
- [ ] Email Kontak diuji dari staging dan **SPF, DKIM, DMARC** `decaa.id` benar (kirim ke Gmail dan satu alamat lain; cek tidak masuk spam).
- [ ] Semua akun Admin dan Super Admin memakai verifikasi 2 langkah; kode pemulihan disimpan pemiliknya.
- [ ] Uji lintas browser dan HP (Chrome, Safari iOS, Firefox, Edge) dan uji kecepatan (PageSpeed) tercatat.
- [ ] Turunkan TTL DNS (lihat prinsip 3).

## 2. H-1

- [ ] `php artisan site:backup` di **staging** dan simpan salinan di luar server.
- [ ] Cadangan **penuh situs WordPress lama** (berkas + database) tersimpan di luar server hosting lama.
- [ ] Catat nilai DNS saat ini (A, AAAA, CNAME, MX, TXT) sebagai tangkapan layar atau berkas. **MX dan TXT email tidak boleh berubah.**
- [ ] Siapkan `.env` produksi (lihat bagian 3) dan sertifikat HTTPS untuk `decaa.id` dan `www.decaa.id`.
- [ ] Beri tahu tim: jam mulai, siapa memutuskan mundur, siapa memegang DNS dan server.

## 3. Hari H: urutan langkah

1. **Bekukan konten**: pemeliharaan dinyalakan di staging lewat Pengaturan Sistem (admin tetap bisa masuk) bila masih ada yang mengedit.
2. **Cadangan terakhir** staging: `php artisan site:backup`.
3. **Siapkan produksi** (server baru atau folder baru; WordPress lama tetap melayani `decaa.id`):
   - Salin kode, `composer install --no-dev --optimize-autoloader`, `npm run build` (atau salin `public/build`).
   - `.env` produksi: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://decaa.id`, `APP_KEY` dan kata sandi database **baru**, `SITE_NOINDEX=false`, `MAIL_MAILER=smtp` (+ host, port 465/tls, akun `info@decaa.id`), `SECURITY_HSTS=false` dulu.
   - Impor database dan media dari cadangan staging (`.sql.gz` dan zip media), `php artisan storage:link`, `php artisan migrate --force`.
   - `php artisan optimize:clear`, lalu `php artisan config:cache`, `route:cache`, `view:cache`.
   - Cron: `* * * * * php artisan schedule:run` (jadwal cadangan dan pembersihan pesan).
4. **Uji produksi sebelum DNS berpindah** (lewat berkas `hosts` di komputer penguji, atau alamat sementara): `php artisan site:check --production` tanpa FAIL; buka beranda, satu halaman tiap menu, berita, kirim form Kontak (email masuk), login admin + 2FA, unggah satu gambar.
5. **Pindahkan DNS** `decaa.id` dan `www.decaa.id` ke server baru (A/AAAA atau CNAME) **(sesuaikan)**. Pastikan `www` dialihkan ke satu alamat utama.
6. **Uji asap setelah DNS** (lihat bagian 4).
7. Bila lolos: aktifkan `SECURITY_HSTS=true` **setelah** HTTPS terbukti stabil (beberapa jam), lalu `php artisan config:cache`.
8. Kirim `sitemap.xml` di Search Console dan Bing Webmaster; pantau 1-2 minggu.

## 4. Uji asap (setelah DNS, 15 menit pertama)

- [ ] `https://decaa.id/` terbuka, tanpa peringatan sertifikat; `http://` dialihkan ke `https://`; `www` dialihkan.
- [ ] `/id` dan `/en` tampil; pengalih bahasa bekerja.
- [ ] Halaman Kontak: kirim pesan uji, email masuk ke `info@decaa.id`, pesan muncul di `/admin/pesan`.
- [ ] 10 URL lama WordPress (berita populer, `/wp-content/...` bila ada) mengalihkan 301 ke halaman baru yang benar.
- [ ] `/sitemap.xml` dan `/robots.txt` benar (robots tidak melarang semua; sitemap memakai alamat `https://decaa.id`).
- [ ] Sumber halaman: **tidak ada** `noindex`; canonical memakai `https://decaa.id`.
- [ ] Login admin + 2FA, buka daftar Berita, unggah satu gambar.
- [ ] Log: `storage/logs/laravel.log` tidak berisi galat baru.

## 5. Rencana rollback

### 5.1 Pemicu (mundur bila salah satu terjadi dan tidak selesai dalam 30 menit)

- Situs tidak terbuka atau galat 500 di halaman utama.
- Form Kontak tidak mengirim atau email tidak sampai.
- Admin tidak bisa masuk (dan `admin:reset-2fa` tidak menyelesaikan).
- Kehilangan atau kerusakan data (berita atau gambar hilang).
- Sertifikat HTTPS bermasalah yang tidak bisa diperbaiki cepat.

### 5.2 Mundur ke situs lama (tercepat)

1. Kembalikan DNS `decaa.id` dan `www.decaa.id` ke **nilai lama yang dicatat** pada H-1. Dengan TTL 5 menit, pengunjung kembali dalam sekitar 5-15 menit.
2. Pastikan WordPress lama melayani `decaa.id` dengan benar (beranda, satu artikel, wp-admin).
3. Di situs baru (yang kini tidak diakses publik): nyalakan Mode Pemeliharaan atau `SITE_NOINDEX=true` agar tidak terindeks selama diperbaiki.
4. Bila sitemap sudah dikirim ke Search Console, biarkan; setelah situs baru siap, kirim ulang.
5. Catat penyebab, perbaiki di staging, jadwalkan ulang.

### 5.3 Mundur sebagian (situs baru tetap jalan)

| Masalah | Tindakan |
|---|---|
| Redirect salah atau berputar | `/admin/redirect`: nonaktifkan aturan yang salah (tidak perlu hapus) |
| Satu halaman rusak | Sembunyikan/tarik ke Draf di admin; Berita dan Halaman Kustom punya Riwayat Revisi |
| Kode baru bermasalah | Kembalikan folder kode rilis sebelumnya (simpan 1 rilis lama), lalu `php artisan optimize:clear` dan cache ulang |
| Migration bermasalah | Pulihkan database dari `.sql.gz` terakhir (lihat 5.4); jangan `migrate:rollback` di produksi tanpa cadangan |
| 2FA mengunci admin | `php artisan admin:reset-2fa email@contoh.id` |
| Email tidak terkirim | Cek `MAIL_*` dan SPF/DKIM; sementara pantau pesan di `/admin/pesan` (pesan tetap tersimpan) |
| HSTS bermasalah | `SECURITY_HSTS=false`, `config:cache` (browser yang sudah menyimpan HSTS menunggu masa berlakunya; karena itu HSTS dinyalakan belakangan) |

### 5.4 Memulihkan database dan media

```
# database (sesuaikan nama pengguna/database)
gunzip -c storage/app/backups/db-YYYYMMDD-HHMMSS.sql.gz | mysql -u USER -p NAMA_DATABASE

# media
unzip storage/app/backups/media-YYYYMMDD-HHMMSS.zip -d .
php artisan storage:link
php artisan optimize:clear
```

Pulihkan ke database **kosong atau bercadangan** lebih dulu bila ragu. Setelah pulih: `php artisan site:check --production`.
Uji pemulihan ini sebaiknya sudah pernah dicoba di staging **sebelum** hari H.

### 5.5 Mundur total sesudah beberapa hari

Berita dan pesan baru yang masuk di situs baru tidak ada di WordPress. Sebelum mundur: ekspor pesan Kontak (`/admin/pesan`, Ekspor) dan catat berita baru; setelah mundur, tindak lanjuti pesan dan pindahkan berita yang perlu.

## 6. Setelah go-live

- [ ] Hari 1, 3, 7: cek Search Console (cakupan, galat 404 dari URL lama; tambahkan redirect bagi yang penting).
- [ ] Minggu 1-2: pantau `storage/logs/laravel.log`, cadangan harian jalan (`site:check` melaporkan umur cadangan), uptime.
- [ ] Setelah 2 minggu stabil: arsipkan WordPress lama (cadangan penuh disimpan), baru nonaktifkan.
- [ ] Naikkan TTL DNS kembali (mis. 3600 detik).
- [ ] Jadwalkan: pembaruan dependensi bulanan (`composer audit`), uji pemulihan cadangan tiap kuartal, tinjau SEO 2-4 minggu.

## 7. Kontak dan peran (isi sebelum hari H)

| Peran | Nama | Kontak |
|---|---|---|
| Pemutus keputusan mundur | | |
| Pemegang DNS / registrar | | |
| Pemegang server / hosting | | |
| Pengelola email `info@decaa.id` | | |
| Pengembang | | |
