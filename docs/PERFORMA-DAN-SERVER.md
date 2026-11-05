# Panduan Kinerja dan Server DECAA.ID

Dokumen ini untuk **staging dan produksi** (bukan Docker lokal). Bagian bertanda **(sesuaikan)** bergantung pada hosting yang dipakai: cocokkan dengan kenyataan, lalu kabari bagian yang tidak cocok.

Target (Tahap C): Lighthouse mobile **>= 90**, LCP **< 2,5 dtk**, CLS **< 0,1**.

---

## 1. Urutan deploy (aman diulang)

Jalankan di server, di folder proyek, **setiap kali deploy**:

```bash
composer install --no-dev --optimize-autoloader   # tanpa paket dev, autoload teroptimasi
npm ci && npm run build                           # Vite: CSS/JS diminify + nama berkas berhash di public/build
php artisan migrate --force                       # hanya bila ada migration baru
php artisan optimize:clear                        # bersihkan cache lama dulu
php artisan config:cache
php artisan view:cache
php artisan event:cache
php artisan route:cache                           # lihat catatan di bawah
php artisan storage:link                          # sekali saja (varian gambar ditulis ke storage/app/public/variants)
php artisan site:check --production               # semua harus OK/WARN, tidak ada FAIL
```

Catatan:
- **`route:cache` gagal** bila ada rute memakai Closure. Bila muncul galat itu, lewati `route:cache` saja (kinerjanya turun sedikit, bukan masalah besar) dan kabari saya agar rutenya dipindah ke controller.
- **`config:cache`**: sesudahnya `.env` tidak dibaca langsung oleh kode; ubah `.env` lalu jalankan `php artisan config:cache` lagi. Di aplikasi ini semua pembacaan `env()` ada di berkas `config/`, jadi aman.
- Build `npm run build` bisa dilakukan di mesin lain lalu folder `public/build` diunggah, bila server tidak punya Node.
- **Rollback cepat** bila ada yang aneh sesudah cache: `php artisan optimize:clear`.
- Tailwind sudah membuang CSS yang tidak dipakai saat build (hanya kelas yang muncul di `resources/views` dan `resources/js`); tidak ada langkah tambahan. Bila kelas dibuat dinamis dari string (`'text-'.$warna`), kelasnya hilang dari build: tulis nama kelas utuh.

## 2. PHP-FPM dan OPcache **(sesuaikan)**

Di `php.ini` (atau `conf.d/opcache.ini`) produksi:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0   ; kode hanya berubah saat deploy; jalankan "reload php-fpm" setiap deploy
opcache.jit=0                   ; JIT tidak membantu aplikasi web Laravel dan menambah risiko
realpath_cache_size=4096K
realpath_cache_ttl=600
memory_limit=256M
upload_max_filesize=6M          ; sudah diperiksa php artisan site:check
post_max_size=40M
```

Bila `validate_timestamps=0`, **wajib** `reload` PHP-FPM sesudah deploy (atau `opcache_reset()`), kalau tidak kode lama terus dipakai. Bila hosting bersama (cPanel) tidak mengizinkan, biarkan `validate_timestamps=1` dengan `opcache.revalidate_freq=60`.

## 3. Kompresi dan header cache

### Nginx **(sesuaikan)**

```nginx
# Kompresi (gzip bawaan; brotli butuh modul ngx_brotli, pakai bila tersedia)
gzip on;
gzip_comp_level 5;
gzip_min_length 1024;
gzip_vary on;
gzip_types text/plain text/css text/xml application/json application/javascript application/xml+rss image/svg+xml;

# brotli on;  brotli_comp_level 5;  brotli_types text/css application/javascript application/json image/svg+xml;

# Aset berhash dari Vite: aman di-cache setahun
location /build/ {
    expires 1y;
    add_header Cache-Control "public, max-age=31536000, immutable";
    access_log off;
}

# Gambar unggahan dan bawaan: nama berkas unik, cache 30 hari
location ~* ^/(storage|img|wp-uploads)/ {
    expires 30d;
    add_header Cache-Control "public, max-age=2592000";
    access_log off;
    try_files $uri =404;
}

# Font
location ~* \.(woff2?|ttf)$ {
    expires 1y;
    add_header Cache-Control "public, max-age=31536000, immutable";
}
```

HTML **jangan** di-cache oleh server (isinya dinamis dan mengikuti bahasa/CSRF).

### Apache / `.htaccess` **(sesuaikan)**

```apache
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/css text/xml application/json application/javascript image/svg+xml
</IfModule>
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType image/webp "access plus 30 days"
  ExpiresByType image/jpeg "access plus 30 days"
  ExpiresByType image/png  "access plus 30 days"
  ExpiresByType image/svg+xml "access plus 30 days"
  ExpiresByType font/woff2 "access plus 1 year"
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType application/javascript "access plus 1 year"
</IfModule>
```

(CSS/JS satu tahun aman **hanya** untuk berkas di `public/build` karena namanya berhash; Laravel tidak memakai nama tetap untuk CSS/JS lain.)

### Di balik Cloudflare atau CDN **(sesuaikan)**
Aktifkan Brotli dan "Auto Minify" tidak perlu (Vite sudah minify). Jangan cache HTML kecuali ada aturan khusus; cache `/build/*`, `/storage/*`, `/img/*`. Atur `TrustProxies` agar `https` dan IP pengunjung terbaca benar (penting untuk pembatas laju dan pengecualian pemeliharaan).

## 4. Redis: perlu atau tidak?

**Belum perlu sekarang.** Situs ini mostly baca-saja dan cache-nya kecil (ukuran gambar, hasil `srcset`, konten halaman). Driver `file`/`database` cukup untuk puluhan ribu kunjungan per hari. Pertimbangkan Redis bila **salah satu** terjadi:
- server lebih dari satu (cache/sesi harus dibagi),
- antrean (queue) mulai dipakai untuk email atau impor besar,
- profil menunjukkan cache/sesi berkas jadi hambatan (banyak I/O disk).

Bila nanti dipakai: pasang paket `predis/predis` atau ekstensi `phpredis`, lalu `CACHE_DRIVER=redis`, `SESSION_DRIVER=redis`, `REDIS_HOST=...`, dan `php artisan config:cache`.

## 5. Gambar

- Unggahan admin sudah **WebP** (maks. 1920 px) dan punya **`srcset` 480/960 px** otomatis (K87); `width`/`height` dipasang otomatis agar tidak ada layout shift.
- Pastikan folder `storage/app/public` **bisa ditulis** oleh pengguna PHP (varian dibuat saat gambar pertama dibuka). Bila tidak bisa ditulis, situs tetap jalan tanpa `srcset`.
- Foto hero (LCP) memakai `fetchpriority="high"` dan tanpa `loading="lazy"` di Beranda. Bila Lighthouse menandai gambar LCP lain, tambahkan `:lazy="false"` pada gambar itu saja.

## 6. Cara menjalankan Lighthouse (di staging)

1. Buka Chrome jendela **Incognito**, tanpa ekstensi, alamat staging (HTTPS).
2. DevTools > Lighthouse > **Mobile**, kategori Performance, Accessibility, Best Practices, SEO.
3. Ukur halaman: **Beranda**, **Berita** (daftar), satu **detail Berita**, **Portofolio**, **Kontak**. Jalankan **3 kali** tiap halaman, ambil median.
4. Simpan skor dan temuan di tabel (halaman, skor, LCP, CLS, temuan teratas) lalu kirim ke saya.

Panduan membaca temuan yang paling sering:

| Temuan | Biasanya karena | Tindakan |
|---|---|---|
| LCP tinggi | Foto hero besar / server lambat (TTFB) | Cek TTFB dulu (cache config/route/view, OPcache). Pastikan hero WebP dan `fetchpriority=high` |
| CLS > 0,1 | Font tukar, iklan, gambar tanpa ukuran | Gambar sudah beratribut ukuran; cek font (`font-display: swap`, preload font utama) |
| "Properly size images" | Gambar lama belum punya varian | Buka halaman sekali agar varian dibuat; cek `sizes` pada kartu |
| "Enable text compression" | Gzip/Brotli belum aktif | Bagian 3 |
| "Serve static assets with efficient cache policy" | Header cache belum ada | Bagian 3 |
| "Reduce unused JavaScript" | Alpine plugin / skrip pihak ketiga (analitik) | Analitik dimuat hanya bila diaktifkan admin; tunda dengan `defer` |
| "Eliminate render-blocking" | CSS besar | CSS sudah hasil Tailwind teroptimasi; cek `public/build` terbaru ter-deploy |

## 7. Daftar periksa setelah deploy

- [ ] `php artisan site:check --production` tanpa FAIL.
- [ ] `php artisan site:links --no-links` tanpa GAGAL (cepat), lalu penuh bila waktu ada.
- [ ] Header respons (`curl -I https://.../build/app-xxxx.css`): `Content-Encoding: br` atau `gzip`, `Cache-Control` panjang.
- [ ] `curl -I https://.../id`: **tanpa** cache panjang; ada header keamanan (K57).
- [ ] Cron aktif: `* * * * * php /path/artisan schedule:run` (cadangan harian, penghapusan pesan 12 bulan).
- [ ] Lighthouse mobile: catat skor; ulangi sesudah perubahan besar.
