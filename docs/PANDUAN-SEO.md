# Panduan SEO DECAA.ID: supaya situs muncul di Google

Dokumen ini menjawab: bagaimana memakai fitur SEO yang sudah ada agar situs benar-benar muncul dan tampil baik di hasil pencarian, dan apa yang masih perlu dilakukan.

## 1. Status singkat

**Bagian teknis sudah lengkap dan siap dipakai.** Situs sudah menghasilkan:

- judul dan deskripsi meta per halaman (bisa diubah di admin),
- alamat kanonis (`canonical`) dan padanan bahasa ID/EN (`hreflang`),
- `sitemap.xml` dan `robots.txt` dinamis,
- Open Graph dan Twitter Card (kartu saat tautan dibagikan),
- data terstruktur JSON-LD (Organization, WebSite, LocalBusiness, BreadcrumbList, NewsArticle),
- `noindex` otomatis untuk halaman Cari, 404, dan seluruh admin,
- redirect 301 (termasuk otomatis saat alamat berita/proyek/dll. berubah, dan dari alamat lama WordPress).

**Yang menentukan muncul tidaknya di Google bukan hanya kode.** Ada langkah di sisi kamu (bagian 2 sampai 4) dan butuh waktu (bagian 7). Tanpa langkah itu situs tetap bagus tetapi Google tidak tahu atau belum percaya.

## 2. Syarat produksi (wajib, satu kali)

Di `.env` server:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://decaa.id          # alamat final, https, tanpa garis miring di akhir
SITE_NOINDEX=false                # true = situs disembunyikan dari Google (hanya untuk staging)
```

Lalu:

```
php artisan optimize:clear
php artisan site:check --production
```

Semua baris harus lolos. Perhatikan khusus baris `SITE_NOINDEX=false di produksi`; kalau masih `true`, **situs tidak akan pernah muncul di Google**.

Cek dengan mata sendiri di browser:

1. `https://decaa.id/robots.txt` harus berisi `Disallow: /admin` dan baris `Sitemap: https://decaa.id/sitemap.xml` (bukan `Disallow: /` saja).
2. `https://decaa.id/sitemap.xml` harus berisi daftar alamat halaman (bukan kosong), semuanya berawalan `https://decaa.id`.
3. Buka salah satu halaman, klik kanan, Lihat Sumber Halaman: `<link rel="canonical">` harus berawalan sama persis dengan `APP_URL` (https, dan www hanya bila APP_URL memakainya) dan tidak ada `<meta name="robots" content="noindex">`.

### Satu alamat saja (www dan http)

Pilih satu bentuk alamat (misalnya `https://decaa.id`) lalu alihkan yang lain dengan 301 **di server atau DNS/CDN**:

- `http://decaa.id` dan `http://www.decaa.id` ke `https://decaa.id`
- `https://www.decaa.id` ke `https://decaa.id`

Mulai v107, alamat di dalam halaman (canonical, sitemap, hreflang, JSON-LD) otomatis dipaksa mengikuti `APP_URL` di produksi, jadi mesin pencari tidak melihat versi ganda walau server sempat bisa dibuka lewat dua alamat. Pengalihan 301 di server tetap dianjurkan supaya pengunjung juga selalu mendarat di satu alamat.

## 3. Daftarkan ke Google Search Console dan Bing

Ini langkah yang paling sering terlupa, dan paling berpengaruh.

1. Buka <https://search.google.com/search-console>, tambahkan properti **URL prefix** `https://decaa.id/`.
2. Pilih verifikasi **Tag HTML**. Google memberi tag `<meta name="google-site-verification" content="ABC123...">`.
3. Salin **hanya nilai `content`** (`ABC123...`), lalu di admin buka **Pengaturan Sistem** (khusus Super Admin) > **Verifikasi Mesin Pencari** > isi kolom **Google Search Console** > Simpan. Kembali ke Search Console, klik **Verifikasi**.
4. Menu **Sitemaps** > tambah `sitemap.xml` > Kirim. Status harus **Berhasil** dan jumlah alamat yang ditemukan sesuai perkiraan.
5. Menu **Pemeriksaan URL** > tempel `https://decaa.id/` > **Minta pengindeksan**. Ulangi untuk beberapa halaman penting (Tentang Kami, Pilar Bisnis, Kontak, dua atau tiga berita terbaru).
6. Ulangi dengan <https://www.bing.com/webmasters> (isi kolom **Bing Webmaster**). Bing juga dipakai asisten AI dan peramban tertentu.

## 4. Isi SEO di admin (di sinilah kualitas hasil pencarian ditentukan)

Tanpa diisi, situs memakai judul dan deskripsi bawaan yang aman tetapi umum. Isi yang penting dulu:

| Di mana | Apa yang diisi |
|---|---|
| **Situs > SEO Halaman** | Judul dan deskripsi untuk 13 halaman utama (Beranda, Profil, Visi & Misi, Perjalanan, Pilar Bisnis, Portofolio, Penghargaan, Klien & Mitra, Berita, Keberlanjutan, Karir, Kontak, Kebijakan Privasi), ID dan EN. Pilih juga **gambar share bawaan** (1200 x 630 px). |
| **Berita** (kartu SEO di form) | Judul SEO dan Deskripsi SEO bila berbeda dari judul/ringkasan. Pilih gambar utama yang jelas. |
| **Proyek**, **Pilar & Brand**, **Halaman Kustom** | Kartu SEO di form masing-masing. |
| **Media Library** | **Alt teks** semua gambar (titik kuning = belum diisi; filter Alt teks: Belum diisi). Alt membantu Google Images dan pembaca layar. |
| **Pengaturan Situs** | Alamat, telepon, email, dan media sosial yang **asli** (bukan placeholder). Data ini ikut ke JSON-LD, dan nomor/alamat contoh sengaja tidak dikeluarkan. |

### Cara menulis yang efektif

- **Judul**: sampai 60 karakter, kata kunci utama di depan, nama situs otomatis ditambahkan di belakang. Contoh: `Layanan Konsultasi Transformasi Digital`.
- **Deskripsi**: sampai 160 karakter, satu atau dua kalimat yang menjawab "kenapa klik halaman ini", tanpa menumpuk kata kunci.
- **Setiap halaman berbeda.** Judul dan deskripsi yang sama di banyak halaman membuat Google memilih sendiri.
- Hitungan karakter di form berwarna kuning bila melebihi batas ideal.
- Tulis versi **EN** untuk halaman penting; bila dikosongkan, pengunjung EN melihat teks Indonesia.

## 5. Alamat lama (WordPress) dan perubahan alamat

- Alamat berita WordPress lama dialihkan lewat `php artisan redirects:import-wp` (cek dulu dengan `--dry-run`), lalu tinjau di admin **SEO & Redirect**.
- Mengubah slug berita, proyek, pilar, brand, atau halaman kustom otomatis membuat redirect 301 dari alamat lama. Tidak perlu dibuat manual.
- Bila memindahkan situs dari `new.decaa.id` ke `decaa.id`: ikuti `docs/RENCANA-GO-LIVE-DAN-ROLLBACK.md`, pasang redirect 301 domain lama ke baru, dan di Search Console gunakan **Perubahan alamat** untuk properti lama.

## 6. Cara mengecek hasilnya

1. **Sumber halaman**: `<title>`, `meta description`, `canonical`, `hreflang`, `og:image` sesuai isian admin.
2. **Rich Results Test** (<https://search.google.com/test/rich-results>): tempel alamat Beranda, Kontak, dan satu berita; data Organization, LocalBusiness, dan Article harus terbaca tanpa galat.
3. **PageSpeed Insights** (<https://pagespeed.web.dev>): ukur versi mobile Beranda dan satu halaman dalam. Target LCP < 2,5 detik; kirim angkanya bila masih di bawah target, nanti saya perbaiki.
4. **Search Console**: laporan **Halaman** (indexing) menunjukkan yang terindeks dan alasan yang tidak; **Performa** menunjukkan kata kunci dan klik.
5. **Kartu share**: tempel alamat ke WhatsApp atau <https://developers.facebook.com/tools/debug/> untuk melihat judul dan gambar yang tampil. Cache bisa bertahan beberapa jam.
6. `php artisan site:links` memeriksa tautan internal dan sitemap; jalankan sebelum dan sesudah perubahan besar.

## 7. Harapan yang realistis

- Beranda dan halaman utama biasanya terindeks dalam beberapa hari sampai 2 minggu setelah sitemap dikirim.
- Pencarian nama **"DECAA.ID"** biasanya muncul lebih dulu. Kata kunci umum ("konsultan IT") butuh waktu berminggu-minggu sampai berbulan-bulan dan bergantung pada isi, tautan dari situs lain, dan persaingan.
- Mengetik `site:decaa.id` di Google menunjukkan halaman yang sudah terindeks; hasilnya bertambah bertahap.
- Yang mempercepat: menerbitkan berita/artikel yang bermanfaat secara rutin, membagikan tautan di media sosial, tautan dari situs mitra dan klien, dan melengkapi **Google Business Profile** untuk kantor (muncul di Google Maps dan pencarian lokal).

## 8. Masalah yang sering terjadi

| Gejala | Penyebab dan cara cek |
|---|---|
| Situs tidak muncul sama sekali, bahkan untuk nama sendiri | `SITE_NOINDEX=true` di `.env`, atau sitemap belum dikirim. Jalankan `php artisan site:check --production`. |
| Search Console: "Dikecualikan oleh tag noindex" | Sama, atau halaman itu memang Cari/404/admin (normal). |
| "Halaman dengan pengalihan" | Normal untuk alamat lama yang dialihkan. |
| Canonical berisi `http://` atau `www` | `APP_URL` salah/belum https, atau `php artisan optimize:clear` belum dijalankan setelah ubah `.env`. |
| "Ditemukan, saat ini belum diindeks" | Wajar di minggu-minggu awal. Tambah tautan internal/eksternal dan konten; jangan kirim ulang berulang-ulang. |
| Gambar share tidak berubah | Cache WhatsApp/Facebook; gunakan Sharing Debugger untuk menyegarkan. |

## 9. Yang belum sempurna (jujur)

- **Angka kinerja nyata** (Lighthouse, Core Web Vitals) baru bisa diukur di server live; ikuti `docs/PERFORMA-DAN-SERVER.md`.
- **Format gambar AVIF** belum ada (WebP dan `srcset` sudah).
- **Analitik** (Plausible/Umami/GA4) perlu dipasang agar kamu tahu halaman mana yang dicari orang; kolomnya ada di Pengaturan Sistem.
- **Pratinjau tampilan Google/share di admin** belum ada.
- **Gambar share per pilar/brand** belum bisa dipilih sendiri (memakai gambar share bawaan).
- Kualitas isi tetap tanggung jawab tim: judul, deskripsi, dan artikel yang ditulis tidak bisa dibuat otomatis oleh sistem.
