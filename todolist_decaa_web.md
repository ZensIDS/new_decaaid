# TODOLIST: Rebuild Website DECAA.ID (Laravel 9)

> Tujuan: website korporat berkelas profesional tinggi yang mengikuti prinsip referensi (Kawan Lama Group), dengan admin panel buatan sendiri yang ramah pengguna, SEO kuat, performa cepat, dan dukungan dwibahasa (ID/EN).

**Versi:** 39 (diperbarui 3 Nov 2026)
**Status:** **Tahap B (Admin): modul Proyek (B3g, end-to-end: admin + frontend dari database) sudah dikirim (v39), menunggu `php artisan migrate`, `php artisan content:import-projects`, dan dicek di Docker-mu.** **Tahap B6 (impor berita WordPress): 171 berita + gambar unggulan siap diimpor lewat `php artisan content:import-wp` (v38), menunggu `php artisan migrate`, salin folder `uploads` ke `public/wp-uploads`, dan dicek di Docker-mu. Tanpa terjemahan (kolom EN dikosongkan, hemat kuota API).** Perbaikan v38: nama config terjemahan yang salah ketik (`transalator.php` menjadi `translator.php`) dan border area tulis editor berita. **Tahap B (Admin): terjemahan otomatis untuk Berita (B5, bagian awal) sudah dikirim (v37), menunggu `php artisan migrate`, pengisian `.env` penyedia terjemahan, dan dicek di Docker-mu.** **Tahap B (Admin): modul konten kedua, Pilar & Brand (B3c), sudah dikirim (v36, admin + frontend dari database), menunggu `php artisan migrate`, `php artisan content:import-pillars`, `php artisan storage:link`, dan dicek di Docker-mu.** Modul konten pertama, Berita & Kategori (B3d), sudah lengkap (v35: view admin + view publik dilengkapi, nama file PSR-4 dibetulkan), menunggu `php artisan migrate`, `php artisan content:import-news`, `php artisan storage:link`, dan dicek di Docker-mu.** QA1-QA18 sudah dijawab dan dicatat sebagai K27-K32. Sedang berjalan: Tahap B-awal (login, dashboard dummy, CRUD pengguna); BA1 (fondasi login dan pengguna), BA2 (layout admin, komponen, login, dashboard dummy), dan BA4 putaran 1 (revisi tampilan) sudah dikirim; **BA3 (CRUD pengguna, profil, log aktivitas) sudah dikirim, menunggu dicek di Docker-mu**; badge "Admin" di dekat logo sidebar sudah dihapus. Berikutnya: putaran revisi desain admin (BA4) bila ada masukan, lalu modul konten pertama (Berita, B3d).** A0 (fondasi) menunggu verifikasimu; A1 (design system) menunggu dicek lewat `/style-guide`; A2 (layout global) dan A3 (beranda) file sudah dikirim, menunggu dicek di browser; A4 (Tentang Kami), A5 (Pilar Bisnis), A6 (Portofolio & Klien), A7 (Berita), A8 (Komunitas, Karir, Kontak), dan A9 (halaman pendukung) file sudah dikirim, menunggu dicek di browser. **Sedang berjalan: Revisi Tampilan Halaman Depan (Bagian 7, setelah A10)**, dimulai dari Beranda; Beranda putaran 2 sudah dikirim, menunggu kamu cek di browser. **Tentang Kami (putaran 1 dan 2) sudah dikirim**, menunggu kamu cek di browser. **Pilar Bisnis (daftar + detail) sudah didesain ulang**, menunggu kamu cek di browser. **Klien & Mitra sudah didesain ulang dengan logo asli**, menunggu kamu cek di browser. **Detail Berita (single page) sudah didesain ulang**, menunggu kamu cek di browser. Halaman lain menyusul satu per satu. **Putaran revisi dari dokumen `Revisi_Website_decaaid.pdf` (2 Nov 2026) sedang berjalan, fokus tampilan dulu; bahan yang belum jelas dikosongkan.** R1 (slogan, Pilar Bisnis = 8 jenis bisnis + brand, menu, hapus Jajaran Kepemimpinan, Karir ikut pilar) sudah dikirim, menunggu kamu cek di browser. Lihat bagian "REVISI DARI DOKUMEN PDF".
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
9. **Info lokasi file wajib jelas.** Nama kartu unduhan hanya menampilkan nama file (mis. `form.blade`), jadi setiap pengiriman menyertakan **daftar `path tujuan → nama file`** di teks balasan (nama file yang sama seperti `index.blade` atau `form.blade` sering muncul di folder berbeda). Path mengikuti struktur proyek, mis. `resources/views/pages/admin/users/form.blade.php`.
10. **Pola desain admin dikunci** (lihat "Pola Desain Admin" di Tahap B): semua modul admin berikutnya memakai komponen dan gaya yang sama, tidak membuat gaya baru.
11. **Arsip yang dikirim ke Claude** (zip): `app`, `config`, `database`, `lang`, `resources`, `routes`, dan `todolist_decaa_web.md`. Tambahkan `tailwind.config.cjs` dan `vite.config.js` bila ada masalah gaya/build. **Jangan** sertakan `.env` (berisi kunci rahasia), `vendor`, `node_modules`, `storage`, `public/build`, `.git`; folder `__MACOSX` dan `.DS_Store` dari zip Mac boleh diabaikan. `public/img` hanya bila ada masalah gambar.

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
| K10 | Unit bisnis *(digantikan K22: 8 jenis bisnis)* | **Dipisah**: Software, Robotics, Hardware, Networking Services, Training & Research Centre, PaperKing (Stationery & Office Supply), Dapur Kirana (Food & Beverages) | Konfirmasi nama: Q8 |
| K11 | CSR | **Komunitas Peduli Digital** (komunitas CSR DECAA.ID), bukan unit bisnis | Ditempatkan di menu "Komunitas" (Q10) |
| K12 | Nama di website | **DECAA.ID** | |
| K13 | Kantor | **Pusat: Jakarta** (Ad Premier Office Park, Jl. TB Simatupang, Ragunan, Pasar Minggu). **Cabang: Pacitan** (Jl. Letjend R. Suprapto, Sidoarjo) dan **Ponorogo** (Jl. Sultan Agung) | |
| K14 | Pusat Unduhan | **Ditunda** | Masuk backlog (Bagian 9) |
| K15 | Jumlah artikel WordPress | **Dibahas setelah ACC desain depan** | |
| K16 | Nama menu CSR | Menu dan halaman **"Komunitas" diganti "Keberlanjutan"** (EN: Sustainability); URL `/id/keberlanjutan` dan `/en/sustainability`. Nama program tetap **Komunitas Peduli Digital** | Menggantikan Q10. Kategori berita "Komunitas" belum diubah (menunggu konfirmasi) |
| K17 | Gaya teks tombol | **Semua tombol dan elemen bergaya tombol: Capitalize Each Word** (CSS `capitalize`, berlaku ID dan EN) | Tombol baru wajib mengikuti |
| K18 | Logo | Logo PNG dipangkas otomatis ruang kosongnya (`logo_url()`, salinan di `public/img/_trim/`, file asli tidak diubah) | Ukuran logo diatur lewat class tinggi (`h-7`, `h-9`, dst.) |
| K19 | Menu Tentang Kami | **Legalitas dihapus** (halaman, menu, route) dan **diganti "Perjalanan Kami"** (`/id/tentang-kami/perjalanan-kami`, `/en/about-us/our-journey`). Bagian "Perjalanan kami" di Profil Perusahaan dihapus (pindah ke halaman sendiri) | Menggantikan sebagian K-Legalitas di Bagian 4B, 8, dan B3. URL lama `/tentang-kami/legalitas` kini 404 (aman di staging; tambahkan redirect 301 bila perlu) |
| K20 | Tentang Kami, putaran 2 *(kepemimpinan dihapus oleh K23; tahun mulai ditinjau di Q14)* | **Perjalanan Kami mulai 2018** (semua tahun dan nama file foto ikut). Menu "Manajemen" diganti **"Jajaran Kepemimpinan"** (EN: Leadership; URL `/id/tentang-kami/jajaran-kepemimpinan`, `/en/about-us/leadership`), kartu hanya nama + foto, **jabatan hanya untuk CEO**. "Sektor yang kami layani" diganti **Chairman Message**. Angka ringkas (7 unit, 3 kantor, dst.) dihapus dari Profil | |
| K21 | Istilah "Unit Bisnis" | **Diganti "Pilar Bisnis"** (EN: Business Pillars) di semua teks, menu, footer, pencarian, dan URL: `/id/pilar-bisnis` dan `/en/business-pillars` (detail: `/id/pilar-bisnis/{slug}`). Nama internal (route `units.*`, method `units()`, file `pages/units/`) sengaja tidak diubah agar kode aman | Catatan lama (K10, Q8, log) yang menyebut "unit bisnis" = Pilar Bisnis. URL lama `/unit-bisnis` kini 404 (staging; redirect 301 bila perlu) |
| K22 | Pilar Bisnis = **jenis bisnis** (revisi PDF butir 2 dan 5) | **8 pilar**: Software & Robotics; Hardware & Peripheral; Networking Services; Commercial Technology; Training & Research Centre; Stationery & Office Supply; Lifestyle Store; Food & Beverage. Nama divisi/brand ada **di dalam** pilar (13 brand + 4 di Commercial Technology). Menu = daftar teks saja (tanpa logo). Data di `app/Support/Pillars.php` | Menggantikan K10 (7 unit) dan Q8. Slug lama (`/pilar-bisnis/software` dst.) kini 404. Ejaan \"Stationary\" di dokumen ditulis **Stationery**; \"Food & Beverages\" jadi **Food & Beverage** sesuai dokumen |
| K23 | Jajaran Kepemimpinan | **Dihapus** (halaman, menu, route, data) | Menggantikan K20 bagian kepemimpinan. Chairman Message tidak disebut di revisi, jadi **tetap** |
| K24 | Slogan | **Mitra Solusi untuk Segala Akselerasi**; teks bawah: \"Ekosistem digital dengan inovasi yang inklusif, aman, dan berkelanjutan bagi semua lini industri\" (typo \"industry\" di dokumen ditulis \"industri\"). Label kecil di atas judul hero dikosongkan | EN: terjemahan draf |
| K25 | Menu Portofolio | Ditambah **Penghargaan** (URL `/id/portofolio/penghargaan`, `/en/portfolio/awards`) berisi penghargaan dan sertifikat seluruh brand | Halaman dibuat di R4 (route dan menu sudah ada sejak R1) |
| K26 | Isi brand | Deskripsi brand memakai teks dokumen apa adanya (ID); EN = terjemahan draf. Instagram, TikTok, dan deskripsi yang belum ada **dikosongkan** (tidak tampil). Tombol \"Selengkapnya\" membuka situs brand (More Info) | Tautan di dokumen berawalan `http://www.`: konfirmasi apakah dipakai apa adanya atau `https://` |
| K27 | Admin: lingkup halaman dan menu (QA1-QA3) | Struktur halaman **tetap** + modul **Halaman Kustom**; menu **otomatis** (admin hanya ubah label, sembunyikan, urut tingkat atas); dikerjakan **modul demi modul, end-to-end** (admin + frontend per modul) | Menyempurnakan K4 |
| K28 | Data awal dan WordPress (QA4, QA7) | Data kode **diimpor otomatis** ke database; berita sementara **dummy**, nanti diimpor dari WordPress **beserta foto**; disediakan perintah impor + tutorial. Media Library (WebP, thumbnail, alt, batas 5 MB, logo dipangkas otomatis) | Detail di B6 |
| K29 | Pengguna admin (QA5) | Peran Super Admin / Admin / Editor; 2FA wajib untuk Super Admin dan Admin; alamat `/admin` ber-noindex + batas login; antarmuka admin Bahasa Indonesia. **Super Admin awal: `decaa@decaa.id`** | Password awal disampaikan lewat chat, tidak ditulis di berkas; wajib diganti saat login pertama |
| K30 | Konten admin (QA6-QA13) | Teks antarmuka tetap di kode; Pilar/Brand boleh tambah-ubah-urut-sembunyikan (hapus hanya Super Admin); Klien: nama opsional, kategori dikelola, beranda via centang, kolom izin tampil; Proyek: satu pilar, galeri disiapkan; **Penghargaan sederhana** (tanpa jenis, penerbit, PDF; terbaru manual); Berita: Draf/Terbit/Terjadwal, kategori dikelola penuh dengan slug aman terhadap route, tanpa video/tag/penulis; **Statistik, Kepemimpinan, Legalitas tanpa modul admin** | |
| K31 | Email (QA14) | Server email sendiri `mail.decaa.id` (SMTP 465). **Hanya satu akun: `info@decaa.id`** untuk pengirim, penerima pesan Kontak, dan notifikasi (tidak ada akun email lain). Selebihnya sesuai rekomendasi (balasan otomatis, honeypot + batas kirim, simpan 12 bulan) | Kredensial hanya di `.env` |
| K32 | Sistem (QA15-QA18) | Tab ID/EN dan status terjemahan sejak awal, tombol terjemah di B5; SEO + Redirect + sitemap di admin; mode pemeliharaan dari admin, `SITE_NOINDEX` tetap di `.env`; log aktivitas, riwayat revisi (Berita + Halaman Kustom), dashboard kesehatan situs masuk Tahap B; live preview setelah CRUD selesai; mode gelap admin backlog | |
| K33 | Berita (B3d) | Isi berita = HTML bersih per bahasa (disaring server); EN kosong jatuh ke ID; status Terjadwal tampil otomatis saat waktunya tiba (tanpa cron); gambar unggahan di `storage/app/public` (tautan `public/storage`), WebP, maks 5 MB; kategori "Komunitas" diganti **Keberlanjutan** (slug `community` tetap, QA12); slug dilarang: kategori, category, page, halaman, search, cari, feed, sitemap; Editor tidak bisa menghapus (K29) | Gambar lama yang diganti tidak dihapus dari disk (bisa dipulihkan lewat revisi); pembersihan menyusul di Media Library (B3b) |
| K34 | Pilar & Brand (B3c) | Pilar: nama (teks biasa, tanpa terjemahan), slug, ikon dari 16 pilihan, tampil/sembunyi, urut seret-dan-lepas (plus tombol panah untuk HP). Brand: pilar induk (bisa dipindah), nama, slug, logo unggahan (dipangkas otomatis, WebP, maks 5 MB), deskripsi ID/EN, Instagram, TikTok, tautan situs, butir "Fokus & Layanan Utama" (opsional), tampil/sembunyi, status terjemahan. Tautan `http://` otomatis jadi `https://` saat disimpan lewat admin (impor awal apa adanya). Tambah/ubah/urut/sembunyikan = Super Admin dan Admin; **hapus hanya Super Admin**; pilar yang masih punya brand dan pilar terakhir tidak bisa dihapus. Brand Commercial Technology ditandai **Dummy** (narasi buatan) | Menerapkan QA8. Slug unik global per jenis (pilar, brand). Perubahan slug belum membuat redirect 301 (B3n). Q15 (http/https) diikuti sesuai rencana B3c; koreksi bila tidak setuju |
| K35 | Terjemahan otomatis Berita (B5 awal) | Admin cukup mengisi **salah satu** bahasa (ID atau EN); kolom yang kosong diterjemahkan otomatis **saat disimpan** (judul, ringkasan, isi, alt gambar, judul SEO, deskripsi SEO). Isian admin tidak pernah ditimpa. Status jadi **Otomatis (belum ditinjau)**. Bila status masih Otomatis dan admin hanya mengubah sisi sumber, sisi hasil mesin diterjemahkan ulang; status Manual dan Ditinjau tidak ditimpa. Saklar "Terjemahkan otomatis" di form (default aktif). Penyedia lewat `.env`: `TRANSLATE_DRIVER` = `deepl` / `google` / `anthropic` / `none` (bawaan `none`). Nama brand dan glosarium tidak diterjemahkan. Bila terjemahan gagal: ID terisi lalu EN gagal = berita tetap tersimpan (EN jatuh ke ID) + peringatan; EN saja terisi lalu ID gagal = tidak tersimpan, admin diminta mengisi ID | Penyedia belum dipilih (Q16). Belum ada tombol "Terjemahkan ulang" manual, belum ada label "Diterjemahkan otomatis" di halaman publik, dan terjemahan massal artikel WordPress (perintah artisan + queue) masih di B5 |
| K36 | Impor berita WordPress (B6, v38) | **171 berita terbit** (2018-2026), semuanya punya gambar unggulan, 35 gambar di dalam isi. Diimpor lewat `php artisan content:import-wp` dari `database/imports/wp_news.json` (data sudah diekstrak dari dump `rumahsof_newdecaa.sql`). **Tanpa terjemahan**: kolom EN dikosongkan, pengunjung EN melihat teks ID (hemat kuota API). Gambar: seluruh `wp-content/uploads` disalin **apa adanya** ke `public/wp-uploads/` (nama file tidak diubah); gambar unggulan memakai ukuran WordPress `large` (1024px) bila ada. Kategori WordPress diubah menjadi 4 kategori berita baru (Training & Research Centre, Software & Robotics, Hardware & Networking Services, Event); "Berita Terbaru" diabaikan; satu berita satu kategori dengan prioritas urutan itu. Ringkasan dibuat otomatis dari awal isi. Kolom `news.wp_id` mencegah impor ganda | Berita lama tidak dikonversi ke WebP (nama file asli dipertahankan). Redirect 301 URL WordPress lama belum dibuat (B3n) |
| K37 | Proyek (B3g, v39) | Satu proyek = **satu pilar** (+ brand opsional, dipilih dari brand milik pilar itu), sesuai QA10. Field: judul, ringkasan, deskripsi (teks biasa), lingkup pekerjaan (satu butir per baris, maks. 12), klien (**wajib**, tampil di kartu), tahun (**wajib**), gambar utama (+ alt), tampil/sembunyi, unggulan, status terjemahan, slug, SEO. **Beranda menampilkan 3 proyek unggulan terbaru; bila yang dicentang kurang dari 3, dilengkapi proyek terbaru.** Urutan di situs: tahun terbaru dulu. Slug `penghargaan`/`awards` dicadangkan (bentrok rute). Editor boleh menambah/mengubah, hapus hanya Super Admin dan Admin. Kolom `gallery` disiapkan, tampilan galeri menyusul (QA10). Terjemahan otomatis untuk Proyek belum dipasang (menyusul di B5); EN kosong jatuh ke ID | Tabel `projects` ada = situs membaca dari database, jadi **jalankan `content:import-projects` setelah migrate** (bila tidak, Portofolio tampil kosong) |

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

**Q11. MBG Food Hub.** Ada di daftar menu (butir 2) tetapi tidak ada di daftar penjelasan (butir 5). Sementara kutaruh di **Commercial Technology** bersama Satu AI, Pasar Online, Desa Digital. Benar?

**Q12. Bahan Commercial Technology.** Satu AI, Pasar Online, MBG Food Hub, Desa Digital belum punya deskripsi, tautan, dan logo di dokumen: sementara hanya nama (deskripsi kosong). Mau diisi nanti?

**Q13. Misi.** Di dokumen tertulis \"Misi : Mengikuti\" (tampaknya belum selesai). Teks Misi dikosongkan sampai kamu kirim isinya (dipakai di R3).

**Q14. Tahun Perjalanan Kami.** Dokumen mulai **2019** (garasi di daerah Veteran, Pacitan), sedangkan K20 mengatur mulai 2018. Di R3 aku ikut dokumen (2019 sampai 2026). Setuju?

**Q15. Tautan brand.** Dipakai `http://www...` seperti di dokumen, atau diganti `https://` (disarankan karena situs memakai HTTPS)?

**Q16. Penyedia terjemahan.** Pilih salah satu untuk `.env`: **DeepL** (kuota gratis 500.000 karakter per bulan, hasil rapi), **Google Cloud Translation**, atau **Claude API** (paling natural, berbayar per pemakaian). Rekomendasiku: DeepL dulu; ganti kapan saja lewat `TRANSLATE_DRIVER`.

*(Pertanyaan Q1-Q7 sebelumnya sudah terjawab dan dicatat di Bagian 2.)*

---

## 3B. Pertanyaan Tahap Admin (jawab langsung di sini, 2 Nov 2026)

Cara menjawab: isi baris **Jawabanmu** di bawah tiap pertanyaan (cukup "ikut rekomendasi" atau pilihan huruf). Pertanyaan bertanda **[Penentu awal]** perlu dijawab sebelum kode admin dimulai; yang lain bisa dijawab nanti, saat modulnya akan dikerjakan. **Semua pertanyaan di bawah sudah dijawab (2 Nov 2026) dan dicatat di K27-K32.**

### Kelompok 1: Arah & lingkup

**QA1. [Penentu awal] Seberapa bebas admin mengatur halaman?** (menyempurnakan K4 "section-based")
- **A.** Struktur halaman **tetap** seperti desain yang di-ACC. Admin mengubah isi tiap section; khusus **Beranda** bisa menampilkan/menyembunyikan dan mengurutkan section.
- **B (rekomendasiku): A + modul "Halaman Kustom"** untuk halaman sederhana yang kamu tambah sendiri nanti (judul, slug ID/EN, isi teks kaya, SEO). Kebijakan Privasi dan halaman legal lain memakai modul ini.
- **C.** Page builder penuh (tambah blok bebas di semua halaman). Paling fleksibel, tetapi paling mahal, dan mudah merusak desain yang sudah rapi.
- Jawabanmu: Aku setuju dengan rekomendasi mu saja

**QA2. [Penentu awal] Menu.** Sekarang menu dibentuk otomatis dari struktur halaman dan daftar pilar/brand.
- **A (rekomendasiku):** tetap otomatis; admin hanya bisa mengubah label, menyembunyikan item, dan mengurutkan menu tingkat atas.
- **B.** Menu builder drag-and-drop penuh (item bebas, tingkat bebas).
- Jawabanmu: sesuai rekomendasimu

**QA3. [Penentu awal] Urutan pengerjaan.**
- **A (rekomendasiku): modul demi modul, end-to-end.** Satu modul selesai berarti adminnya jadi **dan** halaman depannya sudah membaca dari database, sehingga bisa langsung kamu uji (mis. Berita dulu, lalu Pilar Bisnis, dst.).
- **B.** Seperti rencana lama: seluruh admin dibuat dulu, baru frontend dihubungkan di B4.
- Jawabanmu: sesuai rekomendasimu

**QA4. [Penentu awal] Data awal di admin.** Isi yang sekarang tertulis di kode (`Content.php`, `Pillars.php`, 90 logo klien, termasuk yang masih DUMMY) bisa dipindahkan otomatis ke database lewat perintah artisan.
- **A (rekomendasiku):** diimpor otomatis, admin langsung terisi; item DUMMY ditandai agar mudah dicari dan diganti.
- **B.** Admin mulai kosong, semua diketik ulang.
- Jawabanmu: sesuai rekomendasimu. nah untuk berita itu sementara pakai dummy dulu. terakhir kalau misalkan sudah siap nanti kita import dari beriita lama di wordpress. termasuk fotonya juga ambil dari sana. kamu tinggal berikan fasilitas dan tutornya.

**QA5. [Penentu awal] Pengguna, peran, dan akses admin.**
1. Berapa orang yang akan memakai admin, dan siapa saja perannya?
2. Usulan peran: **Super Admin** (semua + pengguna + pengaturan sistem), **Admin** (semua konten + pesan masuk + pengaturan situs), **Editor** (berita, proyek, penghargaan; tidak bisa menghapus dan tidak bisa mengubah pengaturan). Setuju atau ada perubahan?
3. 2FA (kode aplikasi autentikator): wajib untuk Super Admin dan Admin, opsional untuk Editor? (rekomendasiku: ya)
4. Alamat admin: `new.decaa.id/admin` (lalu `decaa.id/admin`), tidak diindeks mesin pencari, dibatasi percobaan login. Setuju? Bahasa antarmuka admin: **Indonesia saja** (rekomendasiku).
- Jawabanmu: sesuai rekomendasimu. *Akun Super Admin awal: `decaa@decaa.id` (password awal diberikan lewat chat, tidak ditulis di berkas ini; wajib diganti saat login pertama).*

**QA6. Teks antarmuka (tombol, label menu, teks aksesibilitas).** Ada sekitar 100 kunci dwibahasa di `Labels.php`.
- **A (rekomendasiku):** tetap di kode; yang diedit dari admin hanya teks halaman dan konten. Perubahan label dikerjakan lewat aku.
- **B.** Dibuat layar "Teks Antarmuka" di admin untuk mengedit semua label.
- Jawabanmu: sesuai rekomendasimu

### Kelompok 2: Media & isi konten

**QA7. Media dan logo.** Sekarang foto dan logo berupa file di `public/img/...`.
1. Dipindah ke **Media Library** (disimpan di `storage`, dikonversi WebP, thumbnail otomatis, wajib alt text ID/EN)? (rekomendasiku: ya)
2. Batas upload: usulan **5 MB per foto**, dikecilkan otomatis. Setuju?
3. Logo PNG tetap **dipangkas otomatis** ruang kosongnya saat diunggah (melanjutkan K18)? (rekomendasiku: ya)
4. Logo yang sudah ada (7 pilar lama, 90 logo klien, logo brand baru) diimpor otomatis ke Media Library lewat perintah artisan.
- Jawabanmu: sesuai rekomendasimu

**QA8. Pilar Bisnis dan Brand.** Data sekarang: 8 pilar dan **20 brand** (13 di pilar biasa + 7 di Commercial Technology; catatan lama menyebut 17, sudah usang).
1. Admin boleh **menambah/menghapus pilar** atau hanya mengubah isi 8 pilar yang ada? (rekomendasiku: boleh tambah/ubah/urut/sembunyikan; hapus hanya Super Admin)
2. Brand boleh dipindah antarpilar, dan perubahan slug otomatis membuat redirect 301. Setuju?
3. Ikon pilar dipilih dari daftar ikon yang sudah ada (code-bracket, cpu-chip, signal, pencil, gift, cake, dst.). Perlu upload ikon sendiri? (rekomendasiku: tidak)
4. Kolom deskripsi/Instagram/TikTok/tautan boleh kosong (sesuai aturan "tidak diisi karangan"); brand tanpa deskripsi tampil seperti sekarang.
- Jawabanmu: sesuai rekomendasimu

**QA9. Klien & Mitra.** Sekarang hanya logo + kategori (dari nama folder), tanpa nama.
1. Kolom **nama klien** di admin: opsional (rekomendasiku) atau wajib?
2. Kategori (sekarang 7) bisa ditambah/diubah dari admin? (rekomendasiku: ya)
3. Strip beranda: logo dipilih manual lewat centang "tampil di beranda" (rekomendasiku), atau otomatis 24 logo seperti sekarang?
4. Tambah kolom internal **"izin tampil sudah ada"** (tidak tampil di publik) untuk melacak izin logo (lihat Risiko)?
- Jawabanmu: sesuai rekomendasimu

**QA10. Portofolio (proyek).**
1. Satu proyek terhubung ke **satu pilar** (+ brand opsional)? (rekomendasiku) Atau boleh banyak pilar?
2. **Galeri foto** (lebih dari satu foto per proyek): disiapkan di admin sekarang, atau menunggu sampai foto asli ada? (rekomendasiku: kolom galeri disiapkan, tampilan menyusul)
3. Tiga proyek unggulan di beranda dipilih manual lewat centang "unggulan". Setuju?
- Jawabanmu: sesuai rekomendasimu

**QA11. Penghargaan & Sertifikat (K25: "penghargaan dan sertifikat seluruh brand").**
1. Perlu pembeda **jenis** (Penghargaan / Sertifikat) dan filter di halaman? Perlu kolom **brand/pilar penerima**? (rekomendasiku: ya, keduanya opsional)
2. Selain judul, tahun, deskripsi, dan foto, perlu **penerbit/pemberi** dan **file PDF sertifikat** yang bisa dilihat pengunjung?
3. Penanda "Penghargaan Terbaru" otomatis dari tahun terbaru, atau dicentang manual (`featured`)? (rekomendasiku: manual)
- Jawabanmu: tidak perlu pembeda, tidak perlu penerbit/pemberi dan file pdf juga tidak perlu. ya manual saja.

**QA12. Berita.**
1. **Editor teks kaya**: yang diizinkan paragraf, subjudul (H2/H3), daftar, tebal/miring, tautan, gambar, kutipan. Perlu **embed video YouTube**? Perlu tabel?
   (Catatan teknis: isi berita sekarang berupa blok data; setelah admin jadi, isinya berupa HTML bersih. Ini juga yang diperlukan untuk impor artikel WordPress.)
2. **Status:** Draf, Terbit, dan **Terjadwal** (butuh scheduler/cron di server). Setuju untuk Berita saja; modul lain cukup tombol "Tampilkan" (rekomendasiku)?
3. Kategori berita **"Komunitas"** diganti **"Keberlanjutan"** (item lama yang belum dijawab; slug `community` tetap agar URL aman)? Kategori dikelola dari admin?
4. Perlu **tag** dan **nama penulis** yang tampil di artikel? (rekomendasiku: tanpa tag, tanpa penulis tampil; pembuat tetap tercatat internal)
- Jawabanmu: sesuai rekomendasimu. tidak perlu embed video di dalamnya. ya kategori dikelola penuh oleh admin jaga slugnya agar tidak crash dengan slug yang ada di route kita. setuju yang nomor 4 begitu saja.

**QA13. Pembersihan lingkup (cukup dicentang).** Dari kode saat ini, tiga hal berikut **tidak akan dibuatkan modul admin** karena sudah tidak dipakai di tampilan: **Statistik/angka pencapaian** (`Content::stats()`, sudah diganti foto di Beranda), **Jajaran Kepemimpinan** (dihapus, K23), **Legalitas** (dihapus, K19). Setuju, atau ada yang mau dihidupkan lagi?
- Jawabanmu: dihapus saja

### Kelompok 3: Interaksi & sistem

**QA14. Form Kontak dan pesan masuk (B4).**
1. Server email apa yang dipakai mengirim (SMTP Google Workspace, Zoho, hosting sendiri, dst.)? Aku butuh jenisnya saja; kredensial hanya di `.env`-mu.
2. Notifikasi email ke siapa saja? Boleh berbeda **per subjek** (mis. "Permintaan Dokumen" ke orang A, "Komunitas Peduli Digital" ke orang B, sisanya `info@decaa.id`)? Daftar subjek (5 sekarang) bisa diubah dari admin?
3. Kirim **balasan otomatis** ke pengirim ("pesan Anda sudah kami terima")? (rekomendasiku: ya, singkat)
4. Anti-spam: honeypot + batas kirim per IP (rekomendasiku) + Turnstile/reCAPTCHA (perlu akun Cloudflare/Google) sekarang atau nanti di Tahap C?
5. Pesan disimpan di database berapa lama? (usulan: 12 bulan, lalu dihapus otomatis; sesuaikan dengan Kebijakan Privasi)
- Jawabanmu: 1. server email sendiri: host `mail.decaa.id` (SMTP port 465; IMAP 993, POP3 995). 2. **Satu-satunya akun email untuk semua keperluan: `info@decaa.id`** (pengirim, penerima semua pesan Kontak, notifikasi). Sisanya sesuai rekomendasi.

**QA15. Terjemahan otomatis di admin.** Penyedia terjemahan baru diputuskan di B5, setelah jumlah artikel WordPress diketahui.
- **A (rekomendasiku):** semua form admin sudah punya tab ID | EN dan status terjemahan sejak awal; tombol "Terjemahkan otomatis" menyusul di B5.
- **B.** Tombol terjemahan dibuat bersamaan dengan modul pertama.
- Jawabanmu: sesuai rekomendasimu

**QA16. SEO, redirect, dan analitik.**
1. Setiap halaman dan setiap konten (berita, proyek, pilar, brand, halaman kustom) punya kolom SEO: judul, deskripsi, gambar share (ID dan EN), dengan nilai bawaan otomatis bila kosong. Setuju masuk admin sejak awal?
2. Layar **Redirect** (URL lama ke baru) dikelola admin, dipakai juga untuk URL WordPress. Setuju?
3. Kolom **ID analitik** (Plausible/Umami/GA4) dan **kode verifikasi Search Console** di Pengaturan. Mana yang dipilih (bisa diputuskan nanti di Tahap C)?
- Jawabanmu: sesuai rekomendasimu yang terbaik

**QA17. Pengaturan sistem.**
1. **Mode pemeliharaan** (halaman 503 yang sudah ada) bisa dinyalakan dari admin oleh Super Admin, dengan tautan rahasia agar tim tetap bisa membuka situs. Setuju?
2. Pengaturan **noindex staging** tetap di `.env` (`SITE_NOINDEX`), **tidak** dipindah ke admin, supaya situs live tidak sengaja ter-noindex. Setuju?
- Jawabanmu: sesuai rekomendasimu

**QA18. Lingkup tambahan (ide I1, I2, I5, I8).** Mana yang masuk Tahap B, dan mana yang tetap backlog?
- **Log aktivitas** (siapa mengubah apa, kapan): rekomendasiku **masuk Tahap B**.
- **Riwayat revisi** (kembalikan versi lama): rekomendasiku hanya untuk Berita dan Halaman Kustom.
- **Live preview** sebelum Terbit: rekomendasiku setelah semua CRUD selesai.
- **Dashboard kesehatan situs** (terjemahan belum ditinjau, gambar tanpa alt, halaman tanpa meta, item DUMMY tersisa): rekomendasiku **masuk**, karena murah dan sangat berguna menjelang go-live.
- **Mode gelap admin** dan pintasan keyboard: rekomendasiku backlog.
- Jawabanmu: sesuai rekomendasimu yang terbaik.

**Catatan tambahan darimu:** ingin segera membuat halaman login dan admin agar desainnya bisa diperbaiki juga. Tahap awal: login, logout, dashboard (dummy), dan CRUD admin (pengguna); fitur penting lainnya menyusul. Dikerjakan sebagai **Tahap B-awal** (lihat Tahap B).

### Pertanyaan lama yang ikut memengaruhi admin

| # | Topik | Dampak ke admin |
|---|---|---|
| Q12 | Deskripsi, tautan, logo untuk 7 brand Commercial Technology | Kolom boleh kosong; isi lewat admin nanti |
| Q13 | Teks Misi | Kolom Misi di admin; kode sekarang masih berisi 5 misi dummy |
| Q14 | Tahun mulai Perjalanan Kami (2018 atau 2019) | Dikelola sebagai data lini waktu di admin |
| Q15 | Tautan brand `http://www...` atau `https://` | Admin bisa menormalkan otomatis ke `https://` saat simpan, bila kamu setuju |
| K16 | Kategori berita "Komunitas" | Lihat QA12 butir 3 |


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
- **Tentang Kami:** Profil Perusahaan (termasuk Chairman Message), Visi & Misi, Perjalanan Kami *(Jajaran Kepemimpinan dan Legalitas sudah dihapus: K23, K19)*
- **Pilar Bisnis:** halaman daftar (8 pilar) + halaman detail pilar (blok per brand) + **halaman brand** (`/id/pilar-bisnis/{pilar}/{brand}`, 20 brand)
- **Portofolio:** Proyek (daftar + detail), Klien & Mitra, **Penghargaan**
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
**Tentang Kami, putaran 2** *(bagian Jajaran Kepemimpinan digantikan K23; Perjalanan mulai 2018 akan menyesuaikan di R3)*
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

### REVISI DARI DOKUMEN PDF `Revisi_Website_decaaid.pdf` (2 Nov 2026, BERJALAN)

Fokus **tampilan** dulu. Bahan yang belum jelas dikosongkan (tidak diisi karangan); yang sudah jelas langsung dipasang. Dikerjakan bertahap R1 sampai R5.

| Butir PDF | Isi | Tahap | Status |
|---|---|---|---|
| 1 | Revisi slogan | R1 | `[~]` |
| 2 | Menu Pilar Bisnis jadi 8 jenis bisnis (list teks) | R1 | `[~]` |
| 3 | Profil perusahaan, visi, misi, perjalanan 2019-2026 | R3 | `[ ]` |
| 4 | Jajaran Kepemimpinan dihilangkan | R1 | `[~]` |
| 5 | Penjelasan pilar bisnis + brand, tombol Selengkapnya | R1 | `[~]` |
| 6 | Isi 3 foto depan (kolase hero) | R2 | `[ ]` |
| 1, 5 (Beranda) | Hero label DECAA.ID, Tentang DECAA.ID, brand 5 per baris | R2 | `[~]` |
| 7 | Portofolio proyek: isi menyusul | R5 | `[ ]` |
| 8 | Klien & Mitra: data dilengkapi, Ristekdikti diganti | R5 | `[ ]` |
| 9 | Berita: data lama dimasukkan | R5 / B6 | `[~]` (v38: perintah impor dikirim, menunggu dijalankan) |
| 10 | Karir mengikuti 8 pilar (butir 5) | R1 | `[~]` |
| 11 | Data kontak divalidasi | R5 | `[ ]` |
| 12 | Menu baru Penghargaan | R4 | `[ ]` (route + menu sudah di R1) |

**R1: Slogan, Pilar Bisnis, menu (file dikirim, menunggu dicek di browser)**
- [~] Hero Beranda: judul \"Mitra Solusi untuk Segala Akselerasi\" + teks \"Ekosistem digital...\" (ID dan EN). Label kecil di atas judul dikosongkan (tampil otomatis bila diisi di `Content::home()['hero']['eyebrow']`). Judul tagline situs (`<title>`) ikut slogan
- [~] Menu **Pilar Bisnis** (mega menu desktop, drawer HP, footer, pencarian cepat): 8 jenis bisnis, **teks saja tanpa logo**, tautan \"Lihat semua pilar bisnis\" tetap ada
- [~] Data pilar + 17 brand di file baru `app/Support/Pillars.php` (teks dokumen apa adanya; Instagram/TikTok kosong; tautan More Info sesuai dokumen). `Content::rawUnits()` kini membaca file ini
- [~] Beranda: bagian Pilar Bisnis = 8 kartu (ikon jenis bisnis, nama, daftar brand), tombol \"Lihat semua pilar bisnis\" di bawah grid (kartu gradasi lama dihapus karena jumlahnya kini genap)
- [~] Halaman daftar Pilar Bisnis: grid 4x2, kartu berisi ikon, nomor, nama pilar, dan label brand. Band \"Satu pintu\" tetap (teks DUMMY)
- [~] Halaman detail pilar: judul pilar, lalu **blok per brand** (logo, nama, penjelasan, ikon Instagram/TikTok bila diisi, tombol **Selengkapnya** ke situs brand di tab baru), lalu proyek terkait dan pilar lainnya. Bagian \"Layanan\" dan \"Cara kerja\" dummy lama **dihapus** (tidak ada di dokumen)
- [~] Komponen baru `<x-ui.pillar-mark>` + 6 ikon baru di `components/ui/icon` (code-bracket, cpu-chip, signal, pencil, gift, cake). Karir (kartu bidang + pilihan bidang) dan ubin pilar di Profil Perusahaan ikut 8 pilar
- [~] Jajaran Kepemimpinan: tab, menu, route (`about.team`), dan data `about()['team']` dihapus
- [~] Menu Portofolio: **Penghargaan** ditambahkan (route `awards` sudah ada; halamannya R4, sementara tampil halaman placeholder)
- [~] Proyek dummy dipetakan ke pilar baru (software/robotics -> Software & Robotics, dst.)
- [ ] **Hapus file lama:** `resources/views/pages/about/team.blade.php` dan `resources/views/pages/about/legal.blade.php`
- [ ] **Logo brand yang dibutuhkan** di `public/img/logo/` (selama kosong tampil placeholder berlabel): sudah ada `software.png`, `robotic.png`, `hardware.png`, `networking.png`, `trainingcentre.png`, `paperking.png`, `dapurkirana.png`. **Baru:** `mmc-computer.png`, `apple-kingdom.png`, `sekolah-robotika.png`, `gardenia.png`, `hba-supplier.png`, `fruvola.png`, `satu-ai.png`, `pasar-online.png`, `mbg-food-hub.png`, `desa-digital.png`
- [ ] Cek di browser: menu (desktop dan HP), Beranda, `/id/pilar-bisnis`, kedelapan halaman detail (terutama Commercial Technology yang brand-nya belum punya deskripsi), kedua bahasa, lebar 360/768/1024/1440 px
- [ ] Teks `lang/*.json` atau Labels yang masih menyebut \"tujuh pilar\": sudah dibersihkan di `Content.php`; cek sisanya di tampilan

**R2: Beranda (file dikirim, menunggu dicek di browser; urutan R2 sekarang dikerjakan per halaman sesuai masukanmu)**
- [~] Hero: judul memakai Capitalize Each Word; label kecil di atas judul diisi **DECAA.ID**
- [~] Tentang DECAA.ID: judul **Everything Is Nothing Without Technology** (ID dan EN sama), deskripsi dari dokumen (EN = terjemahan draf); paragraf kedua (kantor Jakarta dst.) dihapus
- [~] Pilar Bisnis di Beranda: kini menampilkan **semua brand** (Decaa Software, Decaa Robotics, dst., 17 brand) **5 per baris** di desktop (3 di tablet, 2 di HP). Logo yang belum ada tampil placeholder berlabel; tiap kartu menuju blok brand di halaman pilar-nya. Tombol \"Lihat semua pilar bisnis\" tetap
- [ ] Tiga foto depan (butir 6): menunggu giliran (foto besar: semua brand decaa memakai baju berbeda-beda; dua lainnya belum jelas)
- [ ] Cek ulang teks Beranda lain (nilai perusahaan, dst.) terhadap profil baru di R3

**R3: Tentang Kami (butir 3) (menunggu)**
- [ ] Profil Perusahaan: teks baru dari dokumen (5 pilar teknologi + bisnis pendukung, semangat \"everything is nothing without technology\")
- [ ] Visi (dari dokumen); **Misi dikosongkan** (Q13); nilai-nilai dummy lama ditinjau
- [ ] Perjalanan Kami: 2019 sampai 2026 sesuai dokumen (Q14); foto per tahun tetap placeholder

**R4: Penghargaan (butir 12) (menunggu)**
- [ ] Halaman `Penghargaan` (carousel kartu foto + judul, contoh di dokumen: \"Penghargaan Keberlanjutan\"); isi **kosong** sampai daftar penghargaan dan sertifikat dikirim

**R5: Portofolio, Klien & Mitra, Berita, Kontak (butir 7, 8, 9, 11) (menunggu)**
- [ ] Tampilan tetap; hanya data yang diisi. Menunggu bahan: proyek (terutama kementerian/klien besar), logo klien terbaru (Ristekdikti diganti kementerian barunya), berita lama (B6), validasi telepon/WA/sosmed

---

### TAHAP B: BACK-OFFICE & ADMIN (SETELAH ACC)

> **Disusun ulang 2 Nov 2026** mengikuti halaman dan data yang ada di kode sekarang (8 pilar + 20 brand, brand page, Penghargaan, Chairman Message, Perjalanan Kami, Klien & Mitra 7 kategori). **Belum ada yang dikerjakan.** Keputusan yang menentukan bentuk akhir ada di **Bagian 3B (QA1-QA18)**; item di bawah mengikuti rekomendasiku dan akan disesuaikan dengan jawabanmu. Penomoran B1-B6 dipertahankan agar rujukan lama tetap benar.

**Prinsip rancangan (usulan)**
1. **Frontend tidak ditulis ulang.** `App\Support\Content` dan `Pillars` diganti lapisan yang membaca database dan mengembalikan **array dengan bentuk yang sama**, sehingga view tetap.
2. **Kolom dwibahasa = JSON `{"id": ..., "en": ...}`** + status terjemahan per record, tanpa paket pihak ketiga (mengurangi risiko Laravel 9 EOL).
3. **Daftar berulang** (nilai-nilai, misi, program, alasan bergabung, langkah seleksi) disimpan sebagai *repeater* JSON di dalam konten halaman, bukan tabel sendiri; entitas yang punya halaman sendiri (pilar, brand, proyek, berita, dst.) memakai tabel sendiri.
4. Data yang sekarang di kode **diimpor otomatis** (QA4); admin langsung terisi dan item DUMMY ditandai.
5. Admin buatan sendiri: Blade + Tailwind + Alpine (K2), tanpa Livewire; tabel dan filter di sisi server.

#### Peta konten: halaman publik ke modul admin

| Halaman publik | Sumber sekarang | Modul admin | Isi yang bisa diedit |
|---|---|---|---|
| Header, footer, semua halaman | `Content::company()`, `config/site.php` | **Pengaturan Situs** (B3a) | Nama, slogan, logo terang/gelap, favicon, email umum + karir, telepon, WhatsApp, 6 sosmed, kantor (jenis, kota, alamat ID/EN, tautan peta, foto, urutan), teks hak cipta |
| Menu | `Content::nav()` | **Menu** (B3o, QA2) | Label, tampil/sembunyi, urutan tingkat atas |
| Beranda | `Content::home()` | **Beranda** (B3e) | Hero (label, judul, teks, 3 foto kolase), Tentang DECAA.ID (+2 foto), judul section, 4 nilai perusahaan, blok Keberlanjutan (3 poin + foto), CTA; pilih 3 proyek unggulan; tampil/urut section |
| Tentang Kami: Profil | `Content::about()['profile']` | **Tentang Kami** (B3f) | Lead, paragraf cerita, 2 foto, **Chairman Message** (nama, jabatan, foto, judul, isi), foto kantor Jakarta |
| Tentang Kami: Visi & Misi | `about()['vision']` | B3f | Visi, daftar misi (Q13), 5 nilai (judul + teks) |
| Tentang Kami: Perjalanan Kami | `about()['journey']` | B3f | Intro, **lini waktu** (tahun, tag, judul, cerita, foto) |
| Pilar Bisnis (daftar, detail, brand) | `App\Support\Pillars` | **Pilar & Brand** (B3c) | Pilar (nama, ikon, urutan, tampil); brand (pilar induk, nama, logo, deskripsi ID/EN, Instagram, TikTok, tautan Selengkapnya, urutan) |
| Portofolio: Proyek | `Content::portfolio()` | **Proyek** (B3g) | Judul, pilar, klien, tahun, foto, ringkasan, deskripsi, lingkup kerja (daftar), unggulan, galeri (QA10) |
| Portofolio: Klien & Mitra | folder `public/img/clients/*` | **Klien & Mitra** (B3h) | Logo, nama (opsional), kategori (dikelola), urutan, tampil di beranda, izin tampil (internal) |
| Portofolio: Penghargaan | `Content::awardsPage()` | **Penghargaan** (B3i) | Judul, tahun, deskripsi, foto, unggulan (manual) (QA11) |
| Berita | `Content::news()` | **Berita & Kategori** (B3d) | Judul, slug, kategori, tanggal, foto, ringkasan, isi (editor teks kaya), status Draf/Terbit/Terjadwal, SEO |
| Keberlanjutan | `Content::communityPage()` | **Keberlanjutan** (B3j) | Tentang, 3 fokus, program (daftar), ajakan bergabung |
| Karir | `Content::careersPage()` | **Karir** (B3k) | Pengantar, alasan bergabung, langkah seleksi, bidang (mengikuti 8 pilar), email tujuan |
| Kontak | `Content::contactPage()` | **Kontak & Pesan Masuk** (B3l) | Pengantar, daftar subjek, tujuan email, **kotak masuk** pesan |
| Kebijakan Privasi | `Content::privacyPage()` | **Halaman Legal/Kustom** (B3m) | Bagian-bagian (teks kaya), tanggal pembaruan |
| Semua halaman | meta di layout | **SEO, Redirect, Sitemap** (B3n) | Judul, deskripsi, gambar share per halaman dan per konten; redirect 301 |

*Tidak dibuatkan modul admin (QA13): Statistik, Jajaran Kepemimpinan, Legalitas. Teks antarmuka (`Labels.php`) tetap di kode kecuali QA6 dijawab B.*

#### TAHAP B-AWAL: Login, Dashboard, dan CRUD Pengguna Admin (dikerjakan lebih dulu)

Atas permintaanmu, admin dimulai dari kerangka yang bisa langsung dilihat dan didesain, sebelum modul konten. Bagian ini menjadi **bagian awal B2** (login, layout, komponen dasar) ditambah modul **Pengguna (B3o)**. Menunggu aba-aba "mulai" darimu sebelum ada kode.

**Asumsi** (Q2-Q6 belum kamu jawab satu per satu; kuikuti rekomendasiku, koreksi bila perlu):
- Pengguna baru dibuat Super Admin dengan password sementara, wajib ganti saat login pertama (tanpa undangan email).
- Tahap awal: login, logout, ganti password sendiri, reset password oleh Super Admin. **Lupa password lewat email** menyusul setelah SMTP `info@decaa.id` teruji; **2FA** di putaran berikutnya.
- Tampilan: sidebar navy tua, konten terang, aksen biru brand `#1B3590`, merah `#E5352E` hanya untuk peringatan/hapus.
- Dashboard dummy: kartu ringkasan (pesan baru, berita draf, terjemahan belum ditinjau, item DUMMY tersisa), aktivitas terbaru, pintasan "Tambah Berita / Tambah Proyek" (nonaktif sampai modulnya ada). Semua angka ditandai DUMMY.
- Hanya Super Admin yang bisa menambah, mengubah peran, menonaktifkan, dan menghapus pengguna; Admin dan Editor hanya mengubah profil dan password sendiri. Pengguna dinonaktifkan, bukan dihapus, agar log tetap utuh.
- Akun awal: Super Admin `decaa@decaa.id`, dibuat lewat perintah artisan (bukan form daftar), `must_change_password = true`. Password awal tidak ditulis di berkas ini. Aturan password baru: minimal 10 karakter, kombinasi huruf dan angka.

**BA1. Fondasi login dan pengguna**
- [~] Migration: tambah kolom di `users`: `role` (`super_admin|admin|editor`), `is_active`, `must_change_password`, `last_login_at`
- [~] `app/Models/User.php`: konstanta peran, helper `isSuperAdmin()`, dst.
- [~] Perintah artisan pembuat Super Admin (idempoten; email default `decaa@decaa.id`)
- [~] `routes/admin.php` (prefiks `/admin`, tanpa prefiks bahasa) dimuat dari `RouteServiceProvider`
- [~] Middleware: `admin.active` (tolak akun nonaktif), `admin.role:*`, `admin.password` (paksa ganti password), `admin.noindex` (header `X-Robots-Tag` + meta); `Authenticate` diarahkan ke login admin; locale admin dikunci `id`
- [~] Login: batas percobaan (throttle per email + IP), pesan galat generik, "ingat saya", regenerasi sesi; logout lewat POST
- [~] `lang/id/validation.php` dan `lang/id/auth.php` (pesan Indonesia untuk admin)

**BA2. Layout admin dan dashboard**
- [~] `layouts/admin.blade.php`: sidebar menurut peran (`App\Support\AdminMenu`), topbar (menu pengguna: Ganti Password, Keluar; tautan Lihat Situs), responsif (sidebar jadi drawer di HP, Esc/klik latar menutup), tautan \"Lewati ke isi\". Breadcrumb lewat komponen `<x-admin.page-header>` (bukan di layout, supaya tiap halaman mengisinya sendiri). Layout kedua `layouts/admin-guest.blade.php` untuk Masuk dan Ganti Password wajib (tanpa sidebar; layar lebar: panel merek kiri + formulir kanan)
- [~] Komponen `components/admin/*` (`<x-admin.nama>`): `button`, `input`, `select`, `toggle`, `card`, `badge` (termasuk nada `dummy`), `table`, `modal`, `confirm` (modal konfirmasi hapus, satu modal untuk banyak baris), `toast` (otomatis membaca `session('status')`/`session('error')`; event `toast` dari Alpine), `empty`, `icon`, `page-header`, `stat-card`
- [~] Halaman Masuk (desain sendiri, tombol tampilkan/sembunyikan password, tombol Masuk dikunci saat diproses) dan Ganti Password (versi wajib tanpa sidebar; versi sukarela di dalam layout admin dengan breadcrumb)
- [~] Dashboard dummy: 4 kartu ringkasan (Pesan Baru, Berita Draf, Terjemahan Belum Ditinjau, Item Dummy Tersisa), Aktivitas Terbaru (DUMMY), Pintasan nonaktif, **Kesiapan Konten** (checklist item DUMMY dari Bagian \"Daftar DUMMY\", 0 dari 9). Semua angka dan aktivitas ditandai badge Dummy
- [~] Sidebar memuat semua modul mendatang berlabel \"Segera\" (nonaktif); item aktif otomatis saat `route` diisi di `AdminMenu`. Visibilitas per peran: Super Admin semua; Admin semua konten + pesan + situs; Editor hanya Berita, Proyek, Penghargaan (usulan QA5)
- [~] Memakai build Vite dan Tailwind yang sama; warna memakai nilai langsung (`#1B3590`, `#E5352E`, navy `#0B1437`) sehingga tidak bergantung pada token `brand`/`accent`. Tidak ada perubahan `vite.config.js`
- [ ] **Verifikasi oleh kamu:** `npm run dev`/`build`, buka `/admin/login` dan `/admin`; cek lebar 360, 768, 1024, 1440 px; sidebar HP (buka/tutup); menu pengguna; kedua halaman Ganti Password (wajib dan sukarela). Bila kelas Tailwind admin tidak bergaya, kirim `tailwind.config.cjs` (pastikan `content` memuat `./resources/views/**/*.blade.php`)
- [ ] Logo putih `img/logo/decaaid-white.png` dipakai di sidebar dan panel Masuk; bila kosong gambar tidak tampil
- [ ] Catatan penimpaan: `layouts/admin.blade.php`, `pages/admin/dashboard.blade.php`, `pages/admin/auth/login.blade.php`, `pages/admin/password.blade.php`, dan `DashboardController.php` dari BA1 **ditimpa** versi BA2 (tidak ada file lama yang perlu dihapus)

**BA3. CRUD Pengguna dan profil**
- [~] Daftar pengguna (`/admin/pengguna`): cari nama/email, filter peran dan status, paginasi 10 per halaman, penanda "Password Sementara", login terakhir
- [~] Tambah (password sementara dengan tombol **Buat Otomatis**, salin, tampilkan) / ubah (nama, email, peran, aktif) / aktifkan-nonaktifkan / hapus, semuanya hanya Super Admin dan lewat modal konfirmasi
- [~] Reset password oleh Super Admin (password sementara + `must_change_password`; cookie "ingat saya" lama dicabut)
- [~] Pengaman: tidak bisa mengubah peran/status, menonaktifkan, atau menghapus **diri sendiri** dari layar ini; selalu tersisa minimal 1 Super Admin aktif (`User::isLastActiveSuperAdmin()`, dicek di ubah, nonaktifkan, dan hapus)
- [~] Profil sendiri (`/admin/profil`, semua peran): ubah nama; email dan peran read-only; tautan ke Ganti Password; menu "Profil Saya" di topbar
- [~] Tabel `activity_logs` + `ActivityLog::record()` (tidak pernah menggagalkan aksi utama); tercatat: masuk, keluar, ganti password, ubah profil, tambah/ubah/aktifkan/nonaktifkan/reset password/hapus pengguna. Nama pelaku disalin ke log sehingga tetap terbaca bila akun dihapus
- [~] Layar **Log Aktivitas** (`/admin/log-aktivitas`, hanya Super Admin): cari, filter jenis, paginasi 25; "Aktivitas Terbaru" di dashboard kini membaca tabel ini (badge Dummy di kartu itu dihapus)
- [~] Keputusan kecil: pengguna **boleh dihapus** (sesuai K30, hapus hanya Super Admin) selain dinonaktifkan; log lama tidak hilang. Percobaan login gagal belum dicatat (bisa ditambahkan bila perlu)
- [ ] **Perintah yang dijalankan:** `php artisan migrate` (membuat tabel `activity_logs`); bila Vite berjalan, tidak perlu build ulang selain `npm run build` untuk produksi
- [ ] **Verifikasi oleh kamu:** (1) `/admin/pengguna` tampil dan menu Pengguna/Log Aktivitas aktif untuk Super Admin; (2) tambah pengguna Editor, masuk dengan akun itu, wajib ganti password, `/admin/pengguna` memberi 403; (3) nonaktifkan lalu coba masuk (ditolak); (4) coba nonaktifkan/hapus diri sendiri dan Super Admin terakhir (ditolak dengan pesan); (5) reset password pengguna lalu masuk dengan password sementara; (6) Log Aktivitas dan dashboard menampilkan semua kejadian tadi; (7) lebar 360, 768, 1024, 1440 px
- [ ] Catatan penimpaan: `routes/admin.php`, `layouts/admin`, `pages/admin/dashboard`, `components/admin/icon`, `AdminMenu.php`, `DashboardController`, `LoginController`, `PasswordController`, dan `app/Models/User.php` **ditimpa** versi BA3 (tidak ada file lama yang perlu dihapus)

#### Pola Desain Admin (standar untuk semua modul berikutnya)

Disepakati 2 Nov 2026 setelah BA3 dicek di browser. Modul baru (Berita, Pilar & Brand, dst.) **wajib mengikuti pola ini**:
- **Sidebar gelap** navy `#0B1437`, logo putih (`on_dark`), item aktif bergradasi biru (`#2B4BC4` ke `#4A6CF0`), hover putih transparan, judul grup huruf kapital kecil abu terang, item belum jadi redup berlabel "Segera". Sidebar **otomatis scroll ke menu aktif** (skrip di `layouts/admin`) dan mengingat posisi scroll di halaman tanpa menu aktif
- **Halaman daftar:** `<x-admin.page-header>` (breadcrumb + judul + tombol aksi), kotak cari/filter putih rounded-2xl di atas tabel, `<x-admin.table>`, `<x-admin.pager>` di bawah, keadaan kosong `<x-admin.empty>`, aksi berbahaya lewat `<x-admin.confirm>` (satu modal per jenis aksi). Ikon aksi per baris (ubah, nonaktifkan, hapus) dengan `title` dan `aria-label`
- **Halaman tambah/ubah:** grid 3 kolom (`lg:grid-cols-3`); kolom kiri (2/3) berisi kartu `<x-admin.form-section icon title description>` per kelompok isian; kolom kanan (1/3) berisi **kartu pratinjau** (header gradasi tinggi `h-24`, avatar/gambar menumpuk di tepi bawah header dengan `-mt-10`, nama lalu detail ringkas) dan **tombol Simpan + Batal di dalam kartu itu** (menempel saat scroll, `lg:sticky lg:top-24`). Form dibungkus `<form class="contents">` agar kartu kiri dan kanan tetap satu grid; form kedua (mis. reset password) ditaruh **di luar** form utama
- **Pilihan bernilai sedikit** (peran, jenis, status) memakai **kartu radio** (`sr-only` + `peer-checked`), bukan dropdown; saklar `<x-admin.toggle>` ditaruh dalam kotak abu muda
- **Kolom password:** `<x-admin.password-field>` (Buat Otomatis, Salin, Tampilkan)
- **Tab bahasa ID | EN** (K32) dipasang di dalam `form-section` untuk konten dwibahasa
- Pratinjau langsung memakai Alpine (`x-model` pada `x-admin.input`); teks tombol Capitalize Each Word (K17); merah hanya untuk hapus/peringatan; semua teks admin Bahasa Indonesia
- **Hindari** variant Tailwind yang baru (`group-has-*`); pakai `peer-checked` dan varian arbitrer `[&_.kelas]:`
- Komponen yang sudah ada: `button`, `input`, `select`, `toggle`, `card`, `form-section`, `password-field`, `badge`, `table`, `pager`, `modal`, `confirm`, `toast`, `empty`, `icon`, `page-header`, `stat-card`

**BA4. Poles desain admin**
- [~] Putaran 1 (sidebar terang, topbar kaca, kartu dan tombol bergradasi) sudah dikirim (v30)
- [~] Putaran 2 (kecil): **badge "Admin" di dekat logo sidebar dihapus** (`layouts/admin.blade.php`); ikon panah `chevron-right` di tombol "Lihat Situs" ternyata belum ada di `components/admin/icon` dan ditambahkan, bersama ikon `pencil`, `trash`, `power`, `arrow-left`, `arrow-right`
- [~] Putaran 3: **sidebar digelapkan** (putih terlalu terang), **auto-scroll ke menu aktif**, halaman **tambah/ubah pengguna dan Profil Saya didesain ulang** (kartu per bagian, kartu radio peran, kartu pratinjau avatar di kanan). Perbaikan: nama pada kartu pratinjau sempat tertutup blok biru (margin negatif), kini avatar menumpuk di tepi header. File: `layouts/admin`, `components/admin/form-section` dan `password-field` (baru), `pages/admin/users/form`, `pages/admin/profile`. Hasil dikunci sebagai **Pola Desain Admin** di atas
- [~] Putaran 4: logo di sidebar diperkecil (`h-8` menjadi `h-6`, ukuran bisa diatur lewat class itu). File: `layouts/admin`
- [ ] Putaran revisi desain login, layout, dan dashboard berdasarkan masukanmu (seperti revisi halaman depan), lalu dikunci sebagai pola untuk semua modul

*Pengiriman: hanya file yang dibutuhkan, satu file satu kartu, dengan path tujuan dan perintah (`php artisan migrate`, perintah pembuat Super Admin).*

#### B1. Fondasi Data & Bahasa
- [ ] Jawaban QA1-QA6 dicatat sebagai keputusan baru di Bagian 2 (K27 dst.)
- [ ] Skema database (migration), dikelompokkan:
  - Sistem: `users` (+ peran, status aktif, 2FA), `settings` (kunci ke JSON), `media`, `redirects`, `activity_logs`, `messages`
  - Entitas konten: `pillars`, `brands`, `projects`, `awards`, `client_categories`, `clients`, `news_categories`, `news`, `milestones`, `offices`
  - Konten halaman: `page_contents` (satu baris per halaman/section, isi JSON dengan repeater) dan `pages` (halaman kustom/legal, sesuai QA1)
- [ ] Pola kolom dwibahasa JSON + `translation_status` (Manual / Otomatis (belum ditinjau) / Ditinjau) per record
- [ ] Slug unik per bahasa untuk entitas yang punya URL; redirect otomatis saat slug berubah
- [ ] Layer media sesuai QA7: upload, validasi tipe dan ukuran, konversi WebP, thumbnail responsif, alt text ID/EN, pangkas logo otomatis
- [ ] Peran dan *policy* sesuai QA5 (Super Admin, Admin, Editor)
- [ ] Perintah artisan **impor data awal** (idempoten) dari `Content.php`, `Pillars.php`, dan `public/img/clients/*`; logo dan foto yang ada ikut masuk Media Library (QA4, QA7)
- [ ] Strategi cache: hasil query di-cache (driver `file`), dibersihkan otomatis saat admin menyimpan

#### B2. Admin: Fondasi
- [ ] Login aman (batas percobaan, lupa password), 2FA sesuai QA5, alamat `/admin` ber-`noindex`
- [ ] Layout admin: sidebar, topbar, breadcrumb, responsif; antarmuka dan pesan validasi berbahasa Indonesia
- [ ] Komponen reusable: tabel (cari/filter/sort/paginasi), form, toggle, select, repeater (tambah/hapus/urut), modal konfirmasi, toast
- [ ] Upload gambar (drag-and-drop, preview, crop) dan pemilih dari Media Library
- [ ] Editor teks kaya yang bersih (sesuai QA12)
- [ ] Tab bahasa ID | EN + indikator status terjemahan di semua form
- [ ] Dashboard: pesan belum dibaca, draf berita, terjemahan belum ditinjau, **checklist item DUMMY yang tersisa** (lihat daftar di bawah) (QA18)
- [ ] Log aktivitas (QA18)

#### B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)
- [ ] **B3a. Pengaturan Situs:** identitas, logo terang/gelap, favicon, kontak (email umum, email karir, telepon, WhatsApp), sosmed, kantor (tambah/ubah/urut, foto, tautan peta), teks footer, SEO bawaan situs
- [ ] **B3b. Media Library:** grid, cari, filter jenis, ganti nama, alt text, lihat di mana dipakai, cegah hapus bila masih dipakai
- [~] **B3c. Pilar & Brand (v36, end-to-end: admin + frontend dari database, K34):** layar `/admin/pilar-bisnis` memuat 8 pilar beserta brand-nya dalam satu halaman: urut seret-dan-lepas (pilar dan brand dalam pilar) dengan tombol panah sebagai cadangan, ikon mata untuk tampil/sembunyi, penanda Dummy / Tersembunyi / Tanpa deskripsi / Tanpa logo, hapus lewat modal (hanya Super Admin). Form pilar (nama, slug, ikon, tampil) dan form brand (pilar induk, nama, slug, logo, deskripsi ID | EN, status terjemahan, Instagram, TikTok, tautan, butir Fokus & Layanan, tampil) mengikuti Pola Desain Admin. Logo unggahan dipangkas otomatis (`ImageStore::storeLogo`). Semua aksi masuk Log Aktivitas (grup baru Pilar Bisnis dan Brand). Frontend (menu, footer, pencarian, beranda, daftar pilar, detail pilar, profil brand, Karir) membaca database lewat `App\Support\PillarRepo` dengan bentuk data yang sama seperti `Pillars::all()`; bila tabel belum ada atau masih kosong otomatis memakai data kode, jadi situs tidak pernah kosong. Dashboard: item "Brand Commercial Technology dan 10 logo brand baru" tercentang otomatis bila tidak ada brand Dummy dan semua brand tampil punya berkas logo; angka "Item Dummy Tersisa" kini menghitung item yang belum selesai. Belum termasuk: SEO per pilar/brand dan redirect saat slug berubah (B3n), thumbnail dan pelacak pemakaian logo (B3b), cache query (B1), tombol terjemah otomatis (B5), pemindahan brand dengan seret antarpilar (pakai Ubah Brand).
  - [ ] **Perintah yang dijalankan:** `php artisan migrate` (tabel `pillars` dan `brands`), `php artisan content:import-pillars` (8 pilar + 20 brand, aman diulang; `--refresh` menimpa brand Dummy yang belum disentuh admin), `php artisan storage:link` (lewati bila sudah pernah, dari B3d), lalu `npm run build` (atau `npm run dev` yang sedang jalan) karena ada kelas Tailwind baru
  - [ ] **Verifikasi oleh kamu:** (1) menu Pilar & Brand aktif untuk Super Admin dan Admin, tidak muncul untuk Editor (URL langsung memberi 403); (2) sebelum impor, situs publik tetap menampilkan pilar dari kode; setelah impor, daftar admin berisi 8 pilar + 20 brand dan situs publik tidak berubah; (3) seret pilar dan brand ke urutan baru (toast "Urutan disimpan"), muat ulang: urutan bertahan dan menu/footer/halaman Pilar Bisnis mengikuti; coba juga tombol panah; (4) sembunyikan brand dan pilar lewat ikon mata: hilang dari situs (menu, footer, pencarian, halaman), halaman langsung memberi 404; (5) tambah pilar baru lalu brand di dalamnya (hanya nama) dan cek `/id/pilar-bisnis/...`; (6) ubah brand: deskripsi ID saja (EN menampilkan teks ID), lalu isi EN; (7) unggah logo PNG berlatar kosong besar: terpangkas rapi, tersimpan WebP di `storage/app/public/brands`; coba file > 5 MB dan file bukan gambar (ditolak); (8) pindahkan brand ke pilar lain; (9) isi tautan `http://...`: tersimpan `https://...`; (10) Training & Research Centre: butir Fokus & Layanan tampil di profil brand; tambah, urut, hapus butir; (11) slug kembar ditolak; (12) hapus brand dan pilar sebagai Super Admin; pilar yang masih punya brand ditolak, Admin tidak melihat tombol hapus (URL hapus memberi 403); (13) Log Aktivitas mencatat semuanya; (14) dashboard: item brand tercentang setelah semua brand Dummy disimpan dan semua punya logo; (15) lebar 360, 768, 1024, 1440 px
  - [ ] Catatan penimpaan: `routes/admin.php`, `AdminMenu.php`, `ActivityLog.php`, `ImageStore.php`, `Content.php` (hanya `rawUnits()`), dan `DashboardController.php` **ditimpa**; tidak ada file lama yang perlu dihapus
- [~] **B3d. Berita & Kategori (v34, end-to-end: admin + frontend dari database):** daftar + cari/filter status/kategori; form dengan tab ID | EN (judul, ringkasan, isi), status Draf/Terbit/Terjadwal (tampil otomatis saat waktunya tiba, **tanpa cron**), kategori, slug aman (huruf kecil-angka-hubung, daftar kata dicadangkan, unik), status terjemahan, gambar utama (maks 5 MB, dikecilkan, WebP, alt ID/EN), SEO ID/EN, **riwayat revisi** (20 terakhir, tombol Kembalikan), editor teks kaya ringan (P, H2, H3, tebal, miring, daftar, kutipan, tautan, gambar; tanpa video dan tabel), isi disaring server (`App\Support\Html`); kategori dikelola penuh (Super Admin/Admin, tidak bisa hapus bila masih dipakai); Editor boleh menulis dan mengubah tetapi tidak menghapus; semua aksi masuk Log Aktivitas; dashboard menghitung Berita Draf asli. Frontend (daftar, kategori, detail, beranda, pencarian) membaca database; bila tabel belum ada otomatis memakai dummy kode. Kategori lama "Komunitas" kini **Keberlanjutan** (slug `community` tetap). Belum termasuk: tombol terjemah otomatis (B5), thumbnail dan Media Library (B3b), redirect saat slug berubah (B3n), cache query (B1), live preview (setelah semua CRUD)
- **v35, melengkapi B3d:** view admin `pages/admin/news/{index,form}` dan `pages/admin/news/categories/{index,form}` dibuat (daftar + filter, form ID | EN, gambar utama + pratinjau, kartu radio status, SEO, Riwayat Revisi + Kembalikan, hapus lewat modal); view publik `pages/news/show` kini memakai `body_html` (HTML dari editor), `image_alt`, `seo_title`, `seo_description`; `partials/news-list` dan `pages/home` memakai `image_alt`; pintasan \"Tambah Berita\" di dashboard aktif. **Nama file dibetulkan agar sama dengan nama kelas (wajib di Linux/Docker):** `NewsCategory.php`, `NewsRevision.php`, `ImageStore.php`, `NewsRepo.php`, `NewsCategoryController.php`, `NewsController.php`, `ImportNews.php` (sebelumnya huruf kecil, mis. `Newscontroller.php`, sehingga kelas tidak ditemukan). Hapus file lama berhuruf kecil bila masih ada.
  - [ ] **Perintah yang dijalankan:** `php artisan migrate` (tabel `news_categories`, `news`, `news_revisions`), `php artisan content:import-news` (5 kategori + 9 berita dummy, aman diulang; `--refresh` menimpa dummy yang belum disentuh admin), `php artisan storage:link` (agar gambar unggahan tampil; lewati bila sudah pernah), lalu `npm run build` (atau `npm run dev` yang sedang jalan) karena ada kelas Tailwind baru
  - [ ] **Verifikasi oleh kamu:** (1) menu Berita dan Kategori Berita aktif; (2) daftar menampilkan 9 berita dummy berlabel Dummy; (3) tambah berita ID saja (EN kosong) lalu cek `/id/berita/...` dan `/en/news/...` (EN menampilkan teks ID); (4) isi tab EN dan cek lagi; (5) editor: subjudul, daftar, tautan, tempel dari Word, sisip gambar; (6) unggah gambar utama, cek WebP di `storage/app/public/news`; (7) Terjadwal dengan waktu 2 menit lagi: belum tampil, lalu tampil sendiri; (8) ubah dan simpan dua kali, coba Kembalikan di Riwayat Revisi; (9) kategori: tambah, ubah, coba hapus yang masih dipakai (ditolak), slug `kategori` ditolak; (10) masuk sebagai Editor: bisa tulis, tidak ada tombol hapus dan menu Kategori, URL hapus memberi 403; (11) Log Aktivitas mencatat semuanya; (12) lebar 360, 768, 1024, 1440 px
  - [ ] Catatan penimpaan: `routes/admin.php`, `AdminMenu.php`, `DashboardController`, `Content.php`, `ActivityLog.php`, `components/admin/{input,select,icon}`, `pages/admin/dashboard`, `pages/news/show`, `partials/news-list`, `pages/home` **ditimpa**. **Hapus file lama berhuruf kecil** di `app/Models` (`Newscategory.php`, `Newsrevision.php`), `app/Support` (`Imagestore.php`, `Newsrepo.php`), `app/Http/Controllers/Admin` (`Newscontroller.php`, `Newscategorycontroller.php`), `app/Console/Commands` (`Importnews.php`), lalu `composer dump-autoload`
- [ ] **B3e. Beranda:** edit teks tiap section, pilih proyek unggulan, tampil/sembunyi + urut section (QA1)
- [ ] **B3f. Tentang Kami:** Profil + Chairman Message, Visi & Misi + nilai, Perjalanan Kami (lini waktu, urut tahun)
- [~] **B3g. Proyek (v39, end-to-end: admin + frontend dari database, K37):** layar `/admin/proyek` (daftar + cari klien/judul/slug, filter pilar/tahun/status, tombol cepat tampil-sembunyi dan unggulan, paginasi), form tambah/ubah dengan tab ID | EN (judul, ringkasan, deskripsi, lingkup pekerjaan), pilar + brand bertingkat, klien, tahun, gambar utama (WebP, maks. 5 MB), saklar tampil dan unggulan, SEO, kartu pratinjau. Halaman publik (`Content::portfolio()`, `projects()`, halaman Pilar Bisnis, pencarian, beranda) kini membaca `ProjectRepo` dengan bentuk data yang sama, jadi view tidak diubah; bila tabel belum ada, situs memakai data contoh di kode (`Content::projectSeed()`). Menu Proyek aktif untuk semua peran; log aktivitas dan kartu "Kesiapan Konten" di dashboard ikut (proyek contoh dihitung selesai bila tidak ada lagi yang berstatus Dummy)
  - [ ] **Perintah yang dijalankan:** `php artisan migrate` (tabel `projects`), `php artisan content:import-projects` (9 proyek contoh, aman diulang; `--refresh` menimpa proyek contoh yang belum disentuh admin; butuh `content:import-pillars` sudah dijalankan), `php artisan optimize:clear`
  - [ ] **Verifikasi oleh kamu:** (1) menu Proyek aktif; (2) daftar berisi 9 proyek berlabel Dummy, 3 di antaranya Unggulan; (3) `/id/portofolio` dan `/en/portfolio` tampil seperti sebelum migrasi (filter pilar dan tahun tetap jalan); (4) tambah proyek baru: pilih pilar lalu daftar brand ikut berubah, isi ID saja, cek `/en/portfolio/{slug}` (EN menampilkan teks ID); (5) centang unggulan di proyek lain lalu cek beranda (maks. 3 kartu); (6) sembunyikan satu proyek, hilang dari Portofolio, halaman pilar, dan pencarian; (7) login sebagai Editor: bisa tambah/ubah, tidak ada tombol hapus; (8) hapus proyek contoh bila sudah diganti proyek asli, kartu dashboard ikut tercentang
  - [ ] Catatan penimpaan: `routes/admin.php`, `AdminMenu.php`, `ActivityLog.php`, `DashboardController.php`, dan `Content.php` (hanya `portfolio()`, `projects()`, ditambah `projectSeed()`) **ditimpa**; tidak ada file lama yang perlu dihapus
- [ ] **B3h. Klien & Mitra:** kategori dikelola, unggah banyak logo sekaligus, urut, tampil di beranda
- [ ] **B3i. Penghargaan & Sertifikat:** judul, tahun, deskripsi, foto, unggulan manual (tanpa jenis, penerbit, PDF; QA11)
- [ ] **B3j. Keberlanjutan:** tentang, fokus, program, ajakan bergabung
- [ ] **B3k. Karir:** pengantar, alasan, langkah seleksi; bidang diambil otomatis dari 8 pilar
- [ ] **B3l. Kontak & Pesan Masuk:** pengaturan subjek (tujuan email selalu `info@decaa.id`, K31); kotak masuk (status baca/belum, balas lewat email, hapus, ekspor CSV)
- [ ] **B3m. Halaman Legal/Kustom:** Kebijakan Privasi (8 bagian + daftar isi + tanggal) dan halaman kustom (QA1)
- [ ] **B3n. SEO, Redirect, Sitemap:** SEO per halaman (ID/EN), layar Redirect, `sitemap.xml` per bahasa dibuat otomatis (QA16); **juga** redirect 301 otomatis saat slug Berita, Pilar, dan Brand berubah, serta kolom SEO per pilar dan brand
- [ ] **B3o. Menu, Pengguna & Role:** sesuai QA2 dan QA5
- [ ] **B3p. Pengaturan Sistem:** mode pemeliharaan + tautan rahasia (QA17), ID analitik dan kode verifikasi (QA16), alamat email pengirim (`info@decaa.id`)

#### B4. Menghubungkan Frontend ke Database
- [ ] Ganti isi `App\Support\Content` dan `Pillars` menjadi query database **per modul** (bentuk array sama, view tidak ditulis ulang), mengikuti QA3
- [ ] Form Kontak aktif: validasi, simpan ke `messages`, email notifikasi ke `info@decaa.id` (satu-satunya akun email, K31), balasan otomatis, anti-spam (QA14); hapus teks `preview_note`
- [ ] Pencarian situs berfungsi dari database (berita, pilar, brand, portofolio, halaman); kata kunci minimal 2 huruf tetap
- [ ] Filter Portofolio dan Klien & Mitra tetap instan (Alpine), datanya dari database
- [ ] Bersihkan kode yang tak terpakai: `Content::stats()`, `TrimLogos.php`, `about/team.blade.php`, `about/legal.blade.php`, salinan `resources/js/vite.config.js`
- [ ] Uji ID dan EN di semua halaman, dan fallback ke ID bila terjemahan EN kosong

#### B5. Terjemahan Otomatis (lihat Bagian 4A)
- [~] Lapisan penyedia terjemahan + glosarium proteksi (v37: `config/translator.php`, `app/Support/Translator.php`, drivers DeepL/Google/Claude, brand otomatis ikut proteksi) (nama brand: DECAA.ID, Decaa Software, Satu AI, dst.)
- [~] Terjemahan otomatis saat simpan untuk **Berita** (v37, K35); tombol manual dan modul lain (Brand, Halaman Kustom) menyusul
- [ ] Perintah artisan terjemahan massal via queue (queue `sync` diganti `database`)
- [ ] Label "Diterjemahkan otomatis" dan fallback ke ID; halaman inti wajib ditinjau sebelum tampil di EN

#### B6. Migrasi dari WordPress
- [x] Jumlah artikel dan media dibahas (K15): **171 berita terbit**, tidak ada Elementor/Gutenberg, 35 gambar di dalam isi (K36)
- [~] Ekspor dari WordPress: data artikel, kategori, dan gambar unggulan sudah diekstrak dari dump SQL ke `database/imports/wp_news.json`. **Yang masih di sisimu:** unduh folder `wp-content/uploads` dari hosting lama (cPanel File Manager, Compress lalu Download)
- [~] Skrip impor (v38): `app/Console/Commands/ImportWpNews.php` + `app/Support/WpNewsConverter.php` + migration `2026_11_03_090000_add_wp_id_to_news_table` (kolom `wp_id`). Merapikan HTML lewat `Html::clean`, mengganti alamat gambar `https://decaa.id/wp-content/uploads/` menjadi `/wp-uploads/`, memetakan kategori ke 4 kategori baru, membuat ringkasan otomatis, opsi `--dry-run`, `--refresh`, `--skip-images`. Diuji di lingkunganku terhadap 171 berita (slug unik semua, tidak ada isi kosong, tidak ada gambar yang masih menunjuk ke domain lama). Konversi WebP **tidak** dilakukan (nama file asli dipertahankan, K36)
  - [ ] **Perintah yang dijalankan:** `php artisan migrate`, lalu salin `uploads` ke `public/wp-uploads/` (struktur tahun/bulan dan nama file jangan diubah), lalu `php artisan content:import-wp --dry-run`, `php artisan content:import-wp`, `php artisan optimize:clear`
  - [ ] **Verifikasi oleh kamu:** (1) dry-run melaporkan 171 dibuat dan tidak ada gambar hilang (atau daftar gambar hilang dikirim ke aku); (2) `/admin/berita` berisi 171 berita + 9 dummy; (3) buka beberapa berita di `/id/berita/...` dan `/en/news/...` (EN menampilkan teks ID); (4) berita yang punya gambar di dalam isi (mis. artikel IAPT) tampil lengkap; (5) kartu berita menampilkan gambar unggulan; (6) hapus 9 berita dummy bila sudah tidak diperlukan
- [ ] Peta redirect 301 URL lama ke baru (lewat layar Redirect, B3n); slug lama dipertahankan sehingga pemetaan mudah dibuat
- [x] ~~Terjemahan massal artikel lama (B5)~~ **Tidak dilakukan** atas keputusanmu (menghemat kuota API); EN kosong jatuh ke ID. Catatan: menyimpan berita lama di admin dengan toggle terjemahan menyala akan menerjemahkan EN saat itu (matikan toggle bila ingin hemat)
- [ ] Verifikasi hasil impor (sampel + hitung jumlah): lihat daftar verifikasi di atas

#### Daftar DUMMY yang harus diganti lewat admin sebelum go-live
Ini juga menjadi sumber checklist di dashboard admin.
- [ ] Telepon (`+62 21 0000 0000`), WhatsApp, dan 6 URL sosmed (`#`)
- [ ] Chairman Message: nama, foto, isi pesan
- [ ] Teks Beranda: 4 nilai perusahaan; blok Keberlanjutan; "Satu pintu" di halaman Pilar Bisnis
- [ ] Program Komunitas Peduli Digital (4 program contoh)
- [ ] Kebijakan Privasi (draf, **wajib ditinjau pihak hukum**)
- [ ] Brand Commercial Technology (7 brand tanpa deskripsi dan logo) dan 10 logo brand baru (kini ditandai **Dummy** di admin Pilar & Brand; item dashboard tercentang otomatis)
- [ ] Misi (Q13) dan foto Perjalanan Kami per tahun
- [ ] Foto dummy: hero (3), tentang (2), komunitas, berita, proyek, penghargaan, tim/kantor
- [ ] 9 berita contoh (tanda Dummy di admin): ganti atau hapus; nanti digantikan impor WordPress (B6) *(v38: impor WordPress sudah tersedia, K36)*
- [ ] Nama proyek dan klien dummy di Portofolio; 6 penghargaan contoh *(v39: proyek sudah bisa diganti lewat admin Proyek, K37)*

---

### TAHAP C: SEO, PERFORMA, KEAMANAN

**Pengaturan `.env` per lingkungan (hasil pemeriksaan `.env` lokal, 2 Nov 2026)**
- [ ] **Lokal sekarang:** `MAIL_MAILER=log` (di Docker tidak ada Mailpit; email masuk ke `storage/logs/laravel.log`), `MAIL_FROM_ADDRESS="info@decaa.id"`. Setelah ubah `.env`: `php artisan config:clear`
- [ ] **Staging dan produksi (K31):** `MAIL_MAILER=smtp`, `MAIL_HOST=mail.decaa.id`, `MAIL_PORT=465`, `MAIL_USERNAME=info@decaa.id`, `MAIL_ENCRYPTION=tls` (di Laravel 9, port 465 wajib `tls` supaya dipakai `smtps`; `ssl` bisa gagal), `MAIL_FROM_ADDRESS=\"info@decaa.id\"`. Password hanya di `.env` server, tidak pernah dikirim ke chat atau ditulis di berkas
- [ ] **Temuan: noindex staging belum tersambung.** `layouts/app` membaca `config('site.noindex')`, tetapi `config/site.php` tidak punya kuncinya. Tambahkan `'noindex' => (bool) env('SITE_NOINDEX', false),` lalu `SITE_NOINDEX=true` di staging dan `false` di produksi
- [ ] Staging/produksi: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://new.decaa.id` (lalu `https://decaa.id`), `APP_KEY` dan password database **baru** (jangan pakai nilai lokal; `/style-guide` hanya aktif di `local`)
- [ ] Uji kirim email dari server staging (form Kontak, B4) dan cek SPF/DKIM/DMARC `decaa.id` agar email tidak masuk spam

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
- [ ] Pilar bisnis (8 jenis bisnis dan brand-nya) dan bukti kerja (portofolio, klien, penghargaan) jelas
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
- **(I10)** Checklist kesiapan konten di dashboard admin: daftar item DUMMY yang tersisa sebelum go-live (diusulkan masuk B2, lihat QA18)

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
| 2 Nov 2026 | v24: **Revisi dari `Revisi_Website_decaaid.pdf`, tahap R1**: slogan baru, Pilar Bisnis = 8 jenis bisnis + 17 brand (K22), Jajaran Kepemimpinan dihapus (K23), menu Penghargaan ditambahkan (K25), Karir ikut pilar. File: `app/Support/Pillars.php` (baru), `app/Support/Content.php`, `config/site.php`, `partials/header`, `pages/home`, `pages/units/index`, `pages/units/show`, `pages/careers`, `pages/about/profile`, `components/ui/pillar-mark` (baru), `components/ui/icon`. Tahap R2-R5 menyusul |
| 2 Nov 2026 | v25: **R2 Beranda**: hero Capitalize Each Word + label DECAA.ID; Tentang DECAA.ID (judul dan deskripsi baru); Pilar Bisnis tampil per brand 5 per baris. File: `app/Support/Content.php`, `resources/views/pages/home.blade.php` |
| 2 Nov 2026 | v26: **Perencanaan Tahap B (Admin)**. Ditambah Bagian 3B (QA1-QA18, pertanyaan untuk dijawab); Bagian 6 (sitemap) diperbarui (8 pilar, halaman brand, Penghargaan, Chairman Message; Kepemimpinan dan Legalitas dihapus); Tahap B disusun ulang (peta konten ke modul admin, B1-B6 dirinci, daftar DUMMY). Temuan: brand sekarang 20 (bukan 17), `Content::stats()` tidak dipakai lagi. **Tanpa perubahan kode** |
| 2 Nov 2026 | v27: jawaban QA1-QA18 dicatat (K27-K32), koreksi email (hanya `info@decaa.id`, `mail.decaa.id` = server), Super Admin awal `decaa@decaa.id`; ditambah **Tahap B-awal** (BA1-BA4: login, layout + dashboard dummy, CRUD pengguna, poles desain); lingkup Penghargaan disederhanakan. **Tanpa perubahan kode** |
| 2 Nov 2026 | v28: **BA1 dikirim** (fondasi login dan pengguna). File baru: migration `2026_11_02_000001_add_admin_columns_to_users_table`, `app/Console/Commands/CreateSuperAdmin.php`, `routes/admin.php`, 5 middleware di `app/Http/Middleware/Admin/`, `LoginController`, `PasswordController`, `DashboardController` (sementara) di `app/Http/Controllers/Admin/`, `lang/id/auth.php`, `lang/id/validation.php`, `layouts/admin`, `pages/admin/auth/login`, `pages/admin/password`, `pages/admin/dashboard` (sementara). File diubah: `app/Models/User.php`, `app/Http/Kernel.php`, `app/Http/Middleware/Authenticate.php`, `app/Providers/RouteServiceProvider.php`. Belum diuji di lingkunganku (PHP tidak tersedia); menunggu verifikasimu |
| 2 Nov 2026 | v28: **BA1 dikirim**: migration kolom admin pada `users`, model `User` (peran), perintah `admin:create-super`, `routes/admin.php`, 5 middleware admin, `LoginController` (throttle), `PasswordController`, `lang/id/*`. Menunggu dicek di Docker |
| 2 Nov 2026 | v29: **BA2 dikirim**: layout admin (sidebar per peran, topbar, drawer HP) + `layouts/admin-guest`, 14 komponen `components/admin/*`, halaman Masuk dan Ganti Password didesain ulang, dashboard dummy (4 kartu, aktivitas, pintasan, kesiapan konten). File baru: `app/Support/AdminMenu.php`, `components/admin/*`. File diubah: `DashboardController.php`, `layouts/admin.blade.php`, `pages/admin/dashboard.blade.php`, `pages/admin/auth/login.blade.php`, `pages/admin/password.blade.php`. Tanpa perubahan route, migration, atau JS/CSS |
| 2 Nov 2026 | v30: **BA4 putaran 1 (revisi tampilan admin)** atas masukan "terlalu tegas, kurang menarik, tulisan sidebar kurang jelas": sidebar jadi **terang** (teks gelap 15px tebal, ikon dalam kotak, judul grup lebih jelas, item aktif bergradasi biru, "Lihat Situs" di bawah), topbar kaca dengan tanggal, latar gradasi lembut, kartu `rounded-2xl` bayangan halus, tombol bergradasi, kolom isian lembut, hero sambutan di dashboard, kartu angka dengan ikon gradasi, panel Masuk bergradasi + 3 poin keunggulan. File diubah: `layouts/admin`, `layouts/admin-guest`, `pages/admin/dashboard`, `login`, `password`, `components/admin/{button,input,select,card,table,empty,badge,modal,toast,page-header,stat-card}`. Tanpa perubahan route, controller, atau JS/CSS |
| 2 Nov 2026 | v31: **BA3 dikirim** (CRUD pengguna, profil, log aktivitas) + **badge "Admin" di dekat logo sidebar dihapus**. File baru: `database/migrations/2026_11_02_160000_create_activity_logs_table.php`, `app/Models/ActivityLog.php`, `app/Http/Controllers/Admin/{UserController,ProfileController,LogController}.php`, `components/admin/pager.blade.php`, `pages/admin/users/{index,form}.blade.php`, `pages/admin/profile.blade.php`, `pages/admin/logs/index.blade.php`. File diubah: `routes/admin.php`, `app/Support/AdminMenu.php`, `app/Models/User.php`, `LoginController`, `PasswordController`, `DashboardController`, `layouts/admin`, `pages/admin/dashboard`, `components/admin/icon`. Belum diuji di lingkunganku (PHP tidak tersedia); menunggu verifikasimu |
| 2 Nov 2026 | v32: **BA4 putaran 3**: sidebar gelap + auto-scroll menu aktif, redesain form tambah/ubah pengguna dan Profil Saya, perbaikan kartu pratinjau. Ditambah **Pola Desain Admin** (standar styling modul berikutnya) dan aturan kerja no. 9-11 (info path file di teks balasan, pola admin dikunci, daftar folder zip). File: `layouts/admin`, `components/admin/{form-section,password-field}`, `pages/admin/users/form`, `pages/admin/profile`, `todolist_decaa_web.md` |
| 2 Nov 2026 | v33: logo sidebar admin diperkecil (`layouts/admin`); hasil pemeriksaan `.env` dicatat di Tahap C (email lokal `log`, SMTP `mail.decaa.id` port 465 `tls` untuk staging, kunci `site.noindex` yang belum ada di `config/site.php`, aturan `.env` staging/produksi). Tanpa perubahan kode selain layout |
| 2 Nov 2026 | v34: **B3d Berita & Kategori dikirim** (admin + frontend dari database, K33). File baru: `database/migrations/2026_11_02_170000_create_news_tables.php`, `app/Models/{News,NewsCategory,NewsRevision}.php`, `app/Support/{Html,ImageStore,NewsRepo}.php`, `app/Console/Commands/ImportNews.php`, `app/Http/Controllers/Admin/{NewsController,NewsCategoryController}.php`, `components/admin/{editor,textarea}.blade.php`, `pages/admin/news/{index,form}.blade.php`, `pages/admin/news/categories/{index,form}.blade.php`. File diubah: `routes/admin.php`, `app/Support/{AdminMenu,Content}.php`, `app/Models/ActivityLog.php`, `DashboardController`, `components/admin/{input,select,icon}`, `pages/admin/dashboard`, `pages/news/show`, `partials/news-list`, `pages/home`. Uji: lint PHP, 14 kasus uji sanitizer HTML, uji konversi data dummy; **belum dijalankan di Laravel** (Packagist tidak terjangkau dari lingkunganku), menunggu verifikasimu |
| 2 Nov 2026 | v35: **B3d dilengkapi.** File baru: `pages/admin/news/index.blade.php`, `pages/admin/news/form.blade.php`, `pages/admin/news/categories/index.blade.php`, `pages/admin/news/categories/form.blade.php`. File diubah: `pages/news/show.blade.php` (isi dari `body_html`, alt gambar, judul/deskripsi SEO), `partials/news-list.blade.php`, `pages/home.blade.php` (`image_alt`), `pages/admin/dashboard.blade.php` (pintasan aktif). **Nama 7 file dibetulkan** sesuai nama kelas (lihat catatan di B3d). Belum diuji (PHP tidak tersedia di lingkunganku); menunggu verifikasimu di Docker |
| 2 Nov 2026 | v36: **B3c Pilar & Brand dikirim** (admin + frontend dari database, K34). File baru: `database/migrations/2026_11_02_180000_create_pillars_tables.php`, `app/Models/{Pillar,Brand}.php`, `app/Support/PillarRepo.php`, `app/Console/Commands/ImportPillars.php`, `app/Http/Controllers/Admin/{PillarController,BrandController}.php`, `pages/admin/pillars/{index,form,brand-form}.blade.php`. File diubah: `routes/admin.php`, `app/Support/{AdminMenu,Content,ImageStore}.php` (`Content` hanya `rawUnits()`; `ImageStore` + `storeLogo()` pangkas logo), `app/Models/ActivityLog.php`, `DashboardController`. Uji: keseimbangan kurung PHP dan direktif Blade; **belum dijalankan di Laravel** (PHP tidak tersedia di lingkunganku), menunggu verifikasimu di Docker |
| 2 Nov 2026 | v37: **Terjemahan otomatis Berita (B5 awal, K35).** File baru: `config/translator.php`, `app/Support/{Translator,NewsAutoTranslate}.php`, `database/migrations/2026_11_02_220000_add_translated_from_to_news_table.php` (kolom `translated_from`). File diubah: `app/Models/News.php`, `app/Http/Controllers/Admin/NewsController.php`, `pages/admin/news/form.blade.php` (saklar, teks bantuan, kolom ID tidak lagi bertanda wajib), `todolist_decaa_web.md`. Sintaks PHP sudah dicek (`php -l`) dan logika glosarium diuji; panggilan ke DeepL/Google/Claude **belum diuji** (butuh kunci API), menunggu verifikasimu di Docker |
| 3 Nov 2026 | v38: **Perbaikan terjemahan otomatis + B6 impor WordPress (K36).** (1) Penyebab status "belum aktif": file config salah nama `config/transalator.php`, kode memanggil `config('translator.*')`; diganti `config/translator.php`, lalu `php artisan config:clear`. (2) Area tulis editor berita diberi border dan bingkai (`components/admin/editor.blade.php`). (3) Impor berita WordPress: file baru `app/Console/Commands/ImportWpNews.php`, `app/Support/WpNewsConverter.php`, `database/migrations/2026_11_03_090000_add_wp_id_to_news_table.php`, `database/imports/wp_news.json` (171 berita). Tanpa terjemahan; gambar disalin apa adanya ke `public/wp-uploads/`. Menunggu `php artisan migrate` dan dicek di Docker-mu |
| 3 Nov 2026 | v39: **B3g Proyek dikirim** (admin + frontend dari database, K37). File baru: `database/migrations/2026_11_03_100000_create_projects_table.php`, `app/Models/Project.php`, `app/Support/ProjectRepo.php`, `app/Http/Controllers/Admin/ProjectController.php`, `app/Console/Commands/ImportProjects.php`, `resources/views/pages/admin/projects/{index,form}.blade.php`. File diubah: `routes/admin.php`, `app/Support/AdminMenu.php`, `app/Models/ActivityLog.php`, `app/Http/Controllers/Admin/DashboardController.php`, `app/Support/Content.php`. Menunggu `php artisan migrate`, `php artisan content:import-projects`, dan dicek di Docker-mu |