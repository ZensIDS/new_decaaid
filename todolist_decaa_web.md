# TODOLIST: Rebuild Website DECAA.ID (Laravel 9)

> Tujuan: website korporat berkelas profesional tinggi yang mengikuti prinsip referensi (Kawan Lama Group), dengan admin panel buatan sendiri yang ramah pengguna, SEO kuat, performa cepat, dan dukungan dwibahasa (ID/EN).

**Versi:** 23 (diperbarui 30 Okt 2026)
**Status:** A0 (fondasi) menunggu verifikasimu; A1 (design system) menunggu dicek lewat `/style-guide`; A2 (layout global) dan A3 (beranda) file sudah dikirim, menunggu dicek di browser; A4 (Tentang Kami), A5 (Pilar Bisnis), A6 (Portofolio & Klien), A7 (Berita), A8 (Komunitas, Karir, Kontak), dan A9 (halaman pendukung) file sudah dikirim, menunggu dicek di browser. **Sedang berjalan: Revisi Tampilan Halaman Depan (Bagian 7, setelah A10)**, dimulai dari Beranda; Beranda putaran 2 sudah dikirim, menunggu kamu cek di browser. **Tentang Kami (putaran 1 dan 2) sudah dikirim**, menunggu kamu cek di browser. **Pilar Bisnis (daftar + detail) sudah didesain ulang**, menunggu kamu cek di browser. **Klien & Mitra sudah didesain ulang dengan logo asli**, menunggu kamu cek di browser. **Detail Berita (single page) sudah didesain ulang**, menunggu kamu cek di browser. Halaman lain menyusul satu per satu.
**Legenda:** `[ ]` belum, `[~]` file dikirim / menunggu verifikasi darimu, `[x]` selesai dan terverifikasi, **(I)** ide tambahan.

---

## 0. Aturan Kerja (Disepakati)

1. **Prioritas sekarang:** desain halaman depan sampai di-ACC. Backend dan admin lengkap dikerjakan setelah ACC.
2. **Konten sementara:** dummy yang natural (bukan lorem ipsum), disiapkan **dalam ID dan EN** supaya fitur switch bahasa bisa langsung diperlihatkan saat ACC.
3. **Semua menu ikut dibuat** sebagai awalan (lihat sitemap, Bagian 6), tidak hanya beranda.
4. **Dikerjakan bertahap.** Tiap tahap: kirim hanya file yang dibutuhkan, satu file satu kartu unduh, tanpa zip, tanpa menempel kode di chat.
5. **Tiap tahap selesai, todolist ini diperbarui** (centang + catatan di Progress Log, Bagian 12).
6. Setiap pengiriman menyertakan **path tujuan file** dan perintah yang perlu dijalankan (jika ada).
7. Aset (logo, foto) ditaruh di `public/img/...`; sampai foto asli siap, tampilan memakai **placeholder** yang rapi (tidak ada gambar rusak).
8. Data dummy yang berisi fakta (nomor telepon, sosmed, angka, nama klien) **ditandai `DUMMY`** di kode dan diganti setelah desain di-ACC.

---

## 1. Ringkasan Teknis (Final)

| Aspek | Keputusan |
|---|---|
| Framework | Laravel 9 |
| PHP | 8.3 (Laravel Sail runtime 8.3) |
| Environment | Docker Compose milikmu: `laravel.test` (Sail), `mysql` 8.0, `phpmyadmin` (port 8080) |
| Database | MySQL |
| Cache / Queue | Redis ditunda. Sementara cache `file`, queue `sync` (nanti `database`) |
| Frontend publik | Blade + Tailwind CSS 3 + Alpine.js 3, build dengan Vite (tanpa SPA) |
| Admin panel | Dibuat sendiri (Blade + Tailwind + Alpine), gaya sendiri, komponen reusable |
| Halaman | Section-based page builder (blok bisa ditambah, dihapus, diurutkan, disembunyikan) |
| Bahasa | ID (default) + EN, URL `/id/...` dan `/en/...`, terjemahan otomatis sebagai bantuan (Bagian 4A) |
| Font | Plus Jakarta Sans, self-host lewat paket npm |
| Aset logo | `public/img/logo/` |
| Hosting | Staging di `new.decaa.id`, lalu migrasi total ke `decaa.id` |

---

## 2. Log Keputusan

| # | Topik | Keputusan | Catatan |
|---|---|---|---|
| K1 | Versi Laravel | **Tetap Laravel 9 + PHP 8.3** | Risiko EOL diterima, mitigasi di Bagian 10 |
| K2 | Admin | **Dibuat sendiri**, gaya sendiri | Dikerjakan setelah ACC desain depan |
| K3 | Infrastruktur | **MySQL**; Redis dipertimbangkan nanti | |
| K4 | Pengelolaan halaman | **Section-based** | |
| K5 | Bahasa | **ID + EN wajib**, terjemahan otomatis | Rancangan di Bagian 4A |
| K6 | Konten & identitas | Warna dan font ikut desain terbaikku; data dan foto dummy dulu; logo sudah ada | Aset logo: Bagian 5B |
| K7 | Fitur | Karir (CV ke `career@decaa.id`, **tanpa daftar posisi, bersifat umum**), Kontak (`info@decaa.id`), tanpa WhatsApp melayang, tanpa Investor, pencarian situs: ya | |
| K8 | Domain & migrasi | Domain tetap; staging **`new.decaa.id`**; semua berita diimpor beserta foto | Jumlah artikel dibahas setelah ACC |
| K9 | Kriteria audit | Tidak ada kriteria tertulis | Checklist Audit-Ready (Bagian 8) |
| K10 | Unit bisnis | **Dipisah**: Software, Robotics, Hardware, Networking Services, Training & Research Centre, PaperKing (Stationery & Office Supply), Dapur Kirana (Food & Beverages) | Konfirmasi nama: Q8 |
| K11 | CSR | **Komunitas Peduli Digital** (komunitas CSR DECAA.ID), bukan unit bisnis | Ditempatkan di menu "Komunitas" (Q10) |
| K12 | Nama di website | **DECAA.ID** | |
| K13 | Kantor | **Pusat: Jakarta** (Ad Premier Office Park, Jl. TB Simatupang, Ragunan, Pasar Minggu). **Cabang: Pacitan** (Jl. Letjend R. Suprapto, Sidoarjo) dan **Ponorogo** (Jl. Sultan Agung) | |
| K14 | Pusat Unduhan | **Ditunda** | Masuk backlog (Bagian 9) |
| K15 | Jumlah artikel WordPress | **Dibahas setelah ACC desain depan** | |
| K16 | Nama menu CSR | Menu dan halaman **"Komunitas" diganti "Keberlanjutan"** (EN: Sustainability); URL `/id/keberlanjutan` dan `/en/sustainability`. Nama program tetap **Komunitas Peduli Digital** | Menggantikan Q10. Kategori berita "Komunitas" belum diubah (menunggu konfirmasi) |
| K17 | Gaya teks tombol | **Semua tombol dan elemen bergaya tombol: Capitalize Each Word** (CSS `capitalize`, berlaku ID dan EN) | Tombol baru wajib mengikuti |
| K18 | Logo | Logo PNG dipangkas otomatis ruang kosongnya (`logo_url()`, salinan di `public/img/_trim/`, file asli tidak diubah) | Ukuran logo diatur lewat class tinggi (`h-7`, `h-9`, dst.) |
| K19 | Menu Tentang Kami | **Legalitas dihapus** (halaman, menu, route) dan **diganti "Perjalanan Kami"** (`/id/tentang-kami/perjalanan-kami`, `/en/about-us/our-journey`). Bagian "Perjalanan kami" di Profil Perusahaan dihapus (pindah ke halaman sendiri) | Menggantikan sebagian K-Legalitas di Bagian 4B, 8, dan B3. URL lama `/tentang-kami/legalitas` kini 404 (aman di staging; tambahkan redirect 301 bila perlu) |
| K20 | Tentang Kami, putaran 2 | **Perjalanan Kami mulai 2018** (semua tahun dan nama file foto ikut). Menu "Manajemen" diganti **"Jajaran Kepemimpinan"** (EN: Leadership; URL `/id/tentang-kami/jajaran-kepemimpinan`, `/en/about-us/leadership`), kartu hanya nama + foto, **jabatan hanya untuk CEO**. "Sektor yang kami layani" diganti **Chairman Message**. Angka ringkas (7 unit, 3 kantor, dst.) dihapus dari Profil | |
| K21 | Istilah "Unit Bisnis" | **Diganti "Pilar Bisnis"** (EN: Business Pillars) di semua teks, menu, footer, pencarian, dan URL: `/id/pilar-bisnis` dan `/en/business-pillars` (detail: `/id/pilar-bisnis/{slug}`). Nama internal (route `units.*`, method `units()`, file `pages/units/`) sengaja tidak diubah agar kode aman | Catatan lama (K10, Q8, log) yang menyebut "unit bisnis" = Pilar Bisnis. URL lama `/unit-bisnis` kini 404 (staging; redirect 301 bila perlu) |

---

## 3. Masih Perlu Jawabanmu

**Q8. Konfirmasi nama unit.** Dari nama file logomu, aku membaca ada 7 unit + 1 komunitas. Mohon cek apakah nama berikut sudah benar (ejaan resmi di website):

| Logo | Nama yang kupakai | Keterangan |
|---|---|---|
| software.png | Software | |
| robotic.png | Robotics | |
| hardware.png | Hardware | |
| networking.png | Networking Services | |
| trainingcentre.png | Training & Research Centre | |
| paperking.png | PaperKing | Stationery & Office Supply (ejaan lama "Stationary" diperbaiki) |
| dapurkirana.png | Dapur Kirana | Food & Beverages |
| kompeddig.png | Komunitas Peduli Digital | CSR, bukan pilar bisnis |

Catatan: aku hanya bisa membaca nama file dari screenshot, bukan isi gambar logonya. Kalau ada logo yang salah penamaan, kabari.

**Q9. Pilihan desain section Klien & Mitra.** Lihat usulan di Bagian 4E, pilih A, B, atau C (rekomendasiku A).

**Q10. Penempatan CSR (terjawab, lalu diganti K16 jadi "Keberlanjutan").** Usulanku: menu utama **"Komunitas"** (setara "Keberlanjutan" di referensi) berisi halaman Komunitas Peduli Digital, bisa bertambah program nanti. Alternatif: disimpan di bawah menu Tentang Kami. Setuju dengan menu "Komunitas"?

*(Pertanyaan Q1-Q7 sebelumnya sudah terjawab dan dicatat di Bagian 2.)*

---

## 4. Pendekatan Teknis

### 4A. Terjemahan otomatis (jawaban K5)

**Bisa, dan sebaiknya dipakai, dengan cara yang benar.**

- **Tidak kusarankan:** widget terjemah di browser (mis. Google Translate). Hasil tidak tersimpan, tidak terbaca Google sebagai halaman berbahasa Inggris (SEO nol), nama brand sering salah, kualitas tak terkontrol.
- **Kusarankan:** terjemahan mesin **di sisi server**, disimpan ke database sebagai draf EN yang bisa dikoreksi admin.

**Rancangan:**
1. Di admin, tiap form punya tab ID | EN + tombol **"Terjemahkan otomatis ke EN"** yang mengisi kolom EN sebagai draf.
2. Status terjemahan: `Manual`, `Otomatis (belum ditinjau)`, `Ditinjau`. Dashboard menampilkan jumlah yang belum ditinjau.
3. **Halaman inti** (Beranda, Tentang Kami, Pilar Bisnis): wajib ditinjau manusia sebelum tampil di EN.
4. **Berita** (termasuk artikel impor WordPress): diterjemahkan **massal lewat queue** (perintah artisan), tampil di EN dengan catatan kecil "Diterjemahkan otomatis", bisa diedit kapan saja.
5. **Glosarium proteksi:** nama brand dan istilah tertentu (DECAA.ID, nama unit, nama produk) tidak diterjemahkan.
6. Terjemahan dilakukan **sekali**, disimpan, dilayani dari database (cepat dan SEO-friendly dengan hreflang).
7. Artikel tanpa EN: halaman EN menampilkan versi ID dengan label bahasa, bukan halaman kosong.
8. Penyedia terjemahan **bisa diganti** (DeepL, Google Cloud Translation, atau model AI seperti Claude API) lewat `.env`. Pilihan penyedia dan biaya dibahas di Tahap B5, setelah jumlah artikel WordPress diketahui.

### 4B. Pusat Unduhan (DITUNDA)
Tidak dibuat sekarang. Bila nanti diperlukan: halaman kecil berisi dokumen publik (Company Profile PDF, katalog). Dokumen sensitif (akta, NIB) tidak dipajang. Halaman Legalitas sudah dihapus (K19); salinan dokumen tetap bisa diminta lewat formulir Kontak dengan subjek "Permintaan Dokumen".

### 4C. Tombol WhatsApp
Tanpa tombol melayang. WhatsApp tampil sebagai kanal resmi di halaman Kontak dan footer, serta tombol CTA biasa di bagian "Hubungi Kami".

### 4D. Karir (umum)
Halaman Karir **tanpa daftar lowongan**. Isinya: alasan bergabung dengan DECAA.ID, nilai dan budaya kerja, proses seleksi secara umum, dan tombol **"Kirim CV"** yang membuka email ke `career@decaa.id` dengan subjek terisi otomatis ("Lamaran - [posisi yang diminati]"), plus tombol salin alamat email. Tidak ada form upload. Jika nanti lowongan spesifik diperlukan, modul admin bisa ditambahkan.

### 4E. Usulan section "Klien & Mitra" (menunggu pilihanmu, Q9)

Tujuan: menunjukkan bukti kepercayaan (siapa yang sudah memakai layanan DECAA.ID) dengan rapi, tanpa terlihat seperti tempelan logo acak.

**Elemen pembentuk:**
- **Strip "Dipercaya oleh"** di beranda: satu baris 8-12 logo, bergerak pelan (CSS marquee), berhenti saat disorot atau difokus, dan statis bila pengguna mematikan animasi. Logo abu-abu, berwarna saat hover.
- **Angka ringkas** di atasnya (mis. jumlah klien, jumlah proyek, tahun pengalaman). Di dummy ditandai DUMMY.
- **Halaman "Klien & Mitra"** (di bawah menu Portofolio): grid logo seragam dalam kartu abu muda (gaya seperti "Pilar Bisnis" di referensi), dengan filter kategori: **Pemerintahan, Pendidikan, Swasta, Mitra Teknologi**.
- **Klien dan Mitra dipisah:** klien = pengguna layanan, mitra = principal/vendor teknologi.
- Di admin: kelola logo, kategori, urutan, dan toggle "tampil di beranda".

**Tiga opsi:**
- **A (disarankan):** strip di beranda + halaman penuh "Klien & Mitra" dengan filter kategori. Paling lengkap dan paling korporat.
- **B:** beranda menampilkan grid logo statis per kategori, tanpa marquee dan tanpa halaman terpisah. Lebih sederhana, tapi beranda lebih panjang.
- **C:** hanya strip di beranda, tanpa halaman penuh. Paling ringan, cocok jika klien sedikit.

**Catatan praktis:**
- Tampilkan hanya logo klien yang **sudah mengizinkan** (terutama instansi pemerintah).
- Logo ideal: SVG atau PNG transparan, rasio seragam.
- Dummy memakai kartu placeholder netral dengan label kategori generik, **tanpa nama klien asli**.
- Testimoni (kutipan klien) hanya dibuat bila ada kutipan nyata yang disetujui; ditunda.

---

## 5. Arah Desain Awal (Usulan, untuk di-ACC)

**Karakter:** korporat bersih, modern, banyak ruang kosong, hierarki jelas, foto manusia dan aktivitas nyata. Prinsip referensi diikuti, bukan disalin.

- **Palet (token awal sudah ada di `tailwind.config.cjs`, final di A1):** Biru brand `#1B3590` (utama), Navy tua untuk area gelap dan footer, Merah logo `#E5352E` sebagai aksen kecil, netral abu-putih.
- **Font:** satu keluarga, **Plus Jakarta Sans**, self-host.
- **Header:** putih, ringan, logo kiri (`decaaid-black.png`), menu tengah, kanan: pencarian + switch bahasa ID | EN. Sticky halus. Dropdown rapi (mega menu ringan untuk Pilar Bisnis, memakai logo pilar).
- **Hero:** satu pesan utama, foto besar, overlay halus, satu tombol utama + satu sekunder. Bisa beberapa slide pesan korporat (bukan berita) lewat admin.
- **Pilar Bisnis:** grid kartu seragam (logo, nama, satu kalimat, tautan), teks nyata.
- **Berita:** maksimal 3 kartu seragam di beranda, rasio gambar sama, judul bukan huruf kapital semua.
- **Footer:** navy, memakai `decaaid-white.png`, lengkap: kantor pusat dan cabang, kontak, tautan cepat, sosmed, hak cipta.
- **Animasi:** halus saat scroll, hormati `prefers-reduced-motion`.
- **Tidak ada:** teks di dalam gambar, tag bertumpuk, campuran bahasa, tombol WA melayang.

### 5B. Aset logo (`public/img/logo/`)

| File | Dipakai untuk |
|---|---|
| `decaaid-black.png` | Logo DECAA.ID di latar terang (header) |
| `decaaid-white.png` | Logo DECAA.ID di latar gelap (footer, hero gelap) |
| `software.png`, `robotic.png`, `hardware.png`, `networking.png`, `trainingcentre.png`, `paperking.png`, `dapurkirana.png` | Logo pilar bisnis |
| `kompeddig.png` | Logo Komunitas Peduli Digital |

Saran nanti (tidak mendesak): versi SVG atau WebP agar lebih tajam dan ringan.

---

## 6. Sitemap (Dibangun Semua di Tahap A, Konten Dummy)

- **Beranda**
- **Tentang Kami:** Profil Perusahaan, Visi & Misi, Perjalanan Kami, Jajaran Kepemimpinan
- **Pilar Bisnis:** halaman daftar + halaman detail untuk 7 unit (satu template)
- **Portofolio:** Proyek (daftar + detail), Klien & Mitra
- **Berita:** daftar, kategori, detail
- **Keberlanjutan** (dulu "Komunitas", K16): Komunitas Peduli Digital
- **Karir:** umum, kirim CV ke email
- **Kontak:** form, kantor pusat Jakarta + cabang Pacitan dan Ponorogo, peta, kanal resmi (Lokasi Kantor digabung ke sini)
- **Pencarian** (tampilan hasil)
- **Pendukung:** Kebijakan Privasi, 404, 500, maintenance

*(Pusat Unduhan dikeluarkan sementara, lihat K14.)*

---

## 7. Todolist Pengerjaan

### TAHAP A: DESAIN HALAMAN DEPAN (PRIORITAS, TARGET ACC)

#### A0. Setup Proyek & Docker
- [x] Review `docker-compose.yml` (Sail PHP 8.3, MySQL 8.0, phpMyAdmin; tanpa Redis/Mailpit, sesuai rencana)
- [~] Install Laravel 9 dari nol + kompatibilitas PHP 8.3 diuji (langkah 1-6 di `A0-SETUP.md`)
- [~] `.env`, timezone `Asia/Jakarta`, locale default `id`
- [~] Vite + Tailwind CSS 3 + Alpine.js 3 (+ intersect, collapse) terpasang. *Konfigurasi sudah kuuji build-nya di lingkunganku (CSS, JS, font berhasil terbentuk); uji di Docker-mu menunggu verifikasi.*
- [~] Font Plus Jakarta Sans self-host lewat npm
- [~] Struktur folder: layouts, `components/ui`, `pages`, `lang/*.json`, `app/Support`
- [~] Routing berprefiks `/{locale}` (`id|en`), middleware `SetLocale`, redirect root ke `/id`, slug per bahasa (`config/site.php`), helper `lroute()` dan `switch_url()`
- [~] Lapisan konten dummy terpusat `App\Support\Content` (perusahaan, kantor, 7 unit, komunitas, navigasi)
- [~] Komponen placeholder gambar `<x-ui.img>`
- [~] Halaman uji + placeholder untuk semua route
- [ ] **Verifikasi checklist Bagian 9 di `A0-SETUP.md` oleh kamu**
- [ ] Temuan perbaikan A0: `lang/en.json` dan `lang/id.json` sudah dikirim di A2 (lokasi benar, kunci lengkap): hapus file lama `lang/en/en.json` dan `lang/en/id.json`; hapus salinan `resources/js/vite.config.js`; `faker_locale` di `config/app.php` jadi `id_ID`

#### A1. Design System
- [~] Token final: warna (`brand`, `accent`, `ink`), font, bayangan `card`, container, animasi `fade-up` dan `marquee` di `tailwind.config.cjs`; `postcss.config.cjs` terisi; utilitas `.section-y` dan `.eyebrow` di `app.css`
- [~] Komponen dasar di `components/ui/`: `button`, `card`, `section-title`, `badge`, `icon`, `breadcrumb`, `pagination`, `field`
- [~] Pola animasi muncul saat scroll: `<x-ui.reveal :delay="150">`
- [~] Halaman internal `/style-guide` (hanya environment local; dihapus sebelum staging)

#### A2. Layout Global
- [~] Header (sticky), menu desktop + dropdown/mega menu Pilar Bisnis (dari `Content::nav()`), penanda halaman aktif
- [~] Menu mobile (drawer, akordeon submenu)
- [~] Switch bahasa ID | EN (`partials/lang-switch`, mempertahankan halaman yang sedang dibuka)
- [~] Overlay pencarian (tampilan; fungsi penuh di Tahap B)
- [~] Footer lengkap (kantor pusat + 2 cabang, kontak, tautan cepat, sosmed DUMMY, hak cipta)
- [~] Meta dasar (description, canonical, hreflang, Open Graph dasar) dan tautan favicon. *Menunggu file `public/favicon.ico` darimu.*

#### A3. Beranda
- [~] Hero (animasi fade-up bertahap, foto dummy)
- [~] Ringkasan perusahaan + angka pencapaian (DUMMY, hitung naik saat terlihat)
- [~] Pilar Bisnis (grid 4x2: 7 kartu unit + 1 kartu "lihat semua")
- [~] Portofolio unggulan (3 kartu dummy)
- [~] Klien & Mitra: **opsi A** (strip logo berjalan, pause saat hover, statis bila reduced-motion, tombol ke halaman penuh) *(Q9 dijawab: ikut rekomendasi A)*
- [~] Keunggulan / Nilai perusahaan (4 poin, teks DUMMY)
- [~] Komunitas Peduli Digital (blok CSR singkat) *(Q10 dijawab: menu "Komunitas" tetap)*
- [~] Berita terbaru (3 kartu dummy)
- [~] CTA "Hubungi Kami"

#### A4. Tentang Kami
- [~] Komponen bersama untuk halaman dalam: `<x-ui.page-header>` (banner judul + breadcrumb), `<x-ui.cta-band>` (ajakan Hubungi Kami), `partials/about-nav` (tab antar halaman Tentang Kami)
- [~] Profil Perusahaan (siapa kami, sektor, pilar bisnis ringkas, 3 kantor); bagian Perjalanan dipindah ke halaman sendiri
- [~] Visi & Misi (visi, 5 misi, 5 nilai)
- [~] Perjalanan Kami (menggantikan Legalitas, lihat K19 dan revisi Tentang Kami di bawah)
- [~] Jajaran Kepemimpinan (6 kartu dummy, nama fiktif, foto placeholder; jabatan hanya CEO)
- [x] Catatan: halaman Legalitas dihapus. Subjek "Permintaan Dokumen" di form Kontak (`?subject=documents`) tetap ada, tidak dihapus

#### A5. Pilar Bisnis
- [~] Halaman daftar unit (grid 7 kartu: logo, nama, subjudul, ringkasan, tautan)
- [~] Template halaman detail unit (hero, tentang unit, 4 layanan, cara kerja 4 langkah, proyek terkait, unit lainnya, CTA), konten dummy untuk 7 unit
- [x] Catatan: proyek terkait diambil dari `Content::portfolio()`; sejak A6 ketujuh unit punya proyek dummy (pesan "akan segera ditampilkan" tetap ada bila suatu unit kosong)

#### A6. Portofolio & Klien
- [~] Daftar proyek + filter unit dan tahun (9 proyek dummy, filter instan tanpa muat ulang, tombol atur ulang, pesan bila kosong)
- [~] Detail proyek (gambar, ringkasan, lingkup pekerjaan, klien/tahun/unit, proyek lainnya). Slug yang tidak ada menampilkan 404
- [~] Halaman Klien & Mitra (opsi A): filter kategori; sejak v21 memakai logo asli dan 7 kategori (lihat revisi Klien & Mitra)
- [ ] Catatan: galeri foto di detail proyek ditunda sampai foto asli tersedia; filter memakai Alpine (di Tahap B dijadikan query database)

#### A7. Berita
- [~] Daftar berita (berita utama besar di halaman 1, grid kartu seragam, paginasi 7 per halaman, chip kategori)
- [~] Halaman kategori (5 kategori: Kegiatan, Komunitas, Bisnis, Teknologi, Edukasi; slug netral bahasa agar ID | EN tetap benar)
- [~] Detail artikel (tipografi baca nyaman, tombol bagikan WhatsApp/Facebook/X/LinkedIn + salin tautan, berita terkait). Slug atau kategori yang tidak ada menampilkan 404
- [ ] Catatan: 9 berita dummy; beranda tetap menampilkan 3 terbaru. Artikel impor WordPress dan terjemahan massal ada di Tahap B5-B6

#### A8. Komunitas, Karir, Kontak
- [~] Komunitas Peduli Digital (tentang, 3 fokus, 4 program DUMMY, ajakan bergabung lewat Kontak dengan subjek komunitas, tautan ke berita kategori Komunitas)
- [~] Karir (umum): alasan bergabung, proses seleksi, tombol **Kirim CV** ke `career@decaa.id` dengan subjek terisi otomatis ("Lamaran - [bidang yang dipilih]", pilihan bidang = 7 unit + Lainnya), tombol salin alamat email
- [~] Kontak: form (tampilan; kirim belum aktif sampai B4), subjek terisi dari `?subject=` (mis. `documents` untuk permintaan dokumen), kanal resmi (email, telepon DUMMY, WhatsApp DUMMY), kantor pusat Jakarta + cabang Pacitan dan Ponorogo dengan tautan Google Maps
- [ ] Catatan: peta interaktif (ide I4) belum dibuat; sementara tombol "Buka di Google Maps" per kantor. Telepon dan WhatsApp masih DUMMY

#### A9. Halaman Pendukung
- [~] Hasil pencarian: form + hasil dikelompokkan (Halaman, Pilar Bisnis, Portofolio, Berita), keadaan kosong dan "tidak ada hasil". Sementara mencari di data dummy; pencarian database di B4
- [~] Kebijakan Privasi (8 bagian + daftar isi, tanggal pembaruan). **DUMMY: draf awal, wajib ditinjau pihak hukum sebelum tayang**
- [~] 404 (memakai layout situs, bahasa mengikuti URL, ada kotak cari), 500 dan 503/maintenance (halaman mandiri tanpa Vite/database agar tetap tampil saat error)
- [ ] Catatan: menguji 500 dan 503 perlu `APP_DEBUG=false`; untuk 503 jalankan `php artisan down` lalu `php artisan up`

#### A10. Penyempurnaan & Review ACC
- [ ] Responsif: HP, tablet, laptop, layar lebar. *Audit kode statis tidak menemukan lebar tetap atau tabel yang berpotensi meluap, tetapi belum diuji di browser; perlu dicek olehmu (lebar 360, 768, 1024, 1440 px)*
- [~] Animasi dan transisi: keadaan awal `<x-ui.reveal>` dari CSS (`.reveal`) supaya tidak berkedip; tanpa JavaScript konten tetap tampil; reduced-motion menampilkan semuanya tanpa gerak
- [~] Aksesibilitas dasar: fokus keyboard dikunci di menu mobile dan overlay pencarian (`x-focustrap` di `app.js`), fokus pindah dan kembali ke pemicu, `aria-expanded`/`aria-controls`, label dialog mengikuti bahasa. Kontras warna belum diukur
- [~] Performa dasar: gambar di atas lipatan memakai `fetchpriority="high"`, gambar lain lazy. Dimensi eksplisit logo belum ada
- [ ] Konsistensi tipografi, spasi, bahasa ID dan EN di semua halaman (dikerjakan lewat putaran revisi di Bagian 7)
- [~] Staging `new.decaa.id`: halaman otomatis `noindex` bila `SITE_NOINDEX=true` di `.env`. Deploy dan DNS di sisimu
- [ ] **GERBANG: ACC desain halaman depan dari pihakmu**

---

### REVISI TAMPILAN HALAMAN DEPAN (BERJALAN, SEBELUM ACC)

Dikerjakan halaman demi halaman berdasarkan masukanmu. Urutan: **Beranda dulu**, lalu halaman lain.

**Beranda, putaran 1**
- [~] 1. Logo header diperbesar (logo dipangkas otomatis, K18)
- [~] 2. Hero lebih estetik: latar berlapis, kolase foto, tanpa tombol, tanpa teks "7 Pilar Bisnis"
- [~] 3. Angka pencapaian diganti komposisi foto (sisi kiri tetap)
- [~] 4. Kartu "Lihat semua pilar bisnis" dirombak (gradasi, tombol pil)
- [~] 5. Klien & Mitra: panel besar, dua baris logo berjalan berlawanan arah
- [~] 6. Logo Komunitas Peduli Digital diperbesar; label section jadi "Keberlanjutan"
- [~] 7. Logo footer diperbesar

**Beranda, putaran 2**
- [~] Ukuran logo header, footer, dan blok Keberlanjutan masih kecil di putaran 1: kini otomatis dipangkas (`LogoTrim`), tidak perlu perintah manual
- [~] "Komunitas" diganti "Keberlanjutan" di menu, footer, judul halaman, tombol "Pelajari Keberlanjutan", dan URL (K16)
- [~] Semua tombol dan elemen bergaya tombol: Capitalize Each Word (K17)
- [~] Klien & Mitra: label kategori (Pemerintahan, dst.) dihapus dari beranda

**Hero halaman dalam (semua halaman selain Beranda)**
- [~] Hero dirombak di komponen `<x-ui.page-header>` (berlaku otomatis di 17 halaman): latar berlapis (gradasi, pola titik, cahaya, lingkaran garis), penanda garis di label, garis dasar halus, dan **slot siluet kantor**
- [~] Foto kantor hero: `public/img/building.png` (atau .jpg/.jpeg/.webp). Sudah ditambahkan; tampilan diperbaiki di putaran 2 Tentang Kami

**Tentang Kami, putaran 1**
- [~] 1. Legalitas dihapus (halaman, menu, route, teks); diganti **Perjalanan Kami**: lini waktu vertikal kiri-kanan bergantian, foto per peristiwa, label tahun, tombol loncat ke tahun, penutup "Perjalanan ini belum selesai". Template siap isi lewat `Content::about()['journey']` (6 peristiwa DUMMY, foto di `public/img/journey/{tahun}.jpg`)
- [~] 2. Bagian "Perjalanan kami" di Profil Perusahaan dihapus
- [~] 3. Keempat tab didesain ulang: tab jadi pil melayang di tepi bawah hero; Profil (foto bertumpuk, angka ringkas dari data, sektor gaya editorial, ubin pilar bisnis, kantor pusat menonjol); Visi & Misi (panel visi, misi dengan penanda centang, nilai kolom editorial); Manajemen (pimpinan utama besar + grid anggota)
**Tentang Kami, putaran 2**
- [~] Perjalanan Kami dimulai 2018 (2018, 2019, 2021, 2022, 2024, 2026)
- [~] Tab navigasi kini **menempel di bawah header** saat di-scroll (tidak tenggelam)
- [~] Tab navigasi di HP: panah kiri/kanan (muncul hanya bila ada tab tersembunyi) dan tab aktif otomatis di tengah saat halaman dibuka
- [~] Angka ringkas 7 unit / 3 kantor dihapus dari Profil
- [~] "Sektor yang kami layani" diganti **Chairman Message** (foto, judul pesan, isi pesan, nama dan jabatan; DUMMY)
- [~] Kantor pusat Jakarta tampil dengan foto latar (memakai `public/img/offices/jakarta.jpg`, bila belum ada memakai foto `building` yang sama dengan hero)
- [~] **Perbaikan page-header:** foto kantor sebelumnya diproses sebagai siluet (`brightness-0 invert`), sehingga foto biasa berubah menjadi kotak putih. Kini foto ditampilkan sebagai foto (blend menyatu dengan latar biru, pudar ke kiri), mendukung `building.png/.jpg/.jpeg/.webp`
- [~] Manajemen diganti **Jajaran Kepemimpinan**: **CEO di kartu besar sendiri di atas judul "Board of Executive"**, lalu kartu foto dengan gradasi dan nama di bawah (gaya contoh) untuk anggota lain; jabatan hanya CEO
- [ ] Foto yang dibutuhkan (selama kosong tampil placeholder): `public/img/dummy/about-1.jpg`, `about-2.jpg`, `public/img/chairman.jpg`, `public/img/offices/jakarta.jpg` (opsional), `public/img/team/1.jpg` sampai `6.jpg`, `public/img/journey/2018.jpg`, `2019.jpg`, `2021.jpg`, `2022.jpg`, `2024.jpg`, `2026.jpg`
- [ ] Hapus file lama `resources/views/pages/about/legal.blade.php`
- [ ] Checklist Audit-Ready (Bagian 8) menyebut Legalitas: perlu diputuskan apakah item itu diganti atau dihapus

**Pilar Bisnis (ganti istilah dari Unit Bisnis)**
- [~] Semua teks ID dan EN diganti: menu dan mega menu, judul halaman daftar dan detail, beranda, profil perusahaan, footer, pencarian, karir, jajaran kepemimpinan, perjalanan, kategori filter portofolio
- [~] URL: `/id/pilar-bisnis` dan `/en/business-pillars`
- [ ] Cek `lang/id.json` dan `lang/en.json` di proyekmu: bila ada kata "Unit Bisnis" di sana, ganti manual (file itu tidak ada di arsip yang kuterima)
- [ ] Ganti nama folder logo/aset bila ada yang memuat kata "unit" (opsional, tidak memengaruhi tampilan)

**Pilar Bisnis, redesain tampilan (v20)**
- [~] Halaman daftar: grid **bento** 7 kartu (lebar 7/5, 4/4/4, 5/7 di layar lebar; 2 kolom di tablet; 1 kolom di HP) dengan dua kartu gelap diagonal, logo dalam tile putih, nomor besar, garis aksen dan tombol panah animasi saat hover
- [~] Band gelap "Mengapa DECAA.ID / Satu pintu untuk kebutuhan teknologi Anda" (3 poin, **DUMMY**, teks di `Labels.php` kunci `units.band_*`)
- [~] Halaman detail: panel logo bergradasi (tanpa penanda "Pilar xx dari 07"); **Layanan** dua kolom (judul menempel di kiri, 4 kartu bernomor di kanan); **Cara kerja** di section gelap dengan garis penghubung antar tahap; **Proyek terkait** dengan zoom foto saat hover, badge tahun, tombol ke portofolio; **Pilar lainnya** berupa kartu logo + tombol panah **sebelumnya/berikutnya** (berputar)
- [~] Tetap dwibahasa ID/EN, tombol Capitalize Each Word (K17), `prefers-reduced-motion` dihormati lewat `.reveal`, tanpa teks di dalam gambar
- [ ] Cek di browser: lebar 360, 768, 1024, 1440 px; kedua bahasa; ketujuh halaman detail (terutama pilar tanpa subjudul dan pilar tanpa proyek)
- [ ] Logo pilar tampil di tile putih (aman untuk kartu gelap). Bila logo PNG berlatar transparan terlalu kecil/besar, atur `h-20 w-44` di tile logo

- [~] **Angka-angka dihapus** atas permintaanmu: kartu angka ringkas di halaman daftar, tiga ubin angka dan penanda "Pilar 02 dari 07" di halaman detail

**Klien & Mitra, logo asli + redesain (v21)**
- [~] Logo dibaca dari `public/img/clients/{kategori}/{nomor}.png`. Jumlah per kategori di `Content::CLIENT_FOLDERS`: kementerian 4, pemerintah 18, lembaga 13, universitas 25, organisasi 4, perusahaan 6, swasta 20 (total 90). Folder `pemerintahan` dipakai otomatis bila `pemerintah` tidak ada; ekstensi png/jpg/jpeg/webp/svg dikenali
- [~] Kategori diganti dari 4 dummy (Pemerintahan, Pendidikan, Swasta, Mitra Teknologi) menjadi **7 kategori sesuai foldermu**; nama klien dummy dihapus, logo tampil tanpa nama (alt = "Kategori nomor"). Kategori "Mitra Teknologi" tidak ada lagi; tambahkan folder baru bila nanti perlu
- [~] Halaman: pengantar tengah, filter pil melayang yang **menempel di bawah header**, logo dikelompokkan per kategori dalam panel abu muda dengan judul bergaris aksen; kartu putih berasio seragam, logo abu-abu lalu berwarna saat hover (di layar sentuh langsung berwarna), animasi muncul halus; filter instan (Alpine)
- [~] Strip beranda kini memakai 24 logo selang-seling antar kategori (`Content::clients(24)`), bukan 90, agar kecepatan marquee tetap nyaman
- [ ] Nama klien asli belum ada: bila sudah ada, tambahkan ke `Content::clients()` dan tampilkan di bawah logo (izin tampil logo tetap dicek, lihat Risiko)

**Detail Berita, redesain (v22)**
- [~] Hero artikel gelap bergaya situs (kategori sebagai pil, judul besar di tengah, tanggal dan **perkiraan waktu baca**), foto utama besar **menumpuk di tepi bawah hero**
- [~] Bilah **progres baca** tipis di paling atas layar (biru ke merah), bergerak mengikuti isi artikel
- [~] Isi: kalimat pembuka besar dengan garis aksen, subjudul bertanda garis gradasi, lebar baris nyaman dibaca; **rel bagikan melayang di kiri** (desktop: WA, FB, X, in, salin tautan), panel bagikan di akhir artikel untuk semua layar
- [~] Navigasi **berita sebelumnya / berikutnya** (urut tanggal), tombol Semua Berita
- [~] Berita terkait: kartu dengan zoom foto saat hover, badge kategori di atas foto, tombol Baca Selengkapnya
- [ ] Cek di browser: berita tanpa subjudul (`h`), judul sangat panjang, berita terbaru (tanpa tombol berikutnya) dan terlama (tanpa tombol sebelumnya), tampilan HP, kedua bahasa
- [ ] Foto berita masih DUMMY (`public/img/dummy/news-*.jpg`); pakai rasio 16:9 agar tidak terpotong

**Ikon media sosial (v23)**
- [~] Komponen baru `<x-ui.social-icon name="instagram|facebook|youtube|tiktok|linkedin|x|whatsapp">` (ikon merek solid, ikut warna teks). Sumber: Simple Icons (CC0); LinkedIn dari Font Awesome Free (CC BY 4.0, atribusi ada di komentar komponen)
- [~] Footer "Ikuti kami": teks diganti tombol ikon bulat (Instagram, Facebook, YouTube, TikTok, **LinkedIn ditambahkan**), ada `aria-label`/`title`. URL sosmed masih DUMMY (`'#'`) di `Content::company()['social']`, termasuk `linkedin`
- [~] Detail Berita: rel bagikan dan panel bagikan memakai ikon WhatsApp, Facebook, X, LinkedIn (panel bawah: ikon + nama)

**Menunggu keputusan / bahan darimu**
- [ ] Foto dummy baru agar kolase terisi: `public/img/dummy/hero.jpg`, `hero-2.jpg`, `hero-3.jpg`, `about-1.jpg`, `about-2.jpg`, `community.jpg` (selama kosong tampil placeholder berlabel)
- [ ] Kategori berita "Komunitas": ganti jadi "Keberlanjutan" juga?
- [ ] Hapus file lama `app/Console/Commands/TrimLogos.php` (tidak dipakai lagi)

**Halaman berikutnya (menunggu masukanmu setelah Beranda disetujui)**
- [ ] Portofolio (daftar + detail), Berita, Keberlanjutan, Karir, Kontak, Pencarian, Privasi, 404/500/503

---

### TAHAP B: BACK-OFFICE & ADMIN (SETELAH ACC)

#### B1. Fondasi Data & Bahasa
- [ ] Skema database (halaman, section, unit, portofolio, klien, berita, kategori, tim, lowongan umum, pesan, menu, pengaturan, media, terjemahan, redirect)
- [ ] Sistem terjemahan (kolom JSON per bahasa; versi paket dipastikan kompatibel dengan Laravel 9)
- [ ] Slug unik per bahasa + redirect otomatis saat slug berubah
- [ ] Layer media: upload, validasi, konversi WebP, thumbnail responsif, alt text
- [ ] Role: Super Admin, Admin, Editor

#### B2. Admin: Fondasi
- [ ] Login aman (rate limit, lupa password), opsional 2FA
- [ ] Layout admin modern: sidebar, topbar, breadcrumb, mode terang/gelap, responsif
- [ ] Komponen reusable: tabel (cari/filter/sort/paginasi), form, toggle, select, modal konfirmasi, toast
- [ ] Upload gambar (drag-and-drop, preview, crop)
- [ ] Rich text editor yang bersih
- [ ] Tab bahasa ID | EN + indikator status terjemahan
- [ ] Dashboard, Media Library, log aktivitas

#### B3. Admin: Modul Konten
- [ ] Pengaturan situs (logo, kontak, kantor pusat/cabang, sosmed, footer)
- [ ] Menu (drag-and-drop)
- [ ] Halaman + page builder section (+ live preview)
- [ ] Hero, Pilar Bisnis, Portofolio, Klien & Mitra, Berita, Kepemimpinan, Chairman Message, Perjalanan Kami (lini waktu), Statistik, Komunitas, Karir
- [ ] Pesan masuk dari Kontak (status baca, ekspor)
- [ ] Pengguna & role
- [ ] SEO per halaman

#### B4. Menghubungkan Frontend ke Database
- [ ] Ganti isi `App\Support\Content` menjadi query database (view tidak ditulis ulang)
- [ ] Form Kontak aktif (email ke `info@decaa.id`, anti-spam)
- [ ] Pencarian situs berfungsi (lintas berita, unit, portofolio, halaman)

#### B5. Terjemahan Otomatis (lihat Bagian 4A)
- [ ] Lapisan penyedia terjemahan + glosarium proteksi
- [ ] Tombol "Terjemahkan otomatis" di admin + status terjemahan
- [ ] Perintah artisan terjemahan massal via queue
- [ ] Label "Diterjemahkan otomatis" dan fallback ke ID

#### B6. Migrasi dari WordPress
- [ ] Bahas jumlah artikel dan ukuran media (K15)
- [ ] Ekspor semua artikel, kategori, tag, dan **seluruh foto** dari WordPress
- [ ] Skrip impor (rapikan HTML, perbaiki path gambar, konversi WebP)
- [ ] Peta redirect 301 URL lama ke baru
- [ ] Terjemahan massal artikel lama (B5)
- [ ] Verifikasi hasil impor (sampel + hitung jumlah)

---

### TAHAP C: SEO, PERFORMA, KEAMANAN

- [ ] Title dan meta description unik per halaman, Open Graph, Twitter Card
- [ ] Heading terstruktur (satu H1), breadcrumb
- [ ] `hreflang` ID/EN, canonical, sitemap XML per bahasa, `robots.txt`
- [ ] JSON-LD: `Organization`, `LocalBusiness` (kantor pusat + cabang), `BreadcrumbList`, `Article`
- [ ] Search Console dan Bing Webmaster, kirim sitemap
- [ ] Analitik ringan (Plausible/Umami atau GA4)
- [ ] Gambar WebP/AVIF, `srcset`, lazy, tanpa layout shift
- [ ] Minify, hapus CSS tak terpakai, cache route/config/view, kompresi Gzip/Brotli
- [ ] Pertimbangkan Redis di tahap ini
- [ ] Target Lighthouse >= 90 (mobile), LCP < 2,5 dtk, CLS < 0,1
- [ ] HTTPS + HSTS, security headers (CSP dll), rate limiting, honeypot + Turnstile/reCAPTCHA
- [ ] Validasi dan sanitasi input, batasi tipe dan ukuran upload
- [ ] Backup otomatis DB dan media, `composer audit` rutin
- [ ] Uji aksesibilitas penuh

### TAHAP D: QA & GO-LIVE

- [ ] Uji lintas browser dan perangkat
- [ ] Uji form Kontak dan email masuk
- [ ] Panduan penggunaan admin + pelatihan singkat
- [ ] Checklist pra-live (Bagian 8)
- [ ] Migrasi total dari `new.decaa.id` ke `decaa.id`, redirect aktif, pantau Search Console 1-2 minggu
- [ ] Rencana rollback siap

### TAHAP E: PASCA LIVE

- [ ] Monitoring uptime dan error
- [ ] Evaluasi kecepatan dan SEO (2-4 minggu)
- [ ] Jadwal rutin: konten, update dependensi, uji pemulihan backup
- [ ] Rencana naik versi Laravel (lihat Bagian 10)

---

## 8. Checklist "Audit-Ready" (Pengganti Kriteria Tertulis)

- [ ] Identitas jelas: nama, profil, visi-misi, bidang usaha
- [ ] ~~Legalitas tampil~~ (halaman dihapus, K19): tentukan pengganti, mis. cara meminta dokumen lewat Kontak
- [ ] Alamat kantor pusat dan cabang, email, telepon resmi mudah ditemukan
- [ ] Pilar bisnis dan bukti kerja (portofolio, klien) jelas
- [ ] Struktur menu bersifat korporat, berita bukan pusat halaman
- [ ] Tampilan konsisten (font, warna, bahasa), tanpa teks di dalam gambar
- [ ] Konten terbaru dan tanggal konsisten
- [ ] Responsif di HP, cepat, HTTPS aktif
- [ ] Dwibahasa ID/EN yang benar
- [ ] Kebijakan Privasi tersedia
- [ ] Tidak ada link rusak atau halaman kosong
- [ ] Aksesibilitas dasar terpenuhi

---

## 9. Ide Tambahan & Backlog

- **Backlog (ditunda):** Pusat Unduhan (Company Profile PDF, katalog), lowongan spesifik, testimoni klien
- **(I1)** Live preview di page builder sebelum Publish
- **(I2)** Draft, jadwal tayang, dan riwayat revisi
- **(I3)** Analitik tanpa cookie (Plausible/Umami)
- **(I4)** Peta interaktif kantor pusat dan cabang dengan tombol petunjuk arah
- **(I5)** Dashboard "Kesehatan Situs": terjemahan belum ditinjau, gambar tanpa alt, halaman tanpa meta description
- **(I6)** Gambar share (Open Graph) otomatis per artikel
- **(I7)** Video hero ringan dengan fallback gambar di HP
- **(I8)** Mode terang/gelap dan pintasan keyboard di admin
- **(I9)** Halaman "Mitra & Sertifikasi" bila nanti ada (ISO, partner resmi vendor)

---

## 10. Risiko Utama

| Risiko | Dampak | Mitigasi |
|---|---|---|
| **Laravel 9 sudah EOL** (tanpa patch keamanan resmi); PHP 8.3 belum resmi diuji untuk versi ini | Celah keamanan, peringatan deprecation | Keputusan diterima. Batasi paket pihak ketiga, pilih versi paket kompatibel, `composer audit` rutin, proteksi server (HTTPS, security headers, rate limit, WAF seperti Cloudflare), kode "siap naik versi", jadwalkan upgrade bertahap setelah live |
| Konten asli terlambat | Situs terlihat kosong | Dummy natural + placeholder; ganti saat siap |
| Logo klien dipajang tanpa izin | Masalah hubungan klien | Tampilkan hanya yang mengizinkan |
| Peringkat Google turun saat migrasi | Trafik turun | Redirect 301 lengkap + Search Console |
| Terjemahan mesin keliru | Kesan kurang profesional | Halaman inti wajib ditinjau, glosarium brand, status terjemahan di admin |
| Lingkup terlalu besar | Molor | Tahap A dulu sampai ACC, fitur lain menyusul |
| Impor banyak foto WordPress | Beban disk dan waktu | Konversi WebP saat impor, proses via queue |

---

## 11. Definition of Done

- Seluruh konten halaman depan dapat diubah dari admin tanpa menyentuh kode.
- Situs tampil sempurna di HP, tablet, dan desktop.
- Lighthouse >= 90 di keempat kategori (mobile).
- Switch bahasa ID/EN berfungsi di semua halaman dengan URL, hreflang, dan SEO yang benar.
- Tidak ada link rusak; redirect dari URL WordPress lama berjalan.
- Backup otomatis aktif dan sudah diuji pemulihannya.
- Panduan admin tersedia dan admin sudah dilatih.

---

## 12. Progress Log

| Tanggal | Perubahan |
|---|---|
| 30 Okt 2026 | v1: todolist awal dan pertanyaan keputusan |
| 30 Okt 2026 | v2: keputusan K1-K9 dicatat; dirombak jadi Tahap A (desain depan) dulu, lalu B-E |
| 30 Okt 2026 | v3: jawaban Q1-Q7 dicatat (K10-K15); 7 unit + Komunitas Peduli Digital; kantor pusat Jakarta + 2 cabang; staging `new.decaa.id`; Pusat Unduhan ditunda; Karir umum; ditambah usulan Klien & Mitra (4E); **A0 dikirim**: 19 file + `A0-SETUP.md` (routing dwibahasa, Content dummy, Tailwind/Vite/Alpine, komponen placeholder, halaman uji) |
| 30 Okt 2026 | v4: `tailwind.config.cjs` dan `postcss.config.cjs` diisi; **A1 dikirim**: 8 komponen UI, `reveal`, `app.css`, `style-guide`, `routes/web.php`; temuan perbaikan A0 dicatat |
| 30 Okt 2026 | v5: **A2 dikirim**: layout, header (sticky, mega menu, drawer mobile, switch bahasa, pencarian), footer, `lang/id.json` dan `lang/en.json` (lokasi benar) |
| 30 Okt 2026 | v6: Q9 = opsi A, Q10 = menu "Komunitas" tetap (ikut rekomendasi, boleh direvisi di akhir). **A3 dikirim**: `home.blade.php`, `Content.php` (home, stats, projects, clients, posts), `lang/*.json` |
| 30 Okt 2026 | v7: **A4 dikirim**: `Content.php` (tambah `ui()` dan `about()`), `page-header`, `cta-band`, `about-nav`, dan 4 halaman `pages/about/*` (profil, vision, legal, team). Semua teks dan nama bertanda DUMMY, dua bahasa |
| 30 Okt 2026 | v8: **A5 dikirim**: `Content.php` (tambah `unitDetail()` dan `unitsPage()`), `pages/units/index` dan `pages/units/show` |
| 30 Okt 2026 | v9: **A6 dikirim**: `Content.php` (`portfolio()` 9 proyek, `projects()` kini 3 unggulan untuk beranda, `project()` mengembalikan null bila tak ada, `portfolioPage()`, `clientCategories()`, `clientsPage()`), `pages/portfolio/index` dan `show`, `pages/clients` |
| 30 Okt 2026 | v10: **A7 dikirim**: `Content.php` (`news()` 9 berita, `posts()` kini 3 terbaru, `post()` dan `category()` null bila tak ada, `newsCategories()`, `newsPage()`), `partials/news-list`, `pages/news/index`, `category`, `show` |
| 30 Okt 2026 | v11: **A8 dikirim**: `Content.php` (`communityPage()`, `careersPage()`, `contactPage()`), `pages/community`, `pages/careers`, `pages/contact` |
| 30 Okt 2026 | v12: **A9 dikirim**: `Content.php` (`searchPage()`, `search()`, `privacyPage()`), `pages/search`, `pages/privacy`, `errors/404`, `errors/500`, `errors/503` |
| 30 Okt 2026 | v13: **A10 sebagian**: perbaikan aksesibilitas dialog, animasi reveal tanpa kedip, noindex staging, prioritas gambar utama, label `nav.menu` |
| 30 Okt 2026 | v14: **Revisi Beranda putaran 1** (7 butir): logo, hero kolase tanpa tombol, angka diganti foto, kartu ajakan unit, Klien & Mitra dua baris, label Keberlanjutan, logo footer; helper `.animate-float` |
| 30 Okt 2026 | v15: **Revisi Beranda putaran 2**: logo dipangkas otomatis (`LogoTrim`, `logo_url()`), K16 menu Keberlanjutan, K17 tombol Capitalize Each Word, label kategori di Klien & Mitra dihapus; bagian "Revisi Tampilan Halaman Depan" ditambahkan |
| 30 Okt 2026 | v16: **Hero halaman dalam dirombak** (`components/ui/page-header`), slot siluet kantor `public/img/building.png` |
| 30 Okt 2026 | v17: **Revisi Tentang Kami putaran 1**: Legalitas diganti Perjalanan Kami (K19), Perjalanan dihapus dari Profil, empat tab didesain ulang. File: `config/site.php`, `app/Support/Content.php`, `partials/about-nav`, `pages/about/profile`, `vision`, `team`, `journey` (baru) |
| 30 Okt 2026 | v18: **Revisi Tentang Kami putaran 2** (K20): Perjalanan mulai 2018, tab menempel, Chairman Message, foto kantor pusat, perbaikan foto page-header (`components/ui/page-header`), Jajaran Kepemimpinan. File: `config/site.php`, `Content.php`, `about-nav`, `page-header`, `profile`, `team` |
| 30 Okt 2026 | v19: **Unit Bisnis diganti Pilar Bisnis** (K21): `Content.php`, `Labels.php`, `config/site.php` (slug); halaman Pilar Bisnis (daftar + detail) ikut berubah. Redesain halaman Pilar Bisnis menyusul bila diminta |
| 30 Okt 2026 | v20: **Redesain Pilar Bisnis** (daftar bento + angka ringkas + band "Satu pintu"; detail dengan panel logo, layanan sticky, cara kerja gelap, proyek, navigasi sebelumnya/berikutnya). File: `pages/units/index.blade.php`, `pages/units/show.blade.php`, `app/Support/Labels.php` (kunci baru `units.*`). Tanpa perubahan `Content.php`, route, atau CSS |
| 30 Okt 2026 | v21: angka di halaman Pilar Bisnis dihapus; **Klien & Mitra** memakai logo asli (90 logo, 7 kategori) dan didesain ulang. File: `pages/clients.blade.php`, `pages/units/index.blade.php`, `pages/units/show.blade.php`, `pages/home.blade.php` (strip 24 logo), `app/Support/Content.php` (`clients()`, `clientCategories()`, `clientsPage()`), `app/Support/Labels.php` (kunci `units.*` yang tak terpakai dibuang) |
| 30 Okt 2026 | v22: **Redesain detail Berita** (hero + foto menumpuk, progres baca, rel bagikan, sebelumnya/berikutnya, terkait). File: `pages/news/show.blade.php`, `app/Support/Labels.php` (kunci `news.*`). Tanpa perubahan `Content.php`, route, atau CSS |
| 30 Okt 2026 | v23: ikon merek untuk footer dan bagikan berita, LinkedIn ditambahkan. File: `components/ui/social-icon.blade.php` (baru), `partials/footer.blade.php`, `pages/news/show.blade.php`, `app/Support/Content.php` (kunci `social.linkedin`) |