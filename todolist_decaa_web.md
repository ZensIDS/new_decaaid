# TODOLIST: Rebuild Website DECAA.ID (Laravel 9) — Versi Ringkas

> Tujuan: website korporat profesional (referensi: Kawan Lama Group), admin panel buatan sendiri, SEO kuat, cepat, dwibahasa ID/EN.

**Versi:** 38 (ringkas, 2 Nov 2026) | **Legenda:** `[ ]` belum, `[~]` file dikirim / menunggu verifikasimu, `[x]` selesai

## Status Singkat

- **Tahap A (desain depan):** semua halaman sudah dikirim, menunggu dicek di browser. Gerbang ACC belum.
- **Revisi PDF (`Revisi_Website_decaaid.pdf`):** R1 dan R2 (Beranda) dikirim. R3 sampai R5 menunggu bahan.
- **Tahap B-awal (admin):** BA1-BA4 dikirim (login, layout, dashboard, CRUD pengguna, log aktivitas).
- **Modul konten:** B3d Berita (v35), B3c Pilar & Brand (v36), dan terjemahan otomatis Berita (B5 awal, v37) dikirim. Semuanya menunggu dijalankan dan dicek di Docker-mu (lihat "Perintah Tertunda").
- **Berikutnya:** cek hasil di Docker, jawab Q11-Q16, lalu modul B3a/B3b/B3e dst.

---

## 0. Aturan Kerja

1. Tahap A dulu sampai ACC; backend/admin lengkap setelah itu.
2. Konten sementara = dummy natural, **ID dan EN**. Data berfakta dummy ditandai `DUMMY`.
3. Dikerjakan bertahap. Tiap pengiriman: hanya file yang dibutuhkan, satu file satu kartu, tanpa zip, tanpa kode di chat.
4. Tiap pengiriman menyertakan daftar **`path tujuan → nama file`** dan perintah yang perlu dijalankan.
5. Tiap tahap selesai, todolist diperbarui.
6. Aset di `public/img/...`; foto belum ada = placeholder rapi.
7. Modul admin baru **wajib mengikuti Pola Desain Admin** (bagian 5).
8. **Arsip yang dikirim ke Claude:** `app`, `config`, `database`, `lang`, `resources`, `routes`, `todolist_decaa_web.md` (+ `tailwind.config.cjs`, `vite.config.js` bila ada masalah build). **Jangan** sertakan `.env`, `vendor`, `node_modules`, `storage`, `public/build`, `.git`.

## 1. Ringkasan Teknis

Laravel 9 + PHP 8.3 (Sail) | Docker: `laravel.test`, `mysql` 8.0, `phpmyadmin` (8080) | MySQL | cache `file`, queue `sync` (nanti `database`), Redis ditunda | Blade + Tailwind 3 + Alpine 3 + Vite | admin buatan sendiri | ID default + EN (`/id/...`, `/en/...`) | font Plus Jakarta Sans (npm) | logo di `public/img/logo/` | staging `new.decaa.id`, lalu `decaa.id`.

---

## 2. Keputusan (K) — ringkas

| # | Keputusan |
|---|---|
| K1 | Tetap Laravel 9 + PHP 8.3 (risiko EOL diterima, mitigasi di bagian 8) |
| K2 | Admin dibuat sendiri; K3 MySQL |
| K5 | ID + EN wajib; terjemahan mesin di server, disimpan di database, bisa dikoreksi |
| K7 | Karir: CV ke `career@decaa.id`, umum, tanpa daftar posisi. Kontak: `info@decaa.id`. Tanpa WhatsApp melayang, tanpa Investor, ada pencarian |
| K11/K16 | CSR = **Komunitas Peduli Digital**; menu **Keberlanjutan** (`/id/keberlanjutan`, `/en/sustainability`) |
| K12 | Nama di website: **DECAA.ID** |
| K13 | Kantor pusat Jakarta (Ad Premier Office Park, TB Simatupang); cabang Pacitan (Jl. Letjend R. Suprapto) dan Ponorogo (Jl. Sultan Agung) |
| K14 | Pusat Unduhan ditunda |
| K15 | Jumlah artikel WordPress dibahas setelah ACC |
| K17 | Semua tombol: Capitalize Each Word |
| K18 | Logo PNG dipangkas otomatis (`logo_url()`) |
| K19 | Legalitas dihapus, diganti **Perjalanan Kami** |
| K21 | "Unit Bisnis" = **Pilar Bisnis** (`/id/pilar-bisnis`, `/en/business-pillars`); nama internal kode (`units.*`) tidak diubah |
| K22 | **8 pilar** (jenis bisnis): Software & Robotics; Hardware & Peripheral; Networking Services; Commercial Technology; Training & Research Centre; Stationery & Office Supply; Lifestyle Store; Food & Beverage. Brand di dalam pilar (20 brand). Data: `app/Support/Pillars.php` |
| K23 | Jajaran Kepemimpinan dihapus (Chairman Message tetap) |
| K24 | Slogan: **Mitra Solusi untuk Segala Akselerasi** + "Ekosistem digital dengan inovasi yang inklusif, aman, dan berkelanjutan bagi semua lini industri" |
| K25 | Menu Portofolio + **Penghargaan** (`/id/portofolio/penghargaan`) |
| K26 | Isi brand dari dokumen apa adanya; Instagram/TikTok/deskripsi yang belum ada dikosongkan |
| K27 | Halaman tetap + modul Halaman Kustom; menu otomatis (admin ubah label, sembunyikan, urut tingkat atas); dikerjakan modul demi modul end-to-end |
| K28 | Data kode diimpor otomatis ke database; berita sementara dummy, nanti impor WordPress + foto (perintah + tutorial). Media Library: WebP, thumbnail, alt, maks 5 MB |
| K29 | Peran Super Admin / Admin / Editor; 2FA wajib Super Admin dan Admin; `/admin` noindex + batas login; admin berbahasa Indonesia. Super Admin awal `decaa@decaa.id` (password lewat chat, wajib ganti) |
| K30 | Pilar/Brand: tambah-ubah-urut-sembunyikan, hapus hanya Super Admin. Klien: nama opsional, kategori dikelola, beranda via centang, kolom izin tampil. Proyek: satu pilar, galeri disiapkan. Penghargaan sederhana (judul, tahun, deskripsi, foto, unggulan manual). Berita: Draf/Terbit/Terjadwal, kategori dikelola, tanpa video/tag/penulis. **Tanpa modul admin:** Statistik, Kepemimpinan, Legalitas |
| K31 | Email: server `mail.decaa.id` (SMTP 465). **Hanya akun `info@decaa.id`** (pengirim, penerima Kontak, notifikasi). Balasan otomatis, honeypot + batas kirim, simpan pesan 12 bulan |
| K32 | Tab ID/EN + status terjemahan sejak awal; SEO + Redirect + sitemap di admin; mode pemeliharaan dari admin, `SITE_NOINDEX` tetap di `.env`; log aktivitas, riwayat revisi (Berita + Halaman Kustom), dashboard kesehatan situs di Tahap B; live preview setelah CRUD selesai; mode gelap admin = backlog |
| K33 | Berita: isi = HTML bersih per bahasa; EN kosong jatuh ke ID; Terjadwal tampil otomatis tanpa cron; gambar di `storage/app/public` (WebP, maks 5 MB); kategori "Komunitas" jadi **Keberlanjutan** (slug `community` tetap); slug terlarang: kategori, category, page, halaman, search, cari, feed, sitemap; Editor tidak bisa menghapus |
| K34 | Pilar & Brand: ikon dari 16 pilihan, urut seret-dan-lepas + tombol panah, logo dipangkas WebP, tautan `http://` otomatis jadi `https://` saat simpan, hapus hanya Super Admin, pilar berisi brand tidak bisa dihapus. Brand Commercial Technology ditandai Dummy |
| K35 | Terjemahan otomatis Berita saat simpan: isi salah satu bahasa, sisi kosong diterjemahkan (judul, ringkasan, isi, alt, SEO); isian admin tidak pernah ditimpa; status jadi Otomatis (belum ditinjau). `.env`: `TRANSLATE_DRIVER` = `deepl` / `google` / `anthropic` / `none` (bawaan `none`). Belum ada tombol terjemah ulang manual dan label "Diterjemahkan otomatis" di publik |

---

## 3. Pertanyaan yang Masih Terbuka

- **Q11.** MBG Food Hub kutaruh di Commercial Technology bersama Satu AI, Pasar Online, Desa Digital. Benar?
- **Q12.** Deskripsi, tautan, logo 4 brand Commercial Technology: diisi nanti?
- **Q13.** Teks **Misi** di dokumen terpotong ("Misi : Mengikuti"). Kirim isi lengkapnya.
- **Q14.** Tahun mulai Perjalanan Kami: dokumen **2019**, K20 lama **2018**. Di R3 kuikuti dokumen (2019-2026). Setuju?
- **Q15.** Tautan brand: `http://www...` apa adanya, atau `https://` (disarankan; admin sudah menormalkan otomatis)?
- **Q16.** Penyedia terjemahan untuk `.env`: **DeepL** (rekomendasiku, 500 rb karakter gratis/bulan), Google Cloud Translation, atau Claude API.

---

## 4. Sitemap

Beranda | Tentang Kami (Profil + Chairman Message, Visi & Misi, Perjalanan Kami) | Pilar Bisnis (daftar 8, detail pilar, halaman brand) | Portofolio (Proyek, Klien & Mitra, Penghargaan) | Berita (daftar, kategori, detail) | Keberlanjutan | Karir | Kontak | Pencarian | Pendukung (Privasi, 404, 500, 503)

## 5. Pola Desain Admin (wajib untuk modul baru)

- **Sidebar** gelap navy `#0B1437`, logo putih, item aktif gradasi biru (`#2B4BC4` ke `#4A6CF0`), item belum jadi redup berlabel "Segera", otomatis scroll ke menu aktif.
- **Halaman daftar:** `<x-admin.page-header>`, kotak cari/filter putih `rounded-2xl`, `<x-admin.table>`, `<x-admin.pager>`, `<x-admin.empty>`, aksi berbahaya lewat `<x-admin.confirm>`, ikon aksi punya `title` dan `aria-label`.
- **Halaman tambah/ubah:** grid `lg:grid-cols-3`; kiri (2/3) kartu `<x-admin.form-section>`; kanan (1/3) kartu pratinjau (header `h-24`, avatar `-mt-10`) berisi tombol **Simpan + Batal** (`lg:sticky lg:top-24`). Form dibungkus `<form class="contents">`; form kedua ditaruh di luar.
- Pilihan sedikit = kartu radio (`sr-only` + `peer-checked`); saklar `<x-admin.toggle>` dalam kotak abu muda; tab ID | EN di dalam `form-section`.
- Merah hanya untuk hapus/peringatan; teks tombol Capitalize Each Word; semua teks Indonesia.
- **Hindari** `group-has-*`; pakai `peer-checked` atau `[&_.kelas]:`.
- Komponen: `button`, `input`, `select`, `toggle`, `card`, `form-section`, `password-field`, `badge`, `table`, `pager`, `modal`, `confirm`, `toast`, `empty`, `icon`, `page-header`, `stat-card`.

---

## 6. Pekerjaan yang Tersisa

### 6A. Perintah Tertunda (jalankan di Docker, lalu verifikasi)

- [ ] **BA3:** `php artisan migrate` (tabel `activity_logs`). Cek: daftar pengguna, tambah Editor (login, wajib ganti password, `/admin/pengguna` = 403), nonaktifkan/hapus diri sendiri dan Super Admin terakhir ditolak, reset password, Log Aktivitas, responsif 360/768/1024/1440.
- [ ] **B3d Berita:** `php artisan migrate`, `php artisan content:import-news` (5 kategori + 9 dummy), `php artisan storage:link`, `npm run build`. Cek: tambah berita ID saja (EN jatuh ke ID), editor, unggah gambar, Terjadwal, Riwayat Revisi + Kembalikan, kategori (hapus yang dipakai ditolak, slug `kategori` ditolak), Editor tidak bisa hapus.
- [ ] **B3c Pilar & Brand:** `php artisan migrate`, `php artisan content:import-pillars` (8 pilar + 20 brand; `--refresh` menimpa brand Dummy yang belum disentuh), `php artisan storage:link`, `npm run build`. Cek: menu aktif untuk Super Admin dan Admin (Editor 403), seret urut bertahan, sembunyikan = 404 di publik, logo besar terpangkas WebP, `http://` jadi `https://`, slug kembar ditolak, hapus pilar berisi brand ditolak.
- [ ] **B5 terjemahan (v37):** migration `translated_from` pada `news` (`php artisan migrate`), isi `.env`: `TRANSLATE_DRIVER=deepl` (atau pilihan Q16) + kunci API, lalu `php artisan config:clear`. Cek: simpan berita ID saja, EN terisi dan berstatus Otomatis.
- [ ] **Hapus file lama berhuruf kecil**, lalu `composer dump-autoload`: `app/Models/{Newscategory,Newsrevision}.php`, `app/Support/{Imagestore,Newsrepo}.php`, `app/Http/Controllers/Admin/{Newscontroller,Newscategorycontroller}.php`, `app/Console/Commands/Importnews.php`.
- [ ] **Hapus file lain yang tak terpakai:** `resources/views/pages/about/team.blade.php`, `pages/about/legal.blade.php`, `app/Console/Commands/TrimLogos.php`, `resources/js/vite.config.js`, `lang/en/en.json` dan `lang/en/id.json`.
- [ ] Cek `lang/id.json` dan `lang/en.json`: ganti sisa kata "Unit Bisnis" / "tujuh pilar". Di `config/app.php`: `faker_locale` = `id_ID`.

### 6B. Tahap A: Desain Depan

- [ ] **A0:** verifikasi checklist `A0-SETUP.md` (instalasi, Vite/Tailwind/Alpine di Docker, routing `/id` `/en`).
- [ ] **A1/A2:** cek `/style-guide`, header, mega menu, menu HP, switch bahasa; tambahkan `public/favicon.ico`.
- [ ] **A3-A9:** cek semua halaman di browser. Peta interaktif (I4) belum dibuat; telepon dan WhatsApp masih DUMMY. Uji 500/503 dengan `APP_DEBUG=false`, `php artisan down` / `up`.
- [ ] **A10:** responsif 360/768/1024/1440 px, kontras warna, dimensi eksplisit logo, konsistensi tipografi dan bahasa.
- [ ] **GERBANG: ACC desain halaman depan.**

### 6C. Revisi Tampilan (cek di browser)

- [ ] Beranda (putaran 2 + R2), Tentang Kami, Pilar Bisnis (daftar bento + detail per brand), Klien & Mitra (90 logo asli, 7 kategori), Detail Berita, ikon sosmed.
- [ ] **Foto dibutuhkan** (selama kosong tampil placeholder): `public/img/dummy/{hero,hero-2,hero-3,about-1,about-2,community}.jpg`, `public/img/building.png`, `public/img/chairman.jpg`, `public/img/offices/jakarta.jpg`, `public/img/journey/{tahun}.jpg`, `public/img/dummy/news-*.jpg` (16:9).
- [ ] **Logo brand baru** di `public/img/logo/`: `mmc-computer.png`, `apple-kingdom.png`, `sekolah-robotika.png`, `gardenia.png`, `hba-supplier.png`, `fruvola.png`, `satu-ai.png`, `pasar-online.png`, `mbg-food-hub.png`, `desa-digital.png`.
- [ ] Nama klien asli belum ada; tampilkan logo hanya yang sudah mengizinkan.

### 6D. Revisi PDF (menunggu bahan)

- [ ] **R2:** isi 3 foto depan (butir 6: foto besar semua brand decaa berbaju berbeda; dua lainnya belum jelas); cek ulang teks Beranda lain.
- [ ] **R3 (Tentang Kami):** teks profil baru (5 pilar teknologi + bisnis pendukung, semangat "everything is nothing without technology"); Visi dari dokumen; **Misi kosong** (Q13); Perjalanan Kami 2019-2026 (Q14).
- [ ] **R4 (Penghargaan):** halaman carousel kartu foto + judul; isi kosong sampai daftar penghargaan dan sertifikat dikirim.
- [ ] **R5:** data Portofolio (proyek kementerian/klien besar), logo klien terbaru (Ristekdikti diganti kementerian barunya), berita lama (B6), validasi telepon/WA/sosmed.

### 6E. Tahap B: Admin (setelah BA3 terverifikasi)

- [ ] **BA4:** putaran revisi desain login, layout, dashboard sesuai masukanmu, lalu dikunci sebagai pola.
- [ ] **B1 (sisa):** tabel `settings`, `media`, `redirects`, `messages`, `projects`, `awards`, `clients`, `client_categories`, `milestones`, `offices`, `page_contents`, `pages`; media layer (WebP, thumbnail, alt ID/EN); cache query dibersihkan saat simpan; slug unik + redirect saat slug berubah.
- [ ] **B2 (sisa):** 2FA (Super Admin/Admin), lupa password lewat email (setelah SMTP teruji), upload drag-and-drop + crop, pemilih Media Library.
- [ ] **B3 modul konten** (end-to-end, tiap modul: admin + frontend dari database):
  - [ ] B3a Pengaturan Situs (identitas, logo, favicon, kontak, sosmed, kantor, footer, SEO bawaan)
  - [ ] B3b Media Library (grid, alt, cegah hapus bila dipakai)
  - [ ] B3e Beranda (teks section, proyek unggulan, tampil/urut)
  - [ ] B3f Tentang Kami (Profil + Chairman Message, Visi & Misi, Perjalanan)
  - [ ] B3g Proyek | B3h Klien & Mitra | B3i Penghargaan | B3j Keberlanjutan | B3k Karir (bidang otomatis dari 8 pilar)
  - [ ] B3l Kontak & Pesan Masuk (subjek, kotak masuk, status baca, ekspor CSV)
  - [ ] B3m Halaman Legal/Kustom (Kebijakan Privasi)
  - [ ] B3n SEO, Redirect, Sitemap (+ redirect 301 otomatis saat slug berubah, SEO per pilar/brand)
  - [ ] B3o Menu (label, sembunyikan, urut tingkat atas)
  - [ ] B3p Pengaturan Sistem (mode pemeliharaan + tautan rahasia, analitik, verifikasi Search Console)
- [ ] **B4:** form Kontak aktif (simpan ke `messages`, notifikasi ke `info@decaa.id`, balasan otomatis, anti-spam); pencarian dari database; filter Portofolio/Klien tetap Alpine; hapus `Content::stats()`; uji ID/EN + fallback.
- [ ] **B5 (sisa):** tombol terjemah ulang manual, terjemahan Brand dan Halaman Kustom, perintah massal via queue (`database`), label "Diterjemahkan otomatis", halaman inti wajib ditinjau.
- [ ] **B6 WordPress:** bahas jumlah artikel (K15); ekspor artikel + foto; skrip impor (rapikan HTML, path gambar, WebP, petakan kategori ke 5 kategori); redirect 301; terjemahan massal; verifikasi sampel dan jumlah; sediakan tutorial langkah demi langkah.
- [ ] Dashboard kesehatan situs (terjemahan belum ditinjau, gambar tanpa alt, halaman tanpa meta, item DUMMY); live preview setelah semua CRUD selesai.

### 6F. Tahap C: SEO, Performa, Keamanan

- [ ] **`.env` lokal:** `MAIL_MAILER=log`, `MAIL_FROM_ADDRESS="info@decaa.id"`.
- [ ] **`.env` staging/produksi:** `MAIL_MAILER=smtp`, `MAIL_HOST=mail.decaa.id`, `MAIL_PORT=465`, `MAIL_USERNAME=info@decaa.id`, `MAIL_ENCRYPTION=tls`; `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` sesuai domain, `APP_KEY` dan password DB **baru**. Password hanya di `.env` server.
- [ ] **Temuan:** tambah `'noindex' => (bool) env('SITE_NOINDEX', false),` di `config/site.php`; `SITE_NOINDEX=true` di staging, `false` di produksi.
- [ ] Uji email dari staging; cek SPF/DKIM/DMARC `decaa.id`.
- [ ] SEO: title/meta unik, Open Graph, satu H1, `hreflang`, canonical, sitemap per bahasa, `robots.txt`, JSON-LD (Organization, LocalBusiness, BreadcrumbList, Article), Search Console + Bing, analitik ringan.
- [ ] Performa: WebP/AVIF, `srcset`, cache route/config/view, Gzip/Brotli, pertimbangkan Redis; target Lighthouse >= 90 mobile, LCP < 2,5 dtk, CLS < 0,1.
- [ ] Keamanan: HTTPS + HSTS, security headers (CSP), rate limiting, honeypot + Turnstile/reCAPTCHA, validasi upload, backup otomatis DB + media, `composer audit` rutin, uji aksesibilitas penuh.

### 6G. Tahap D dan E

- [ ] **D:** uji lintas browser/perangkat, uji Kontak + email, panduan admin + pelatihan singkat, checklist pra-live (bagian 7), migrasi `new.decaa.id` ke `decaa.id` dengan redirect aktif, pantau Search Console 1-2 minggu, rencana rollback.
- [ ] **E:** monitoring uptime/error, evaluasi kecepatan dan SEO (2-4 minggu), jadwal rutin (konten, update dependensi, uji pemulihan backup), rencana naik versi Laravel.

### 6H. Daftar DUMMY yang harus diganti sebelum go-live

- [ ] Telepon (`+62 21 0000 0000`), WhatsApp, 6 URL sosmed (`#`)
- [ ] Chairman Message (nama, foto, isi)
- [ ] Teks Beranda: 4 nilai perusahaan, blok Keberlanjutan, "Satu pintu" di halaman Pilar Bisnis
- [ ] 4 program Komunitas Peduli Digital
- [ ] Kebijakan Privasi (draf, **wajib ditinjau pihak hukum**)
- [ ] Brand Commercial Technology (tanpa deskripsi/logo) + 10 logo brand baru
- [ ] Misi (Q13) dan foto Perjalanan Kami per tahun
- [ ] Foto dummy: hero (3), tentang (2), komunitas, berita, proyek, penghargaan, kantor
- [ ] 9 berita contoh (diganti impor WordPress, B6)
- [ ] Nama proyek dan klien dummy di Portofolio; 6 penghargaan contoh

---

## 7. Checklist Audit-Ready

- [ ] Identitas jelas (nama, profil, visi-misi, bidang usaha)
- [ ] Pengganti Legalitas: cara meminta dokumen lewat Kontak (subjek "Permintaan Dokumen")
- [ ] Alamat kantor, email, telepon resmi mudah ditemukan
- [ ] 8 pilar bisnis + bukti kerja (portofolio, klien, penghargaan) jelas
- [ ] Menu korporat, tampilan konsisten, tanpa teks di dalam gambar
- [ ] Konten dan tanggal terbaru, tanpa link rusak atau halaman kosong
- [ ] Responsif, cepat, HTTPS; dwibahasa ID/EN benar; aksesibilitas dasar
- [ ] Kebijakan Privasi tersedia

## 8. Risiko Utama

| Risiko | Mitigasi |
|---|---|
| Laravel 9 EOL, PHP 8.3 belum resmi diuji | Batasi paket pihak ketiga, `composer audit` rutin, HTTPS/headers/rate limit/WAF (mis. Cloudflare), upgrade bertahap setelah live |
| Konten asli terlambat | Dummy natural + placeholder |
| Logo klien tanpa izin | Tampilkan hanya yang mengizinkan |
| Peringkat Google turun saat migrasi | Redirect 301 lengkap + Search Console |
| Terjemahan mesin keliru | Halaman inti wajib ditinjau, glosarium brand, status terjemahan |
| Lingkup terlalu besar | Selesaikan Tahap A sampai ACC dulu |
| Impor banyak foto WordPress | WebP saat impor, proses via queue |

## 9. Definition of Done

Seluruh konten depan dapat diubah dari admin tanpa kode | tampil sempurna di HP/tablet/desktop | Lighthouse >= 90 | switch ID/EN benar (URL, hreflang, SEO) | tanpa link rusak, redirect WordPress berjalan | backup otomatis teruji | panduan admin tersedia dan admin dilatih.

## 10. Backlog

Pusat Unduhan, lowongan spesifik, testimoni klien, peta interaktif kantor (I4), gambar share OG otomatis (I6), video hero (I7), mode gelap + pintasan keyboard admin (I8), halaman Mitra & Sertifikasi (I9).

---

## 11. Progress Log (diringkas)

| Tanggal | Perubahan |
|---|---|
| 30 Okt 2026 | v1-v23: keputusan awal, Tahap A selesai dikirim (A0-A10), revisi Beranda/hero/Tentang Kami/Pilar Bisnis/Klien/Berita/ikon sosmed |
| 2 Nov 2026 | v24-v25: revisi PDF R1 (slogan, 8 pilar + brand, hapus Kepemimpinan, menu Penghargaan) dan R2 Beranda |
| 2 Nov 2026 | v26-v27: perencanaan admin (QA1-QA18 dijawab, K27-K32), Tahap B-awal disusun |
| 2 Nov 2026 | v28-v33: BA1-BA4 (login, layout, dashboard, CRUD pengguna, log aktivitas, Pola Desain Admin), pemeriksaan `.env` dicatat |
| 2 Nov 2026 | v34-v35: B3d Berita & Kategori (admin + frontend), nama file PSR-4 dibetulkan |
| 2 Nov 2026 | v36: B3c Pilar & Brand (admin + frontend, `PillarRepo`) |
| 2 Nov 2026 | v37: terjemahan otomatis Berita (B5 awal, K35): `config/translator.php`, `Translator`, `NewsAutoTranslate`, migration `translated_from` |
| 2 Nov 2026 | v38: todolist diringkas (riwayat detail, daftar file per versi, dan pertanyaan terjawab dibuang; isinya sudah tercermin di K-log) |