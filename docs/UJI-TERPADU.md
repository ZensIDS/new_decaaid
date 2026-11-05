# Uji Terpadu DECAA.ID (daftar verifikasi dari seluruh todolist)

> Dibuat otomatis dari butir **Verifikasi oleh kamu** di `todolist_decaa_web.md` (v100). Tidak ada butir yang ditambah atau diubah isinya; yang berbeda hanya urutan dan bentuknya: satu daftar, berurutan dari persiapan sampai pasca live, dengan kotak centang. Centang di sini, lalu pindahkan hasilnya ke todolist (`[~]` menjadi `[x]`) atau kirim butir yang gagal kepadaku.

**Isi:** 50 kelompok uji, 424 butir. Perkiraan waktu: satu sampai dua hari bila dikerjakan berurutan (perkiraan kasar, bergantung seberapa banyak yang sudah pernah kamu coba).

**Cara membaca versi:** `(v75)` berarti butir itu dibuat saat versi 75.

**Butir "sebelum migrate":** hanya bisa dicoba bila migration terkait belum dijalankan. Bila sudah, lewati saja butir itu.

## A. Persiapan (kerjakan sekali di awal)

1. Timpa semua berkas dari zip terakhir, lalu jalankan berurutan:

```bash
php artisan migrate:status        # lihat migration yang belum jalan (kolom "Pending")
php artisan migrate               # jalankan semuanya
php artisan storage:link
php artisan optimize:clear        # bersihkan cache config, rute, dan view
php artisan site:check            # pemeriksa lingkungan + tag komponen Blade
php artisan site:links            # pemeriksa halaman sitemap dan tautan internal
```

2. Perintah lain yang disebut di todolist (jalankan hanya bila butirnya kamu uji):

   - `php artisan media:sync`
   - `php artisan content:import-pillars`
   - `php artisan content:import-news`
   - `php artisan content:import-projects`
   - `php artisan content:import-clients`
   - `php artisan content:import-awards`
   - `php artisan redirects:import-wp --dry-run`
   - `php artisan redirects:import-wp`
   - `php artisan content:import-wp --dry-run`
   - `php artisan content:import-wp`
   - `php artisan site:backup`
   - `php artisan site:links`
   - `php artisan site:check`
   - `php artisan site:check --production`

3. Migration yang ada di proyek (26 berkas). Semua harus berstatus **Ran** di `php artisan migrate:status`:

<details><summary>Daftar migration</summary>

- `2014_10_12_000000_create_users_table`
- `2014_10_12_100000_create_password_resets_table`
- `2019_08_19_000000_create_failed_jobs_table`
- `2019_12_14_000001_create_personal_access_tokens_table`
- `2026_11_02_142957_add_admin_columns_to_users_table`
- `2026_11_02_151833_create_activity_logs_table`
- `2026_11_02_170000_create_news_table`
- `2026_11_02_195913_create_pillars_tables`
- `2026_11_02_203714_add_translated_from_to_news_table`
- `2026_11_03_082452_add_wp_id_to_news_table`
- `2026_11_03_084625_create_projects_table`
- `2026_11_03_120000_create_clients_tables`
- `2026_11_03_130000_create_awards_table`
- `2026_11_03_140000_create_page_contents_table`
- `2026_11_03_150000_create_custom_pages_table`
- `2026_11_03_160000_create_contact_messages_table`
- `2026_11_03_170000_create_redirects_table`
- `2026_11_03_180000_create_media_table`
- `2026_11_04_100000_add_seo_to_pillars_and_brands`
- `2026_11_04_110000_add_two_factor_to_users_table`
- `2026_11_04_120000_create_custom_page_revisions_table`
- `2026_11_04_130000_add_translation_to_custom_pages`
- `2026_11_04_140000_add_translated_from_to_awards_and_projects`
- `2026_11_05_100000_add_seo_image_to_custom_pages`
- `2026_11_05_110000_add_footer_column_to_custom_pages`
- `2026_11_05_120000_add_translated_from_to_brands`

</details>

4. `.env` yang perlu terisi sesuai tujuan: penyedia terjemahan (`TRANSLATE_DRIVER` + kunci), email (`MAIL_*`), `SITE_NOINDEX` (true hanya di staging), `SECURITY_HSTS` (true hanya di produksi HTTPS), Turnstile (opsional). Setelah mengubah `.env`: `php artisan config:clear`.

5. Siapkan **tiga akun uji**: Super Admin, Admin, dan Editor. Banyak butir meminta membandingkan hak akses ketiganya.

## Daftar isi uji

1. [Tampilan dasar admin: login, dashboard, ganti password](#uji-1) 
2. [Pengguna dan Log Aktivitas](#uji-2) 
3. [Lupa password lewat email](#uji-3) *(v73, lupa password)*
4. [Login aman, 2FA, dan /admin noindex](#uji-4) *(v72)*
5. [B3a. Pengaturan Situs (v47, halaman tunggal, K45)](#uji-5) 
6. [B3b. Media Library (v53, K51)](#uji-6) 
7. [B3c. Pilar & Brand](#uji-7) 
8. [B3d. Berita & Kategori](#uji-8) 
9. [B3e. Beranda (v50, halaman tunggal, K48)](#uji-9) 
10. [B3f. Tentang Kami (v51, halaman tunggal, K49)](#uji-10) 
11. [B3g. Proyek](#uji-11) 
12. [B3h. Klien & Mitra](#uji-12) 
13. [B3i. Penghargaan & Sertifikat](#uji-13) 
14. [B3j. Keberlanjutan (v42, halaman tunggal, K40)](#uji-14) 
15. [B3k. Karir (v43, halaman tunggal, K41)](#uji-15) 
16. [B3l. Kontak & Pesan Masuk (v46, K44)](#uji-16) 
17. [B3m. Kebijakan Privasi (admin)](#uji-17) 
18. [B3m. Halaman Kustom](#uji-18) 
19. [B3m. Riwayat revisi Halaman Kustom](#uji-19) 
20. [B3n. Layar Redirect, sitemap.xml, robots.txt](#uji-20) 
21. [B3n. Redirect otomatis saat slug berubah](#uji-21) *(bagian 2a)*
22. [B3n. SEO per halaman (menu SEO Halaman)](#uji-22) *(bagian 2b)*
23. [B3n. SEO Pilar dan Brand](#uji-23) *(bagian 2c)*
24. [B3o. Menu (v48, halaman tunggal, K46; Pengguna & Role sudah selesai di BA3)](#uji-24) 
25. [B3p. Pengaturan Sistem (v49, halaman tunggal, K47)](#uji-25) 
26. [B4. Pencarian situs dari database](#uji-26) 
27. [B5. Terjemahan otomatis Penghargaan dan Proyek](#uji-27) *(v76)*
28. [B5. Terjemahan otomatis Halaman Kustom](#uji-28) *(v75)*
29. [B5. Terjemahan otomatis Brand](#uji-29) *(v98, terjemahan otomatis Brand)*
30. [B5. Terjemahan massal berita (translate:news)](#uji-30) *(v97)*
31. [B5. Label "Diterjemahkan otomatis" dan fallback ke ID](#uji-31) 
32. [B6. Impor berita WordPress](#uji-32) 
33. [Title dan meta description unik per halaman, Open Graph, Twitter Card](#uji-33) *(v91)*
34. [Heading terstruktur (satu H1), breadcrumb](#uji-34) *(v89)*
35. [`hreflang` ID/EN, canonical, sitemap XML, `robots.txt`](#uji-35) *(v64)*
36. [JSON-LD: `Organization`, `LocalBusiness` (kantor pusat + cabang), `BreadcrumbList`, `Article`](#uji-36) 
37. [C. Gambar responsif (srcset)](#uji-37) *(v90)*
38. [C. Gambar tanpa layout shift (width/height)](#uji-38) 
39. [C. Turnstile formulir Kontak (opsional)](#uji-39) *(v99)*
40. [C. Header keamanan (CSP, HSTS, dll)](#uji-40) 
41. [C. Validasi input dan batas unggahan](#uji-41) *(v68)*
42. [C. Backup database dan media](#uji-42) 
43. [C. Pembatas laju (rate limiting)](#uji-43) *(v69)*
44. [C. Aksesibilitas](#uji-44) *(v70)*
45. [D. Thumbnail Beranda dan pemeriksa tag Blade](#uji-45) *(v100)*
46. [D. Pemeriksa halaman dan tautan (site:links)](#uji-46) *(v92)*
47. [D. Panduan Admin (versi terbaru)](#uji-47) *(v95)*
48. [D. Panduan Admin (uji dengan calon Editor)](#uji-48) *(v71)*
49. [D. Checklist pra-live (site:check)](#uji-49) *(v70)*
50. [E. Monitoring uptime (/health)](#uji-50) *(v96)*


---

## TAHAP B: BACK-OFFICE & ADMIN (SETELAH ACC)

<a id="uji-1"></a>
### 1. Tampilan dasar admin: login, dashboard, ganti password
*Bagian todolist: TAHAP B-AWAL: Login, Dashboard, dan CRUD Pengguna Admin (dikerjakan lebih dulu)*

- [ ] `npm run dev`/`build`, buka `/admin/login` dan `/admin`; cek lebar 360, 768, 1024, 1440 px; sidebar HP (buka/tutup); menu pengguna; kedua halaman Ganti Password (wajib dan sukarela). Bila kelas Tailwind admin tidak bergaya, kirim `tailwind.config.cjs` (pastikan `content` memuat `./resources/views/**/*.blade.php`)

<a id="uji-2"></a>
### 2. Pengguna dan Log Aktivitas
*Bagian todolist: TAHAP B-AWAL: Login, Dashboard, dan CRUD Pengguna Admin (dikerjakan lebih dulu)*

- [ ] `/admin/pengguna` tampil dan menu Pengguna/Log Aktivitas aktif untuk Super Admin
- [ ] tambah pengguna Editor, masuk dengan akun itu, wajib ganti password, `/admin/pengguna` memberi 403
- [ ] nonaktifkan lalu coba masuk (ditolak)
- [ ] coba nonaktifkan/hapus diri sendiri dan Super Admin terakhir (ditolak dengan pesan)
- [ ] reset password pengguna lalu masuk dengan password sementara
- [ ] Log Aktivitas dan dashboard menampilkan semua kejadian tadi
- [ ] lebar 360, 768, 1024, 1440 px

<a id="uji-3"></a>
### 3. Lupa password lewat email (v73, lupa password)
*Bagian todolist: B2. Admin: Fondasi*

- [ ] halaman masuk punya tautan **Lupa password?**
- [ ] isi email akun aktif: jawaban hijau yang sama dengan bila email dikarang-karang
- [ ] buka `storage/logs/laravel.log`, salin tautan `/admin/atur-ulang-password/...`, buka: formulir password baru dengan email terisi
- [ ] simpan password baru: kembali ke Masuk dengan pesan sukses; password lama ditolak, baru diterima; akun ber-2FA tetap diminta kode
- [ ] buka tautan yang sama lagi: ditolak (sekali pakai); tunggu >60 menit atau minta tautan baru: tautan lama mati
- [ ] akun **nonaktif**: jawaban sama tetapi tidak ada email di log
- [ ] kirim 6 permintaan cepat: dibatasi (halaman 429)
- [ ] password lemah (<10, tanpa angka) ditolak
- [ ] Log Aktivitas (kelompok Password): `password.reset_requested` dan `password.reset`
- [ ] di staging dengan SMTP asli: email sampai dan tidak masuk spam (SPF/DKIM)

<a id="uji-4"></a>
### 4. Login aman, 2FA, dan /admin noindex (v72)
*Bagian todolist: B2. Admin: Fondasi*

- [ ] setelah rilis terpasang, buka halaman admin mana pun sebagai Super Admin: diarahkan ke **Aktifkan Verifikasi 2 Langkah** (tanpa sidebar), tombol Keluar tetap ada
- [ ] Mulai Penyiapan: kunci tampil berkelompok 4 huruf; tambahkan ke aplikasi autentikator (**Masukkan kunci penyiapan**, jenis berbasis waktu) lalu masukkan kode: aktif, **8 kode pemulihan tampil sekali**; muat ulang: kode tidak tampil lagi
- [ ] Keluar lalu Masuk: setelah password muncul layar **Verifikasi 2 Langkah**; kode benar masuk ke Dashboard, kode salah ditolak, kode yang sama dipakai ulang ditolak ('baru saja dipakai'), 5 kode salah mengunci sementara
- [ ] masuk memakai **kode pemulihan** (huruf kecil dan tanpa tanda hubung juga diterima): berhasil, pesan sisa kode, kode itu tidak bisa dipakai lagi
- [ ] Keamanan Akun: **Buat Kode Baru** butuh password, kode lama tak berlaku; tombol nonaktifkan **tidak ada** untuk Super Admin/Admin
- [ ] buat pengguna **Editor**: tidak dipaksa 2FA, boleh mengaktifkan dan menonaktifkan sendiri (butuh password)
- [ ] pengguna Admin baru: masuk, **ganti password sementara dulu**, lalu diarahkan menyiapkan 2FA
- [ ] Super Admin: Pengguna > ubah Admin tadi > **Hapus Verifikasi 2 Langkah** (kartu baru di bawah Atur Ulang Password); daftar Pengguna menampilkan lencana **2FA Aktif/Belum 2FA**; Admin itu diminta menyiapkan ulang
- [ ] Log Aktivitas: filter **Verifikasi 2 Langkah** memuat kejadian di atas, dan 'masuk ... (dengan verifikasi 2 langkah)'
- [ ] `php artisan admin:reset-2fa email` menghapus 2FA akun itu; `php artisan site:check` melaporkan akun tanpa 2FA
- [ ] jam ponsel salah: kode ditolak (jelaskan ke pengguna: aktifkan jam otomatis)
- [ ] lebar 360, 768, 1024 px untuk layar penyiapan dan tantangan

<a id="uji-5"></a>
### 5. B3a. Pengaturan Situs (v47, halaman tunggal, K45)
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu Pengaturan Situs aktif untuk Super Admin dan Admin, tidak muncul untuk Editor (URL langsung memberi 403)
- [ ] sebelum menyimpan: form terisi 3 kantor contoh, lencana "Data contoh", situs tidak berubah
- [ ] isi telepon dan WhatsApp (mis. `0812-3456-7890`), simpan: footer dan `/id/kontak` memakai nomor itu, tombol WhatsApp mengarah ke `wa.me/6281234567890`; nomor berisi huruf ditolak
- [ ] isi URL Instagram dan YouTube (`https://...`): ikon footer dan Kontak mengarah ke sana, ikon lain tetap `#`; URL tanpa `https://` ditolak
- [ ] ubah slogan ID, kosongkan slogan EN: `/en` memakai slogan ID; isi tab EN lalu cek lagi
- [ ] tambah kantor keempat, ubah urutan dengan panah (kantor pertama menjadi kartu besar di Profil Perusahaan dan Kontak), hapus satu kantor; kosongkan semua kantor: ditolak; kantor ketujuh tidak bisa ditambah
- [ ] tab EN: kosongkan jenis/alamat EN, cek `/en/contact` (memakai teks ID)
- [ ] item dashboard "Telepon, WhatsApp, dan 6 URL sosial media" tercentang setelah telepon dan WhatsApp terisi
- [ ] Kembalikan Ke Data Contoh: situs kembali memakai data contoh
- [ ] Log Aktivitas mencatat grup **Pengaturan Situs**
- [ ] lebar 360, 768, 1024, 1440 px

<a id="uji-6"></a>
### 6. B3b. Media Library (v53, K51)
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu Media Library aktif untuk Super Admin dan Admin, tidak muncul untuk Editor (URL `/admin/media` langsung 403; tetapi pemilih di formulir tetap bekerja untuk Editor)
- [ ] sebelum migrate: layar memberi petunjuk migrate, tidak error
- [ ] setelah `media:sync`: gambar logo klien, brand, dan unggahan lama muncul di kisi, berlabel Bawaan untuk `public/img`
- [ ] unggah 3 gambar (satu PNG besar) dengan dan tanpa alt: tampil di kisi dengan thumbnail, gambar tanpa alt bertitik kuning, filter Alt teks: Belum diisi menyaringnya
- [ ] unggah logo PNG berlatar kosong dengan saklar Ini logo: ruang kosong terpangkas; coba berkas > 5 MB, bukan gambar, dan > 20 berkas (ditolak dengan pesan)
- [ ] detail gambar: ubah nama dan alt, simpan, muat ulang; tombol Salin alamat menampilkan toast
- [ ] **unggah gambar baru lewat Berita atau Penghargaan**: otomatis muncul di Media Library
- [ ] pakai gambar itu sebagai gambar utama berita, buka detailnya: daftar Dipakai Di memuat berita itu; tombol Hapus nonaktif, dan menghapusnya ditolak; lepas dari berita lalu hapus: berhasil, berkas dan thumbnail hilang dari `storage/app/public`
- [ ] gambar yang disisipkan di isi berita (editor) juga terdeteksi sebagai dipakai
- [ ] berkas bawaan: tidak ada tombol Hapus, ada catatan
- [ ] Tentang Kami > Chairman Message: Pilih dari Media Library membuka jendela, cari, pilih, atau Unggah gambar baru (langsung terpilih); simpan; foto tampil di `/id/tentang-kami/profil-perusahaan`; Lepas gambar + simpan kembali ke foto bawaan
- [ ] validasi: path berisi `..` atau luar `storage/img` ditolak
- [ ] Log Aktivitas mencatat grup Media Library
- [ ] lebar 360, 768, 1024, 1440 px

<a id="uji-7"></a>
### 7. B3c. Pilar & Brand
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu Pilar & Brand aktif untuk Super Admin dan Admin, tidak muncul untuk Editor (URL langsung memberi 403)
- [ ] sebelum impor, situs publik tetap menampilkan pilar dari kode; setelah impor, daftar admin berisi 8 pilar + 20 brand dan situs publik tidak berubah
- [ ] seret pilar dan brand ke urutan baru (toast "Urutan disimpan"), muat ulang: urutan bertahan dan menu/footer/halaman Pilar Bisnis mengikuti; coba juga tombol panah
- [ ] sembunyikan brand dan pilar lewat ikon mata: hilang dari situs (menu, footer, pencarian, halaman), halaman langsung memberi 404
- [ ] tambah pilar baru lalu brand di dalamnya (hanya nama) dan cek `/id/pilar-bisnis/...`
- [ ] ubah brand: deskripsi ID saja (EN menampilkan teks ID), lalu isi EN
- [ ] unggah logo PNG berlatar kosong besar: terpangkas rapi, tersimpan WebP di `storage/app/public/brands`; coba file > 5 MB dan file bukan gambar (ditolak)
- [ ] pindahkan brand ke pilar lain
- [ ] isi tautan `http://...`: tersimpan `https://...`
- [ ] Training & Research Centre: butir Fokus & Layanan tampil di profil brand; tambah, urut, hapus butir
- [ ] slug kembar ditolak
- [ ] hapus brand dan pilar sebagai Super Admin; pilar yang masih punya brand ditolak, Admin tidak melihat tombol hapus (URL hapus memberi 403)
- [ ] Log Aktivitas mencatat semuanya
- [ ] dashboard: item brand tercentang setelah semua brand Dummy disimpan dan semua punya logo
- [ ] lebar 360, 768, 1024, 1440 px

<a id="uji-8"></a>
### 8. B3d. Berita & Kategori
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu Berita dan Kategori Berita aktif
- [ ] daftar menampilkan 9 berita dummy berlabel Dummy
- [ ] tambah berita ID saja (EN kosong) lalu cek `/id/berita/...` dan `/en/news/...` (EN menampilkan teks ID)
- [ ] isi tab EN dan cek lagi
- [ ] editor: subjudul, daftar, tautan, tempel dari Word, sisip gambar
- [ ] unggah gambar utama, cek WebP di `storage/app/public/news`
- [ ] Terjadwal dengan waktu 2 menit lagi: belum tampil, lalu tampil sendiri
- [ ] ubah dan simpan dua kali, coba Kembalikan di Riwayat Revisi
- [ ] kategori: tambah, ubah, coba hapus yang masih dipakai (ditolak), slug `kategori` ditolak
- [ ] masuk sebagai Editor: bisa tulis, tidak ada tombol hapus dan menu Kategori, URL hapus memberi 403
- [ ] Log Aktivitas mencatat semuanya
- [ ] lebar 360, 768, 1024, 1440 px

<a id="uji-9"></a>
### 9. B3e. Beranda (v50, halaman tunggal, K48)
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu Beranda aktif untuk Super Admin dan Admin, tidak muncul untuk Editor (URL langsung memberi 403)
- [ ] **sebelum menyimpan**: form terisi teks contoh, lencana "Teks contoh", 8 section berurutan seperti Beranda sekarang, dan **Beranda publik tampil persis seperti sebelum v50** (cek `/id` dan `/en`: hero, tentang, pilar, proyek, klien, nilai, keberlanjutan, penghargaan, berita, ajakan)
- [ ] ubah judul hero dan teks Tentang, simpan: `/id` berubah; tab EN dikosongkan sebagian, cek `/en` (memakai teks ID)
- [ ] kosongkan label kecil hero: label hilang dari hero
- [ ] tambah nilai sampai 8 (tombol tambah hilang di batas), hapus, urutkan dengan panah; kosongkan semua nilai atau semua poin Keberlanjutan: ditolak dengan pesan
- [ ] pindahkan Berita ke atas Pilar Bisnis, sembunyikan Klien & Mitra, simpan: Beranda mengikuti, dan `/id/klien-mitra` tetap terbuka; sembunyikan semua 8 section: Beranda hanya hero + ajakan (tidak error)
- [ ] ubah teks ajakan: ikut berubah di halaman dalam (mis. `/id/berita`); ubah teks Pilar Bisnis: ikut berubah di Profil Perusahaan
- [ ] Kembalikan Ke Teks Contoh: teks dan urutan kembali bawaan
- [ ] hapus tabel `page_contents` atau jangan migrate: Beranda tetap tampil dari teks contoh, layar admin memberi petunjuk migrate
- [ ] log aktivitas mencatat "mengubah isi halaman Beranda"; checklist dashboard "Teks Beranda" tercentang setelah pertama kali disimpan
- [ ] lebar 360, 768, 1024, 1440 px (daftar section dan repeater tetap rapi di HP)

<a id="uji-10"></a>
### 10. B3f. Tentang Kami (v51, halaman tunggal, K49)
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu Tentang Kami aktif untuk Super Admin dan Admin, tidak muncul untuk Editor (URL langsung memberi 403)
- [ ] **sebelum menyimpan**: form terisi teks contoh, lencana "Teks contoh", dan tiga halaman publik (`/id/tentang-kami/profil-perusahaan`, `/visi-misi`, `/perjalanan-kami`) tampil persis seperti sebelum v51 (8 peristiwa 2019-2026, 5 misi, 5 nilai)
- [ ] ubah nama Chairman dan satu paragraf pesan, simpan: halaman Profil berubah, foto tetap
- [ ] ubah satu misi dan tambah misi baru (maks. 8, tombol tambah hilang di batas), hapus satu, urutkan dengan panah: urutan di halaman Visi & Misi mengikuti
- [ ] tab EN: kosongkan sebagian isian, cek versi EN (memakai teks ID)
- [ ] tambah peristiwa tahun 2027: muncul di akhir lini waktu + tombol "Loncat ke tahun", fotonya placeholder; hapus semua peristiwa atau misi: ditolak dengan pesan
- [ ] kosongkan nama Chairman atau isian ID wajib lain: ditolak, tab bahasa berpindah ke yang bermasalah
- [ ] Kembalikan Ke Teks Contoh: situs kembali seperti awal
- [ ] dashboard: item Chairman Message dan Misi tercentang sesuai (lihat K49)
- [ ] lebar 360, 768, 1024, 1440 px

<a id="uji-11"></a>
### 11. B3g. Proyek
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu Proyek aktif
- [ ] daftar berisi 9 proyek berlabel Dummy, 3 di antaranya Unggulan
- [ ] `/id/portofolio` dan `/en/portfolio` tampil seperti sebelum migrasi (filter pilar dan tahun tetap jalan)
- [ ] tambah proyek baru: pilih pilar lalu daftar brand ikut berubah, isi ID saja, cek `/en/portfolio/{slug}` (EN menampilkan teks ID)
- [ ] centang unggulan di proyek lain lalu cek beranda (maks. 3 kartu)
- [ ] sembunyikan satu proyek, hilang dari Portofolio, halaman pilar, dan pencarian
- [ ] login sebagai Editor: bisa tambah/ubah, tidak ada tombol hapus
- [ ] hapus proyek contoh bila sudah diganti proyek asli, kartu dashboard ikut tercentang

<a id="uji-12"></a>
### 12. B3h. Klien & Mitra
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu Klien & Mitra dan Kategori Klien aktif untuk Super Admin dan Admin, tidak muncul untuk Editor (URL langsung memberi 403)
- [ ] sebelum impor, `/id/klien-mitra` dan beranda tetap menampilkan logo bawaan; setelah impor tampil sama seperti sebelumnya (7 kategori, 90 logo, strip beranda 24 logo)
- [ ] seret logo dan pakai tombol panah, muat ulang: urutan bertahan di halaman publik
- [ ] ikon mata: logo hilang dari halaman Klien & Mitra dan beranda; kategori yang semua logonya tersembunyi hilang dari tombol filter
- [ ] ikon rumah: centang/lepas lalu cek strip beranda (dengan nol centang, beranda memakai logo terdepan)
- [ ] Unggah Banyak Logo: pilih kategori + 3 berkas PNG berlatar kosong besar, hasil terpangkas rapi dan tersimpan WebP di `storage/app/public/clients`; coba berkas > 5 MB, bukan gambar, dan lebih dari 20 berkas (ditolak dengan pesan)
- [ ] Tambah Logo satu per satu dengan dan tanpa nama; Ubah: ganti logo, pindah kategori (jadi urutan terakhir di kategori baru)
- [ ] filter kategori/status/izin dan cari nama (urut dinonaktifkan saat filter aktif)
- [ ] kategori: tambah, ubah nama EN, urutan, slug `all` ditolak, hapus kategori yang masih berisi klien ditolak
- [ ] hapus klien unggahan: berkas di `storage/` ikut terhapus, logo bawaan tetap ada
- [ ] Log Aktivitas mencatat semuanya
- [ ] lebar 360, 768, 1024, 1440 px

<a id="uji-13"></a>
### 13. B3i. Penghargaan & Sertifikat
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu Penghargaan aktif untuk semua peran (Editor juga)
- [ ] sebelum impor, `/id/portofolio/penghargaan` tampil kosong dan carousel beranda hilang (tabel sudah ada); setelah impor tampil sama seperti sebelumnya (1 sorotan + 5 kartu, filter tahun jalan)
- [ ] tambah penghargaan baru dengan ID saja, cek `/en/portfolio/awards` (EN menampilkan teks ID), lalu isi tab EN
- [ ] unggah foto/piagam: terkecilkan, tersimpan WebP di `storage/app/public/awards`; coba berkas > 5 MB dan bukan gambar (ditolak)
- [ ] ikon piala: jadikan penghargaan lain sorotan, yang lama otomatis terlepas, halaman publik mengikuti; lepas semua sorotan: situs memakai penghargaan pertama
- [ ] sembunyikan satu penghargaan: hilang dari halaman Penghargaan dan carousel beranda
- [ ] ubah foto lalu simpan: berkas lama di `storage/` terhapus
- [ ] login sebagai Editor: bisa tambah/ubah, tidak ada tombol hapus (URL hapus memberi 403)
- [ ] Log Aktivitas mencatat semuanya
- [ ] hapus 6 penghargaan contoh bila sudah diganti yang asli: kartu dashboard tercentang
- [ ] lebar 360, 768, 1024, 1440 px

<a id="uji-14"></a>
### 14. B3j. Keberlanjutan (v42, halaman tunggal, K40)
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu Keberlanjutan aktif untuk Super Admin dan Admin, tidak muncul untuk Editor (URL langsung memberi 403)
- [ ] sebelum menyimpan, form terisi teks contoh dan lencana "Teks contoh"; situs tidak berubah
- [ ] ubah paragraf, judul, dan satu program, simpan, cek `/id/keberlanjutan`
- [ ] tab EN: kosongkan satu isian EN, cek `/en/sustainability` (isian itu memakai teks ID), lalu isi
- [ ] tambah butir Fokus sampai 6 dan Program sampai 8 (tombol tambah hilang di batas), hapus dan urutkan dengan panah, cek urutan di situs
- [ ] kosongkan semua teks satu butir lalu simpan: butir itu dibuang; kosongkan seluruh Fokus atau Program: ditolak dengan pesan
- [ ] galat di isian ID/EN membuka tab yang bermasalah
- [ ] Kembalikan Ke Teks Contoh: situs memakai teks contoh lagi
- [ ] Log Aktivitas mencatat simpan dan kembalikan; dashboard: item Program Komunitas tercentang setelah disimpan
- [ ] lebar 360, 768, 1024, 1440 px

<a id="uji-15"></a>
### 15. B3k. Karir (v43, halaman tunggal, K41)
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu Karir aktif untuk Super Admin dan Admin, tidak muncul untuk Editor (403)
- [ ] sebelum menyimpan, form terisi teks contoh dan situs tidak berubah
- [ ] ubah judul, satu alasan, dan satu langkah, simpan, cek `/id/karir`
- [ ] tab EN: kosongkan sebagian isian EN, cek `/en/careers` (memakai teks ID)
- [ ] tambah butir sampai 6 (tombol tambah hilang), hapus, urutkan dengan panah; kosongkan semua alasan atau langkah: ditolak dengan pesan
- [ ] isi Email tujuan CV: tombol Kirim CV dan alamat di bagian gelap memakai email itu; email tidak valid ditolak; kosongkan: kembali ke alamat bawaan
- [ ] daftar bidang di halaman Karir mengikuti brand di Pilar & Brand (sembunyikan satu brand, hilang dari daftar)
- [ ] Kembalikan Ke Teks Contoh
- [ ] Log Aktivitas mencatat; dashboard: item Karir tercentang setelah disimpan
- [ ] lebar 360, 768, 1024, 1440 px

<a id="uji-16"></a>
### 16. B3l. Kontak & Pesan Masuk (v46, K44)
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] sebelum migrate: `/id/kontak` tetap tampil, kirim formulir memberi pesan galat + tautan email, menu Pesan Masuk memberi petunjuk migrate
- [ ] setelah migrate: kirim pesan lengkap di `/id/kontak`: layar sukses "Pesan Anda terkirim", pesan muncul di `/admin/pesan` (titik biru), kartu dashboard Pesan Baru naik
- [ ] email: `info@decaa.id` menerima notifikasi (Reply-To = pengirim) dan pengirim menerima balasan otomatis; bila SMTP belum benar, pesan tetap masuk dan detailnya menampilkan "Notifikasi email: Gagal"
- [ ] validasi: kosongkan nama/email/pesan, email salah, pesan > 1000 karakter, telepon berisi huruf (ditolak)
- [ ] tab EN `/en/contact`: teks sukses dan balasan otomatis berbahasa Inggris
- [ ] honeypot: isi kolom `website` lewat DevTools lalu kirim: tampil sukses tetapi **tidak ada** pesan baru
- [ ] batas kirim: kirim 7 kali berturut-turut, yang ke-7 menampilkan pesan terlalu banyak percobaan
- [ ] buka detail: otomatis dibaca (titik hilang, dashboard turun); Tandai Belum Dibaca; Balas Lewat Email membuka aplikasi email dengan alamat, subjek `Re:`, dan kutipan
- [ ] cari nama/email/isi, filter status dan subjek, Atur Ulang
- [ ] Ekspor CSV terbuka rapi di Excel (karakter Indonesia benar, filter ikut)
- [ ] hapus pesan lewat modal; Log Aktivitas mencatat hapus dan ekspor
- [ ] Editor tidak melihat menu Pesan Masuk (URL langsung memberi 403)
- [ ] `php artisan messages:prune --months=1` menghapus pesan lebih tua dari 1 bulan (uji dengan pesan lama)
- [ ] lebar 360, 768, 1024, 1440 px

<a id="uji-17"></a>
### 17. B3m. Kebijakan Privasi (admin)
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu Halaman Legal aktif untuk Super Admin dan Admin, tidak untuk Editor (403)
- [ ] sebelum menyimpan: peringatan draf dan lencana "Draf contoh", situs tidak berubah
- [ ] ubah satu bagian (dua paragraf dipisah baris baru), tanggal, dan pengantar, simpan, cek `/id/kebijakan-privasi`: paragraf terpisah, daftar isi mengikuti judul, tanggal benar
- [ ] tab EN: kosongkan sebagian, cek versi EN (memakai teks ID)
- [ ] tambah bagian sampai 15, hapus, urutkan dengan panah; kosongkan semua bagian: ditolak; tanggal masa depan ditolak
- [ ] Kembalikan Ke Draf Contoh
- [ ] Log Aktivitas mencatat; dashboard: item Kebijakan Privasi tercentang setelah disimpan
- [ ] lebar 360, 768, 1024, 1440 px

<a id="uji-18"></a>
### 18. B3m. Halaman Kustom
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu Halaman Kustom aktif untuk Super Admin dan Admin, tidak muncul untuk Editor (URL langsung memberi 403)
- [ ] sebelum migrate, layar daftar menampilkan petunjuk migrate dan situs publik tidak error
- [ ] tambah halaman "Syarat & Ketentuan" (judul ID saja, isi dengan subjudul + daftar + tautan), status **Draf**: `/id/syarat-ketentuan` memberi 404
- [ ] ubah ke **Terbit** (tombol ikon daya di daftar atau lewat form): halaman tampil dengan hero, remah roti, dan isi rapi; `/en/syarat-ketentuan` menampilkan teks ID; isi tab EN lalu cek lagi, tombol ID | EN saling menuju
- [ ] centang **Tampilkan di footer** + urutan: tautan muncul di footer kolom Perusahaan (ID dan EN), lepas centang: hilang; Draf tidak muncul di footer walau dicentang
- [ ] slug bentrok ditolak: `karir`, `berita`, `kebijakan-privasi`, `admin`, dan slug yang sudah dipakai halaman kustom lain; slug kosong dibuat dari judul dan dijamin unik; huruf besar/spasi ditolak dengan pesan
- [ ] isi kosong atau hanya spasi ditolak; sisipkan gambar lewat editor (maks. 5 MB); tempel dari Word jadi teks polos
- [ ] SEO: judul dan deskripsi SEO tampil di `<title>` dan `<meta name="description">`; kosong memakai judul halaman
- [ ] ubah slug halaman Terbit: pesan peringatan alamat lama tidak berlaku
- [ ] hapus halaman: alamatnya jadi 404 dan tautan footer hilang
- [ ] Log Aktivitas mencatat tambah, ubah, terbit/draf, hapus (jenis "Halaman Kustom")
- [ ] lebar 360, 768, 1024, 1440 px

<a id="uji-19"></a>
### 19. B3m. Riwayat revisi Halaman Kustom
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] buka satu Halaman Kustom, panel Riwayat Revisi berisi "Belum ada revisi"
- [ ] ubah judul dan isi, simpan: muncul satu revisi berisi **judul lama**, dengan waktu dan namamu
- [ ] simpan ulang tiga kali dengan isi berbeda: tampil terbaru dulu, paling banyak 20
- [ ] Kembalikan ke versi lama: halaman berisi judul, isi, dan SEO lama, **slug, status, dan footer tidak berubah**, dan versi sebelum pengembalian muncul sebagai revisi baru (bisa dikembalikan lagi)
- [ ] ubah slug lalu kembalikan versi lama: alamat tetap slug terbaru, redirect lama tetap jalan
- [ ] Editor tidak bisa membuka layar (403) dan URL `POST .../revisi/{id}` memakai halaman lain memberi 404
- [ ] hapus halaman: tidak ada galat, Log Aktivitas mencatat pembuatan, perubahan, dan pengembalian
- [ ] sebelum `migrate` (opsional): menyimpan halaman tetap berhasil tanpa galat, hanya belum ada riwayat yang tercatat

<a id="uji-20"></a>
### 20. B3n. Layar Redirect, sitemap.xml, robots.txt
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu SEO & Redirect aktif untuk Super Admin dan Admin, tidak muncul untuk Editor (URL langsung memberi 403)
- [ ] sebelum migrate: layar memberi petunjuk migrate, situs publik tidak error, `/sitemap.xml` tetap tampil
- [ ] tambah redirect `/lama-tes` ke `/id/berita` (301): buka `/lama-tes` di jendela baru, langsung pindah dan hitungan "Dipakai" naik; alamat lama berhuruf besar, berakhiran `/`, atau berisi `?a=1` tetap cocok
- [ ] coba tambah asal `/` , `/admin/x`, atau tujuan sama dengan asal: ditolak dengan pesan; asal yang sudah ada: ditolak
- [ ] nonaktifkan: `/lama-tes` kembali 404; aktifkan lagi; hapus lewat modal
- [ ] alamat lama yang sebenarnya masih ada halamannya (mis. `/id/karir`) **tidak** dialihkan
- [ ] Impor Banyak: tempel 3 baris (`/a -> /id/berita`, `/b, https://decaa.id`, satu baris rusak): laporan "2 baru, 0 diperbarui, 1 dilewati" + alasan; tempel ulang: "diperbarui"
- [ ] `/sitemap.xml`: XML valid, tiap halaman muncul ID dan EN dengan hreflang, ada berita, proyek, brand, halaman kustom terbit, **tanpa** Pencarian, draf, dan item tersembunyi
- [ ] `/robots.txt`: dengan `SITE_NOINDEX=false` berisi Disallow `/admin` + baris Sitemap; dengan `true` berisi `Disallow: /` dan sitemap kosong, dan sumber halaman memuat `<meta name="robots" content="noindex, nofollow">`
- [ ] setelah impor WordPress: `redirects:import-wp --dry-run` melaporkan jumlah, lalu coba URL lama `/{slug-wp}` dan `/{tahun}/{bulan}/{slug-wp}` dari berita yang ada

<a id="uji-21"></a>
### 21. B3n. Redirect otomatis saat slug berubah (bagian 2a)
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] ubah slug satu berita Terbit (mis. `a` menjadi `b`): pesan sukses menyebut redirect otomatis; di SEO & Redirect ada dua aturan (`/id/berita/a` ke `/id/berita/b` dan `/en/news/a` ke `/en/news/b`, 301, catatan "Otomatis: slug berita berubah"); buka alamat lama: langsung pindah dan hitungan Dipakai naik
- [ ] ubah lagi `b` menjadi `c`: aturan lama `a` kini menuju `c` (tidak berantai), ada aturan baru `b` ke `c`
- [ ] kembalikan `c` menjadi `a`: tidak ada putaran (aturan otomatis dari `a` dihapus), `b` dan `c` menuju `a`
- [ ] berita Draf: ubah slug, tidak ada redirect baru
- [ ] Proyek tampil, Halaman Kustom terbit: sama seperti berita
- [ ] Pilar: ubah slug pilar yang punya 2 brand: aturan untuk pilar dan tiap brand (ID dan EN)
- [ ] Brand: ubah slug dan pindahkan ke pilar lain: alamat lama `/id/pilar-bisnis/{pilar-lama}/{brand-lama}` mengarah ke alamat baru
- [ ] aturan buatan sendiri dengan asal yang sama tidak ditimpa
- [ ] nonaktifkan atau hapus aturan otomatis di layar Redirect: alamat lama kembali 404

<a id="uji-22"></a>
### 22. B3n. SEO per halaman (menu SEO Halaman) (bagian 2b)
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu SEO Halaman aktif untuk Super Admin dan Admin, tidak muncul untuk Editor (URL langsung 403)
- [ ] sebelum migrate: layar memberi petunjuk, situs publik tidak error
- [ ] isi judul dan deskripsi Beranda ID, simpan; buka sumber halaman `/id`: `<title>`, `<meta name="description">`, `og:title`, `og:description`, `twitter:title` memuat isian itu; `/en` (EN kosong) memuat teks ID; isi EN: `/en` memakai EN
- [ ] halaman lain: judul diberi akhiran ` | DECAA.ID` sekali saja (bila kamu sudah menulis DECAA.ID di judul, tidak dobel)
- [ ] judul dengan tanda `&` atau kutip tampil benar di tab browser dan sumber (`&amp;` satu kali, bukan `&amp;amp;`), juga di halaman yang tidak kamu atur (mis. Klien & Mitra)
- [ ] pilih **gambar share bawaan** lewat Media Library: semua halaman publik punya `og:image` dan `twitter:image` berisi alamat lengkap (https), `twitter:card` berisi `summary_large_image`; kosongkan: tag `og:image` hilang dan card menjadi `summary`
- [ ] pilih gambar khusus untuk satu halaman: halaman itu memakai gambarnya, halaman lain tetap bawaan
- [ ] buka sebuah **berita** yang punya gambar utama: `og:image` = gambar berita itu; berita tanpa gambar atau berkas hilang memakai bawaan situs; sama untuk detail **proyek**
- [ ] berita dengan Judul SEO/Deskripsi SEO sendiri tetap memakainya (tidak ditimpa SEO Halaman)
- [ ] hitungan karakter berubah saat mengetik dan berwarna kuning di atas 60 (judul) atau 160 (deskripsi)
- [ ] panel halaman: yang sudah terisi bertanda Diatur dan terbuka otomatis; tautan Lihat halaman membuka halaman terkait; tombol Kosongkan Semua Isian mengembalikan semuanya ke bawaan
- [ ] bagikan satu tautan ke WhatsApp/Facebook Sharing Debugger untuk melihat kartu share (cache bisa beberapa jam)
- [ ] Log Aktivitas mencatat grup SEO Halaman

<a id="uji-23"></a>
### 23. B3n. SEO Pilar dan Brand (bagian 2c)
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] sebelum migrate: Ubah Pilar dan Ubah Brand tetap bisa disimpan tanpa error (kolom SEO belum tersimpan)
- [ ] setelah migrate: di Pilar & Brand > Ubah Pilar ada kartu SEO (judul + deskripsi, ID dan EN); isi, simpan, muat ulang: nilai bertahan
- [ ] buka `/id/pilar-bisnis/{slug}`: `<title>` = judul SEO + ` | DECAA.ID`, `<meta name="description">` = deskripsi SEO; `og:title` dan `twitter:title` ikut; di `/en/business-pillars/{slug}` EN kosong memakai ID, EN diisi memakai EN
- [ ] kosongkan kolom SEO pilar itu dan simpan: judul kembali "Nama | Pilar Bisnis | DECAA.ID" dan deskripsi ringkasan brand seperti semula
- [ ] sama untuk **Brand**: `/id/pilar-bisnis/{pilar}/{brand}` memakai judul/deskripsi SEO brand; kosong = "Nama brand | Pilar | DECAA.ID" dan deskripsi dari deskripsi brand (maks. 160 karakter)
- [ ] tanda `&` dan kutip di judul SEO tampil benar (satu kali escape)
- [ ] pilar atau brand dari data contoh kode (sebelum impor) tetap tampil tanpa error
- [ ] pindah pilar sebuah brand, ubah slug: redirect otomatis (v55) tetap bekerja

<a id="uji-24"></a>
### 24. B3o. Menu (v48, halaman tunggal, K46; Pengguna & Role sudah selesai di BA3)
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu Menu aktif untuk Super Admin dan Admin, tidak muncul untuk Editor (403)
- [ ] sebelum menyimpan: 7 item sesuai urutan header, lencana "Menu bawaan"
- [ ] ubah urutan (mis. Berita di atas Portofolio), simpan: header desktop, menu HP, dan footer mengikuti
- [ ] ubah label ID "Berita" menjadi "Kabar": hanya ID berubah, `/en` tetap "News"; isi tab EN lalu cek lagi; kosongkan label: kembali ke bawaan
- [ ] sembunyikan Karir: hilang dari header, footer, dan hasil pencarian halaman, tetapi `/id/karir` tetap terbuka
- [ ] sembunyikan Tentang Kami: tab di halaman Tentang Kami tetap tampil normal
- [ ] sembunyikan semua item: ditolak
- [ ] anak menu Pilar Bisnis dan Portofolio tetap tampil dan mengikuti Pilar & Brand
- [ ] Kembalikan Ke Menu Bawaan
- [ ] Log Aktivitas mencatat grup **Menu**
- [ ] lebar 360, 768, 1024, 1440 px

<a id="uji-25"></a>
### 25. B3p. Pengaturan Sistem (v49, halaman tunggal, K47)
*Bagian todolist: B3. Admin: Modul Konten (urutan usulan, mengikuti QA3)*

- [ ] menu Pengaturan Sistem aktif untuk Super Admin saja (Admin dan Editor: tidak muncul, URL langsung 403)
- [ ] nyalakan pemeliharaan **tanpa** kata rahasia: ditolak dengan pesan; klik Buat Otomatis, simpan
- [ ] buka situs di jendela incognito: halaman 503 (ID dan EN), `/admin/masuk` tetap terbuka
- [ ] buka tautan rahasia di incognito: diarahkan ke beranda dan situs tampil normal; tutup cookie: 503 lagi
- [ ] di jendela yang sudah login admin situs tampil normal dan semua layar admin menampilkan banner merah
- [ ] ganti kata rahasia: tautan lama tidak lagi membuka (cookie lama ditolak)
- [ ] matikan pemeliharaan: situs normal untuk semua, banner hilang
- [ ] analitik: pilih GA4, isi ID salah (`ABC`): ditolak; isi `G-ABC123XYZ`; di `APP_ENV=local` skrip tidak ada di sumber halaman, di production ada; coba Plausible (`decaa.id`) dan Umami (UUID)
- [ ] verifikasi: isi nilai content Google, lihat `<meta name="google-site-verification">` di sumber halaman publik; tag lengkap (`<meta ...>`) ditolak
- [ ] Log Aktivitas mencatat grup **Pengaturan Sistem** ("menyalakan/mematikan mode pemeliharaan")
- [ ] lebar 360, 768, 1024, 1440 px

<a id="uji-26"></a>
### 26. B4. Pencarian situs dari database
*Bagian todolist: B4. Menghubungkan Frontend ke Database*

- [ ] cari kata yang hanya ada di **isi** satu berita (bukan judul): berita itu muncul di grup Berita dengan cuplikan di sekitar kata
- [ ] cari judul sebuah Halaman Kustom terbit dan sepotong isinya: muncul di grup Halaman; halaman draf tidak muncul
- [ ] cari kata umum (mis. "decaa"): grup Berita paling banyak 20 hasil
- [ ] cari di `/en/search`: berita yang EN-nya kosong tetap ketemu lewat kata Indonesia, berita yang EN-nya terisi ketemu lewat kata Inggris
- [ ] cari `%` atau `_`: tidak error, tidak mencocokkan semuanya; satu huruf: pesan minimal 2 huruf
- [ ] berita tersembunyi/draf/terjadwal belum waktunya tidak muncul
- [ ] sebelum `php artisan migrate` pada instalasi baru: pencarian tidak error (memakai data contoh)
- [ ] kecepatan pencarian dengan 171+ berita terasa cepat

<a id="uji-27"></a>
### 27. B5. Terjemahan otomatis Penghargaan dan Proyek (v76)
*Bagian todolist: B5. Terjemahan Otomatis (lihat Bagian 4A)*

- [ ] Proyek baru: isi hanya ID (judul, ringkasan, deskripsi, lingkup tiga baris), saklar aktif, simpan: tab EN terisi, lingkup EN **tiga butir** sejajar, status Otomatis (belum ditinjau)
- [ ] `/en/portfolio/{slug}`: catatan "translated automatically ..." di bawah judul Overview
- [ ] ubah status ke Ditinjau: catatan hilang
- [ ] ubah hanya lingkup ID pada proyek berstatus Otomatis lalu simpan: lingkup EN ikut diterjemahkan ulang, judul EN tidak berubah
- [ ] Penghargaan baru hanya ID: EN terisi; `/en/portfolio/awards` menampilkan satu catatan di atas; Ditinjau: catatan hilang
- [ ] tulis EN sendiri lalu simpan dengan saklar aktif: tidak ditimpa
- [ ] penyedia dimatikan: form menampilkan peringatan kuning, simpan tetap berhasil
- [ ] sebelum `migrate`: simpan tetap berhasil tanpa saklar
- [ ] data lama (sebelum v76): tanpa catatan di situs

<a id="uji-28"></a>
### 28. B5. Terjemahan otomatis Halaman Kustom (v75)
*Bagian todolist: B5. Terjemahan Otomatis (lihat Bagian 4A)*

- [ ] Halaman Kustom baru: isi hanya ID, saklar aktif, simpan: tab EN terisi (judul, isi), status menjadi Otomatis (belum ditinjau), pesan sukses menyebut terjemahan
- [ ] buka `/en/{slug}`: catatan "translated automatically ... not been reviewed yet" tampil di atas isi
- [ ] ubah status ke Ditinjau lalu simpan dengan saklar mati: catatan hilang
- [ ] tulis EN dan ID sendiri, saklar aktif: tidak ada yang ditimpa
- [ ] halaman hanya EN (ID kosong): ID terisi mesin, `/id/{slug}` bertuliskan catatan versi Indonesia
- [ ] matikan penyedia di `.env`: form menampilkan peringatan kuning, simpan tetap berhasil
- [ ] sebelum `migrate`: simpan halaman tetap berhasil, form tanpa saklar/status
- [ ] halaman lama (sebelum v75): status Manual, tanpa catatan di situs

<a id="uji-29"></a>
### 29. B5. Terjemahan otomatis Brand (v98, terjemahan otomatis Brand)
*Bagian todolist: B5. Terjemahan Otomatis (lihat Bagian 4A)*

- [ ] `php artisan migrate` (kolom `brands.translated_from`), lalu `php artisan optimize:clear`
- [ ] buka Ubah Brand yang deskripsinya hanya ID: saklar **Terjemahkan otomatis** tampil aktif (bila penyedia belum diatur muncul kotak kuning)
- [ ] simpan: pesan 'Terjemahan Inggris diisi otomatis (status: belum ditinjau)'; buka form lagi: tab EN terisi deskripsi, judul + butir Layanan, SEO; status Otomatis
- [ ] buka halaman brand di `/en/...`: catatan 'translated automatically ... not been reviewed yet' tampil di atas deskripsi; di `/id/...` catatan tidak tampil
- [ ] ubah status ke **Ditinjau**, simpan: catatan hilang
- [ ] tulis sendiri satu butir Layanan EN lalu hapus EN butir lain dan simpan: yang kamu tulis tidak ditimpa, yang kosong terisi
- [ ] matikan saklar: tidak ada terjemahan, tab EN kosong jatuh ke teks Indonesia di situs
- [ ] brand tanpa deskripsi (7 brand Commercial Technology): simpan tidak error dan tidak memanggil penyedia untuk deskripsi kosong
- [ ] sebelum migrate: form Brand tetap bisa disimpan (saklar tidak muncul)

<a id="uji-30"></a>
### 30. B5. Terjemahan massal berita (translate:news) (v97)
*Bagian todolist: B5. Terjemahan Otomatis (lihat Bagian 4A)*

- [ ] `php artisan translate:news --dry-run`: muncul jumlah calon (berita impor WordPress) dan perkiraan karakter; tidak ada panggilan ke penyedia
- [ ] tanpa kunci penyedia: perintah **tanpa** `--dry-run` menolak dengan pesan jelas
- [ ] `php artisan translate:news --limit=3`: tiga berita terbaru terisi EN; buka salah satu di admin Berita: kolom EN terisi, status **Otomatis (belum ditinjau)**, tulisan ID tidak berubah; di `/en/news/{slug}` tampil catatan "belum ditinjau"
- [ ] jalankan lagi: ketiganya tidak diproses ulang (calon berkurang 3)
- [ ] ubah status satu berita menjadi **Ditinjau** lalu kosongkan salah satu kolom EN: perintah melewatinya, kecuali `--include-reviewed`
- [ ] kunci salah/kuota habis: berhenti setelah 3 galat beruntun dengan pesan, berita lain tidak rusak
- [ ] Log Aktivitas mencatat satu baris `news.translated_batch`
- [ ] setelah puas, jalankan bertahap `--limit=20` sampai sisa 0, lalu tinjau berita terpenting di admin

<a id="uji-31"></a>
### 31. B5. Label "Diterjemahkan otomatis" dan fallback ke ID
*Bagian todolist: B5. Terjemahan Otomatis (lihat Bagian 4A)*

- [ ] berita berstatus "Otomatis (belum ditinjau)" dibuka di `/en/news/...`: tampil catatan "translated automatically ... not been reviewed yet" di atas ringkasan
- [ ] ubah status jadi "Ditinjau" atau "Manual": catatan hilang
- [ ] berita yang EN-nya kosong (jatuh ke ID) **tidak** menampilkan catatan
- [ ] versi `/id/...` tidak menampilkan catatan untuk berita yang sumbernya ID

<a id="uji-32"></a>
### 32. B6. Impor berita WordPress
*Bagian todolist: B6. Migrasi dari WordPress*

- [ ] dry-run melaporkan 171 dibuat dan tidak ada gambar hilang (atau daftar gambar hilang dikirim ke aku)
- [ ] `/admin/berita` berisi 171 berita + 9 dummy
- [ ] buka beberapa berita di `/id/berita/...` dan `/en/news/...` (EN menampilkan teks ID)
- [ ] berita yang punya gambar di dalam isi (mis. artikel IAPT) tampil lengkap
- [ ] kartu berita menampilkan gambar unggulan
- [ ] hapus 9 berita dummy bila sudah tidak diperlukan


---

## TAHAP C: SEO, PERFORMA, KEAMANAN

<a id="uji-33"></a>
### 33. Title dan meta description unik per halaman, Open Graph, Twitter Card (v91)
*Bagian todolist: TAHAP C: SEO, PERFORMA, KEAMANAN*

- [ ] beranda `/id`: `<meta property="og:type" content="website">` dan `<meta property="og:locale:alternate" content="en_US">`; di `/en` alternatifnya `id_ID`
- [ ] detail Berita: `og:type` = `article` dan ada `article:published_time` berformat `2026-11-04`
- [ ] daftar Berita dan halaman lain: `og:type` tetap `website`, tanpa `article:published_time`
- [ ] isi Ringkasan/SEO deskripsi sebuah Berita dengan teks sangat panjang (> 300 karakter) atau berisi baris baru: `<meta name="description">`, `og:description`, dan `twitter:description` menjadi satu baris, maksimum 300 karakter, berakhir `...`
- [ ] deskripsi normal (< 160 karakter): tidak berubah
- [ ] halaman Pencarian dan 404: tetap `noindex`
- [ ] tempel salah satu alamat di Facebook Sharing Debugger / LinkedIn Post Inspector (butuh alamat publik/staging): judul, deskripsi, dan gambar muncul
- [ ] bandingkan judul semua halaman utama: tidak ada dua halaman dengan judul sama

<a id="uji-34"></a>
### 34. Heading terstruktur (satu H1), breadcrumb (v89)
*Bagian todolist: TAHAP C: SEO, PERFORMA, KEAMANAN*

- [ ] tulis Berita (atau Halaman Kustom) yang isinya **diawali Subjudul kecil (H3)** lalu Subjudul besar (H2), simpan: di halaman publik (lihat sumber) H3 pertama itu kini `<h2>` dan sesudahnya normal
- [ ] isi H2 lalu H3 lalu H3: tetap H2, H3, H3
- [ ] isi dengan hanya H3 (dua kali): yang pertama jadi H2, yang kedua tetap H3
- [ ] tempel dari Word yang memuat Heading 1: tetap jadi H2
- [ ] paragraf, daftar, tautan, dan gambar di dalam isi tidak berubah
- [ ] heading kosong tetap dibuang
- [ ] buka `/id`, `/id/tentang-kami/profil-perusahaan`, `/id/berita` dan satu detail berita dengan ekstensi pemeriksa heading (mis. HeadingsMap): satu H1, tanpa loncatan

<a id="uji-35"></a>
### 35. `hreflang` ID/EN, canonical, sitemap XML, `robots.txt` (v64)
*Bagian todolist: TAHAP C: SEO, PERFORMA, KEAMANAN*

- [ ] `/id/berita`: `<link rel="canonical">` = `https://.../id/berita` dan hreflang `id`, `en`, `x-default` tanpa query
- [ ] `/id/berita?utm_source=x`: canonical tetap tanpa `utm_source`
- [ ] `/id/berita?page=2` (bila ada halaman 2): canonical dan hreflang memuat `?page=2`
- [ ] `/id/search?q=decaa`: ada `<meta name="robots" content="noindex, follow">`, **tidak** ada canonical dan hreflang, dan tombol ID \| EN tetap membawa `?q=decaa`
- [ ] buka alamat ngawur (mis. `/id/tidak-ada`): halaman 404 dengan `noindex, follow` tanpa canonical/hreflang
- [ ] halaman biasa (beranda, Kontak): **tanpa** meta robots (kecuali staging: `noindex, nofollow`)
- [ ] detail berita, tab EN: canonical `/en/news/...` dan hreflang saling menunjuk

<a id="uji-36"></a>
### 36. JSON-LD: `Organization`, `LocalBusiness` (kantor pusat + cabang), `BreadcrumbList`, `Article`
*Bagian todolist: TAHAP C: SEO, PERFORMA, KEAMANAN*

- [ ] buka sumber halaman beranda (`/id`): ada satu `<script type="application/ld+json">` berisi Organization dan WebSite
- [ ] `/id/kontak` dan `/id/tentang-kami/profil-perusahaan`: ada satu LocalBusiness per kantor dengan alamat yang sama seperti di Pengaturan Situs
- [ ] halaman dalam (mis. `/id/berita`, `/id/portofolio`): BreadcrumbList Beranda > halaman; detail berita: Beranda > Berita > judul, plus Article dengan tanggal dan gambar; detail brand: Beranda > Pilar Bisnis > pilar > brand
- [ ] telepon dan tautan sosial yang masih contoh **tidak** muncul; setelah diisi asli di Pengaturan Situs muncul
- [ ] tempel kodenya ke validator (search.google.com/test/rich-results atau validator.schema.org) pada beberapa halaman: tanpa galat
- [ ] versi `/en/...`: nama, breadcrumb, dan `inLanguage` berbahasa Inggris
- [ ] sumber halaman 404 tetap rapi (hanya Organization)
- [ ] judul berita berisi tanda kutip, `<`, atau `&`: JSON tetap valid

<a id="uji-37"></a>
### 37. C. Gambar responsif (srcset) (v90)
*Bagian todolist: TAHAP C: SEO, PERFORMA, KEAMANAN*

- [ ] unggah foto besar (mis. 4000 x 3000 px) sebagai gambar Berita, buka daftar Berita lalu detailnya: di sumber halaman `<img>` kartu punya `srcset` berisi tiga entri (`.../variants/480/...webp 480w`, `.../variants/960/...webp 960w`, dan gambar asli `1920w`) dan `sizes`
- [ ] folder `storage/app/public/variants/480` dan `/960` terisi berkas WebP
- [ ] tab Network dengan lebar layar HP (mis. 390 px): yang dimuat varian 480 atau 960, bukan 1920; layar desktop lebar memuat varian lebih besar
- [ ] tampilan tidak berubah (rasio, potongan `object-cover`, `width`/`height` tetap)
- [ ] gambar kecil (lebar di bawah 600 px): tanpa `srcset`
- [ ] logo SVG, logo `img/logo`, gambar bawaan, dan hero bawaan: tanpa `srcset`
- [ ] hapus gambar itu dari Media Library: varian di folder `variants` ikut terhapus
- [ ] `php artisan media:sync`: tidak mendaftarkan isi `variants/` ke Media Library
- [ ] matikan izin tulis folder `variants` (uji): halaman tetap tampil normal tanpa `srcset`, tanpa galat
- [ ] Lighthouse mobile: peringatan "Properly size images" berkurang

<a id="uji-38"></a>
### 38. C. Gambar tanpa layout shift (width/height)
*Bagian todolist: TAHAP C: SEO, PERFORMA, KEAMANAN*

- [ ] inspeksi gambar berita, klien, dan brand di halaman publik: tag `<img>` kini punya `width` dan `height` sesuai ukuran asli file
- [ ] tampilan tidak berubah (gambar tetap mengikuti kelas Tailwind, tanpa gepeng)
- [ ] logo SVG tidak diberi atribut
- [ ] ganti satu gambar dengan ukuran berbeda: nilai ikut berubah
- [ ] Lighthouse/PageSpeed: skor CLS tidak turun

<a id="uji-39"></a>
### 39. C. Turnstile formulir Kontak (opsional) (v99)
*Bagian todolist: TAHAP C: SEO, PERFORMA, KEAMANAN*

- [ ] **tanpa** kunci (keadaan sekarang): halaman `/id/kontak` dan pengiriman pesan persis seperti sebelumnya, tanpa widget, dan CSP tidak menyebut Cloudflare
- [ ] dengan **kunci uji selalu-lolos** (site `1x00000000000000000000AA`, secret `1x0000000000000000000000000000000AA`): widget tampil di atas tombol Kirim, kirim pesan berhasil, token baru muncul setelah kirim
- [ ] kirim sebelum widget selesai atau dengan kunci uji **selalu-gagal** (`2x00000000000000000000AB` / `2x0000000000000000000000000000000AB`): pesan galat verifikasi tampil (ID dan EN), pesan **tidak** masuk kotak masuk admin
- [ ] tab Network (CSP mode lapor): tidak ada pelanggaran untuk `challenges.cloudflare.com`
- [ ] putuskan jaringan server ke Cloudflare (uji): pengiriman tetap berhasil dan ada peringatan Turnstile di `storage/logs/laravel.log`
- [ ] kunci produksi asli: ganti kunci uji, pastikan domain `new.decaa.id` dan `decaa.id` didaftarkan di dasbor Turnstile

<a id="uji-40"></a>
### 40. C. Header keamanan (CSP, HSTS, dll)
*Bagian todolist: TAHAP C: SEO, PERFORMA, KEAMANAN*

- [ ] `curl -I http://localhost/id` (atau tab Network): ada `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy`, `Permissions-Policy`, `Content-Security-Policy-Report-Only`
- [ ] tidak ada `Strict-Transport-Security` di http lokal; muncul di https bila `SECURITY_HSTS=true`
- [ ] buka beranda, Kontak, Peta, dan admin dengan konsol browser terbuka: catat pelanggaran CSP (pesan "Content Security Policy"); bila bersih, set `SECURITY_CSP=enforce` di staging dan uji ulang formulir, peta, analitik, editor berita, unggah media
- [ ] halaman tidak bisa dibingkai (iframe) dari domain lain

<a id="uji-41"></a>
### 41. C. Validasi input dan batas unggahan (v68)
*Bagian todolist: TAHAP C: SEO, PERFORMA, KEAMANAN*

- [ ] unggah foto biasa (mis. 4000 x 3000 px) di Berita, Proyek, Penghargaan, Klien, Brand, dan Media Library: tetap berhasil
- [ ] unggah gambar lebih dari 8000 piksel pada salah satu sisi (mis. 9000 x 3000): ditolak dengan pesan "Ukuran piksel ... terlalu besar ... (maksimal 8000 x 8000 piksel)" dan tidak ada berkas tersimpan
- [ ] berkas non-gambar atau SVG tetap ditolak dengan pesan format
- [ ] gambar lebih dari 5 MB tetap ditolak
- [ ] unggah banyak di Media Library dan Klien: berkas valid tetap masuk, yang terlalu besar ditolak dengan pesan per berkas
- [ ] cek `upload_max_filesize` dan `post_max_size` di PHP dan `client_max_body_size` di Nginx minimal 6M agar pesan berasal dari aplikasi, bukan galat server

<a id="uji-42"></a>
### 42. C. Backup database dan media
*Bagian todolist: TAHAP C: SEO, PERFORMA, KEAMANAN*

- [ ] `php artisan site:backup`: muncul `Database: db-....sql.gz` dan `Media: media-....zip` di `storage/app/backups`
- [ ] buka `.sql.gz` (`gunzip -c ... | head`): berisi `CREATE TABLE`
- [ ] isi zip media: `storage/app/public/...` dan `public/wp-uploads/...`
- [ ] jalankan sampai 3 kali dengan `--keep=2`: hanya 2 terbaru per jenis tersisa
- [ ] di Docker Sail bila `mysqldump` tidak ditemukan, pesan galat jelas (pasang `mysql-client`)
- [ ] produksi: cron `* * * * * php artisan schedule:run` sudah aktif (dibutuhkan juga oleh `messages:prune`)
- [ ] **uji pemulihan**: impor `.sql.gz` ke database kosong dan pastikan situs jalan

<a id="uji-43"></a>
### 43. C. Pembatas laju (rate limiting) (v69)
*Bagian todolist: TAHAP C: SEO, PERFORMA, KEAMANAN*

- [ ] `/id/cari?q=a`: muat ulang cepat lebih dari 30 kali dalam semenit: muncul halaman **429** berbahasa Indonesia (di `/en/search` berbahasa Inggris) dengan perkiraan detik tunggu, lalu normal lagi setelah itu
- [ ] form Kontak: kirim 7 kali dalam 10 menit: pesan batas tetap muncul di formulir (perilaku lama) dan **tidak terpengaruh** oleh pencarian atau sitemap
- [ ] `/sitemap.xml` dan `/robots.txt` normal untuk pemakaian biasa
- [ ] admin: ganti password dengan password lama salah 7 kali dalam semenit: 429 pada percobaan ke-7
- [ ] admin: urutkan klien/pilar/brand berulang lebih dari 10 kali lalu Ekspor Pesan: **tidak** 429 (hitungan terpisah)
- [ ] unggah banyak di Media Library, Klien, dan gambar di isi Berita tetap normal pada pemakaian wajar
- [ ] cek hasil temuan view `errors/` di K66: buka alamat acak `/id/xyz-tidak-ada`: halaman 404 rapi (bukan halaman polos Laravel)

<a id="uji-44"></a>
### 44. C. Aksesibilitas (v70)
*Bagian todolist: TAHAP C: SEO, PERFORMA, KEAMANAN*

- [ ] Tab dari awal halaman: muncul tautan lewati ke konten, Enter memindahkan fokus ke isi utama
- [ ] navigasi keyboard penuh di header, menu HP, overlay pencarian, form Kontak, dan filter Portofolio: fokus terlihat dan tidak terjebak
- [ ] pembaca layar (NVDA/VoiceOver) pada beranda dan satu halaman dalam: judul dan wilayah terbaca berurutan
- [ ] kontras teks putih di atas foto hero/banner (alat kontras di Chrome DevTools atau Lighthouse Accessibility)


---

## TAHAP D: QA & GO-LIVE

<a id="uji-45"></a>
### 45. D. Thumbnail Beranda dan pemeriksa tag Blade (v100)
*Bagian todolist: TAHAP D: QA & GO-LIVE*

- [ ] buka `/id` dan `/en`: bagian **Portofolio Unggulan** menampilkan **foto** di atas tiap kartu proyek (atau placeholder abu-biru bila proyek belum punya foto, bukan area kosong)
- [ ] arahkan kursor ke kartu: foto sedikit membesar seperti dulu
- [ ] sumber halaman: `<img ... sizes="(min-width: 768px) 33vw, 100vw">` ada di tiap kartu, dan tidak ada tulisan `<x-ui.img` mentah
- [ ] `php artisan site:check`: bagian **Tag komponen Blade** menampilkan OK
- [ ] bila foto masih kosong padahal tag sudah muncul: buka Admin > Proyek, pastikan proyek unggulan punya foto (kolom `image`) dan berkas ada di `storage/app/public`, lalu `php artisan storage:link`

<a id="uji-46"></a>
### 46. D. Pemeriksa halaman dan tautan (site:links) (v92)
*Bagian todolist: TAHAP D: QA & GO-LIVE*

- [ ] `php artisan site:links --limit=10`: sepuluh halaman pertama tercetak `ok` dengan status, ringkasan di akhir ("0 gagal")
- [ ] `php artisan site:links --locale=en --no-links`: hanya halaman EN, tanpa pemeriksaan tautan
- [ ] jalankan penuh `php artisan site:links` di Docker dan baca daftar GAGAL/PERINGATAN: perbaiki yang GAGAL (tautan rusak, halaman tanpa h1/title), catat PERINGATAN
- [ ] uji sengaja: buat Halaman Kustom Terbit yang memuat tautan ke `/id/tidak-ada` di isinya: dilaporkan **Tautan rusak (404)** lengkap dengan halaman sumber; hapus lalu ulangi, bersih lagi
- [ ] buat dua Halaman Kustom Terbit dengan judul sama: muncul **Judul kembar**
- [ ] nyalakan Mode Pemeliharaan lalu jalankan: halaman terbaca status 503 (GAGAL), matikan lagi
- [ ] kode keluar: `php artisan site:links; echo $?` bernilai `0` bila bersih dan `1` bila ada GAGAL
- [ ] tidak ada berkas baru bertambah di `storage/framework/sessions` setelah menjalankannya
- [ ] waktu jalan wajar (catat berapa menit untuk seluruh halaman)

<a id="uji-47"></a>
### 47. D. Panduan Admin (versi terbaru) (v95)
*Bagian todolist: TAHAP D: QA & GO-LIVE*

- [ ] baca bagian 1 (2FA, lupa password) sambil mencoba di Docker: nama tombol dan urutan layar sama dengan tulisan
- [ ] bagian 2 (terjemahan otomatis): saklar benar hanya tampil bila `TRANSLATE_DRIVER` diisi
- [ ] bagian Halaman Kustom, Proyek, Pengaturan Sistem: nama kartu dan kolom sesuai
- [ ] catat kalimat yang keliru lalu kirim, nanti kuperbaiki
- [ ] taruh berkas di `docs/PANDUAN-ADMIN.md` (timpa versi lama bila ada)

<a id="uji-48"></a>
### 48. D. Panduan Admin (uji dengan calon Editor) (v71)
*Bagian todolist: TAHAP D: QA & GO-LIVE*

- [ ] baca panduan sambil membuka admin: nama menu, tombol, dan urutan sesuai kenyataan (catat yang berbeda)
- [ ] minta satu orang non-teknis (calon Editor) menjalankan tiga tugas hanya dari panduan: tulis satu Berita sampai Terbit, ganti satu foto di Media Library, balas satu Pesan Masuk
- [ ] putuskan **2FA**: kerjakan sebelum live atau revisi K29
- [ ] putuskan apakah "lupa password" mandiri dibutuhkan atau cukup lewat Super Admin
- [ ] *(dijawab v74: riwayat revisi Halaman Kustom dibuat, K71)* putuskan apakah Halaman Kustom perlu riwayat revisi atau K32 diperbaiki

<a id="uji-49"></a>
### 49. D. Checklist pra-live (site:check) (v70)
*Bagian todolist: TAHAP D: QA & GO-LIVE*

- [ ] `site:check` di Docker lokal: daftar bagian PHP, Batas unggah, .env, Database, Storage, Jadwal, Cache tampil; tidak ada FAIL kecuali yang memang kamu abaikan (mis. `MAIL_MAILER=log` hanya WARN di lokal)
- [ ] `site:check --production` di lokal: muncul FAIL untuk `APP_ENV`, `APP_DEBUG`, `APP_URL`, dan email (ini benar)
- [ ] ubah `upload_max_filesize` ke 2M sementara: muncul FAIL batas unggah
- [ ] unggah foto besar (mis. 6000 x 4000) di Media Library dengan `memory_limit=128M`: berhasil (menjadi WebP) tanpa galat 500
- [ ] kode keluar: `php artisan site:check; echo $?` memberi 1 bila ada FAIL dan 0 bila tidak


---

## TAHAP E: PASCA LIVE

<a id="uji-50"></a>
### 50. E. Monitoring uptime (/health) (v96)
*Bagian todolist: TAHAP E: PASCA LIVE*

- [ ] buka `/health`: JSON `status ok`, `database true`, `storage true`, `maintenance false`, HTTP 200 (`curl -i https://.../health`)
- [ ] matikan MySQL sebentar atau salah ketik `DB_DATABASE` di staging: `/health` memberi 503 dan `"database":false` tanpa pesan galat rinci
- [ ] nyalakan mode pemeliharaan: `/health` tetap 200 dengan `"maintenance":true`, sedangkan halaman lain 503
- [ ] muat `/health` lebih dari 60 kali dalam semenit: halaman 429
- [ ] `/health` tidak masuk sitemap dan memiliki header `X-Robots-Tag: noindex`
- [ ] setelah live: buat monitor sesuai tabel di `PEMELIHARAAN-RUTIN.md` bagian 1 dan pastikan peringatan sampai ke dua orang


---

## Hasil uji

| Tanggal | Penguji | Lingkungan (lokal / staging / produksi) | Kelompok yang lulus | Butir gagal (nomor kelompok + butir) |
|---|---|---|---|---|
|  |  |  |  |  |

