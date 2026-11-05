# Panduan Admin DECAA.ID

Panduan singkat untuk tim yang mengelola isi website lewat panel admin. Ditulis untuk pengguna non-teknis. Alamat panel: **`/admin`** (mis. `https://new.decaa.id/admin`).

> Versi panduan: sesuai todolist v95 (5 Nov 2026). Bila ada layar atau tombol yang berbeda dari tulisan ini, catat dan kabari tim pengembang agar panduan diperbarui.

---

## 1. Masuk dan peran

**Masuk.** Buka `/admin`, isi email dan password. Setelah beberapa kali salah, akun dikunci sementara; tunggu hitungan mundur yang tampil lalu coba lagi. Pada **login pertama** (atau setelah password direset), sistem meminta Anda mengganti password. Password minimal **10 karakter** dan harus berisi **huruf dan angka**; disarankan lebih panjang (12 karakter atau lebih) dan unik, jangan dipakai di layanan lain.

**Verifikasi dua langkah (2FA).** Wajib untuk **Super Admin dan Admin**, opsional untuk Editor. Memakai aplikasi autentikator di HP (Google Authenticator, Microsoft Authenticator, Authy, 1Password, Bitwarden).
- **Pertama kali:** setelah masuk, Anda otomatis diarahkan ke halaman **Aktifkan Verifikasi 2 Langkah** (menu lain terkunci sampai selesai). Di aplikasi autentikator pilih **tambah akun dengan kunci/kode manual**, masukkan **kunci penyiapan** yang tampil (atau buka tautan `otpauth://` di HP), lalu ketik kode 6 angka dari aplikasi untuk konfirmasi. Kode QR belum tersedia.
- **Simpan 8 kode pemulihan** yang tampil **hanya sekali** (salin atau unduh .txt dan simpan di tempat aman, bukan di email kantor). Tiap kode sekali pakai dan dipakai bila HP hilang.
- **Tiap masuk:** setelah password benar, masukkan kode 6 angka dari aplikasi (berganti tiap 30 detik) atau satu kode pemulihan. Kode yang sama tidak bisa dipakai dua kali; setelah beberapa kali salah, akun terkunci sementara.
- **Kelola:** menu pengguna > **Keamanan Akun**: buat ulang kode pemulihan (butuh password) dan, khusus Editor, nonaktifkan 2FA. Peran yang wajib 2FA tidak bisa menonaktifkannya.
- **HP hilang dan kode pemulihan habis:** minta Super Admin menghapus 2FA Anda (lihat bagian Pengguna), lalu aktifkan ulang di perangkat baru. Bila yang kehilangan akses adalah Super Admin terakhir, tim teknis dapat meresetnya lewat server (`php artisan admin:reset-2fa email`).

**Lupa password.** Di halaman masuk klik **Lupa password?**, isi email akun, lalu ikuti tautan yang dikirim ke email (berlaku **60 menit**, sekali pakai). Layar selalu menampilkan jawaban yang sama baik email terdaftar maupun tidak (demi keamanan), dan akun yang dinonaktifkan tidak dikirimi email. Bila email tidak datang dalam beberapa menit, cek folder spam, lalu minta **Super Admin** mengatur password sementara lewat menu **Pengguna**. Setelah password diganti, **2FA tetap diminta** saat masuk.

**Tiga peran:**

| Menu | Super Admin | Admin | Editor |
|---|:-:|:-:|:-:|
| Berita, Proyek, Penghargaan (tambah dan ubah) | ya | ya | ya |
| Hapus Berita, Proyek, Penghargaan | ya | ya | tidak |
| Beranda, Tentang Kami, Pilar & Brand, Klien & Mitra, Keberlanjutan, Karir, Halaman Legal/Kustom | ya | ya | tidak |
| Kategori Berita dan Kategori Klien | ya | ya | tidak |
| Pesan Masuk | ya | ya | tidak |
| Media Library, Menu, SEO Halaman, SEO & Redirect, Pengaturan Situs | ya | ya | tidak |
| Hapus Pilar dan Brand | ya | tidak | tidak |
| Pengguna, Log Aktivitas, Pengaturan Sistem | ya | tidak | tidak |

Menu yang tidak boleh Anda buka memang tidak tampil di sidebar.

---

## 2. Cara kerja yang sama di semua layar

- **Tab Indonesia (ID) | English (EN).** Hampir semua teks punya dua bahasa. Isi **ID** dahulu. **Bila EN dikosongkan, pengunjung berbahasa Inggris melihat teks Indonesia**, jadi situs tidak pernah kosong. Beberapa kolom bersifat satu bahasa (mis. nama orang, tahun, nama brand) dan tidak punya tab.
- **Simpan Perubahan.** Perubahan baru tampil di situs setelah menekan tombol simpan. Pesan hijau di pojok atas menandakan berhasil; pesan merah menandakan ada isian yang perlu diperbaiki (periksa juga tab bahasa lain).
- **Teks contoh (Dummy).** Banyak halaman awalnya berisi teks contoh. Lencana **Data contoh / Dummy** menandakan isi masih bawaan. Setelah Anda menyimpan, lencana berubah menjadi **Isi dari admin**. Tombol **Kembalikan Ke Data/Teks Contoh** menghapus isi yang tersimpan dan kembali ke contoh. Dashboard memuat **daftar periksa** item contoh yang masih tersisa.
- **Ikon mata** = tampil/sembunyikan. **Panah naik/turun** atau **seret-dan-lepas** = ubah urutan. **Ikon tempat sampah** = hapus (selalu ada konfirmasi dan tidak bisa dibatalkan).
- **Daftar berulang** (butir, langkah, nilai, program): tombol tambah, hapus, naik/turun. Ada batas jumlah per daftar; tombol tambah hilang saat batas tercapai.
- **Gambar.** Format JPG, PNG, atau WebP; maksimal **5 MB** dan sisi terpanjang **8000 piksel**. Sistem mengecilkan dan mengubahnya ke WebP otomatis. SVG tidak diterima. Isi **teks alternatif (alt)** bila bisa: membantu pembaca layar dan SEO (kolom alt kosong ditandai titik kuning).
- **Tombol "Lihat Di Situs"** membuka halaman publik di tab baru untuk memeriksa hasil.
- **Terjemahkan otomatis** (Berita, Halaman Kustom, Penghargaan, Proyek). Bila tim teknis sudah menyalakan penyedia terjemahan di server, formulir punya saklar **Terjemahkan otomatis bahasa yang kosong** dan pilihan **Status terjemahan**: **Manual** (Anda tulis sendiri), **Otomatis (belum ditinjau)**, atau **Ditinjau**. Saat disimpan, kolom yang **kosong** di satu bahasa diisi hasil mesin; tulisan Anda **tidak pernah ditimpa**. Teks berstatus Otomatis tampil di situs dengan catatan kecil "belum ditinjau"; ubah ke **Ditinjau** setelah Anda memeriksa hasilnya. Bila terjemahan ke Inggris gagal, isi tetap tersimpan dengan peringatan; bila ke Indonesia gagal, simpanan ditolak (ID wajib). Saklar tidak tampil bila penyedia belum dinyalakan.
- **Pratinjau SEO.** Kartu SEO di SEO Halaman, Berita, Proyek, dan Halaman Kustom menampilkan pratinjau hasil pencarian dan kartu share yang berubah saat Anda mengetik (per bahasa). Hanya perkiraan; Google dan aplikasi bebas memotong teks sendiri.

---

## 3. Dashboard

Halaman pertama setelah masuk. Menampilkan ringkasan dan **daftar periksa item contoh** yang belum diganti (telepon dan WhatsApp, foto, berita contoh, kebijakan privasi, dst.). Item tercentang otomatis setelah Anda menyimpan isinya.

---

## 4. Konten

### Beranda (`Beranda`)
Satu formulir. Mengatur teks hero, Tentang DECAA.ID, judul bagian Pilar Bisnis, Proyek, Klien, Berita, "Mengapa DECAA.ID" (1-8 butir), poin Keberlanjutan (1-6), dan ajakan Hubungi Kami. Kartu **Susunan Section** mengurutkan dan menyembunyikan 8 section tengah; hero tetap di atas dan ajakan tetap di bawah. Kartu **Foto Beranda** memuat 6 slot gambar (hero: utama, kegiatan, tim; Tentang DECAA.ID: 2; Keberlanjutan: kegiatan komunitas) yang dipilih dari **Media Library**; slot kosong memakai foto bawaan, jadi Beranda tidak pernah menampilkan gambar rusak. Kartu proyek unggulan diatur di menu **Proyek** (centang unggulan), kartu brand di **Pilar & Brand**, dan logo klien di **Klien & Mitra**.

### Tentang Kami
Satu formulir: Profil Perusahaan, Chairman Message (nama, jabatan, pesan), Visi, Misi (1-8), Nilai-nilai (1-8), dan Perjalanan Kami (1-20 peristiwa dengan tahun, label, judul, cerita). Kartu **Foto Halaman Profil** (foto perusahaan, kegiatan, kantor pusat) dan **Foto Perjalanan Kami** (satu foto per tahun) memakai **Media Library**; slot kosong memakai foto bawaan. Peristiwa baru di lini waktu mendapat pemilih foto **setelah disimpan**. Foto Chairman dipilih di kartu Chairman.

### Pilar & Brand
Satu layar berisi 8 pilar beserta brand-nya.
- **Pilar:** nama, alamat (slug), ikon (16 pilihan), tampil/sembunyi, urutan.
- **Brand:** pilar induk (bisa dipindah), nama, logo, deskripsi ID/EN, Instagram, TikTok, tautan situs, butir "Fokus & Layanan Utama" (opsional), tampil/sembunyi.
- Logo diproses otomatis (ruang kosong dipangkas). Tautan `http://` otomatis menjadi `https://`.
- **Hapus hanya oleh Super Admin.** Pilar yang masih punya brand, dan pilar terakhir, tidak bisa dihapus.
- Brand bertanda **Dummy** berisi narasi buatan; ganti sebelum tayang.

### Proyek (portofolio)
Daftar dengan cari dan filter. Satu proyek = **satu pilar** (brand opsional). Wajib: **judul, klien, tahun**. Lainnya: ringkasan, deskripsi, lingkup pekerjaan (satu butir per baris, maks. 12), gambar utama, tampil/sembunyi, **unggulan**, SEO. **Galeri Foto:** hingga **12 foto** dipilih sekaligus dari Media Library (atau unggah baru dari jendela pilih), diurutkan dengan panah; tampil di halaman detail proyek dengan lightbox (klik foto, panah kiri/kanan, Esc untuk menutup). Daftar kosong = tanpa galeri. Beranda menampilkan **3 proyek unggulan terbaru**; bila yang dicentang kurang dari 3, dilengkapi proyek terbaru. Urutan di situs: tahun terbaru dahulu.

### Klien & Mitra dan Kategori Klien
- **Kategori Klien** dikelola dahulu (nama ID/EN, urutan). Kategori yang masih berisi klien tidak bisa dihapus.
- **Klien** = logo + kategori (wajib); nama opsional (dipakai sebagai teks alternatif logo). Urutan per kategori dengan seret-dan-lepas. **Ikon rumah** = tampil di strip logo Beranda. **Izin tampil** hanyalah catatan internal (titik kuning = belum dikonfirmasi) dan tidak menyembunyikan logo.
- **Unggah banyak logo** sekaligus per kategori (maks. 20 berkas per unggahan).

### Penghargaan
Nama (ID/EN), keterangan (opsional, maks. 600 karakter), tahun (wajib), foto atau piagam, tampil/sembunyi. **Ikon piala** menandai penghargaan "terbaru" yang disorot: hanya satu; menandai yang baru melepas yang lama.

### Berita dan Kategori Berita
- **Status:** **Draf** (hanya terlihat admin), **Terbit**, atau **Terjadwal** (tampil otomatis saat waktunya tiba, tanpa tindakan lagi).
- Isi memakai editor teks (format teks, daftar, tautan, gambar). Isi disaring oleh server demi keamanan (tag berbahaya dibuang), jadi periksa tampilannya di situs setelah disimpan, terutama bila menempel teks dari Word atau situs lain.
- **Alamat (slug)** dibuat dari judul dan boleh diubah. Kata yang tidak boleh dipakai: `kategori`, `category`, `page`, `halaman`, `search`, `cari`, `feed`, `sitemap`. **Mengubah slug berita yang sudah terbit membuat redirect otomatis** dari alamat lama sehingga tautan lama tidak mati (berlaku juga untuk slug Kategori Berita, Proyek, dan Pilar).
- **Riwayat Revisi** di bawah formulir menyimpan salinan sebelum tiap perubahan; berita dapat dikembalikan ke versi sebelumnya. (Halaman Kustom punya riwayat serupa; lihat di bawah.)
- **Terjemahan otomatis:** lihat bagian 2; hanya bekerja bila penyedia terjemahan sudah dinyalakan oleh tim teknis. Bila belum, isi kedua bahasa sendiri atau biarkan EN kosong.
- Berita hasil impor WordPress (171 artikel) tidak memiliki versi EN; pengunjung EN melihat teks Indonesia.
- Editor tidak dapat menghapus berita.
- **Kategori Berita** dikelola penuh (nama ID/EN, slug). Slug kategori tidak boleh bentrok dengan halaman bawaan.

### Keberlanjutan, Karir, Halaman Legal
Masing-masing satu formulir berbahasa ID/EN.
- **Keberlanjutan:** Tentang (1-2 paragraf), Fokus (1-6), Program (1-8), ajakan.
- **Karir:** judul, pengantar, Mengapa Bergabung (1-6), Proses Seleksi (1-6), Kirim CV. Isi **Email tujuan CV** dengan `info@decaa.id` bila alamat karir terpisah tidak ada; kolom kosong memakai alamat bawaan situs.
- **Halaman Legal:** Kebijakan Privasi: pengantar, **tanggal pembaruan** (wajib, tidak boleh di masa depan), 1-15 bagian (judul dan teks; baris kosong = paragraf baru; daftar isi dibuat otomatis). **Teks bawaan hanya draf; wajib ditinjau pihak hukum sebelum tayang.**

### Halaman Kustom
Halaman tambahan buatan sendiri (mis. Syarat & Ketentuan). Isi: judul ID/EN, isi (editor teks yang sama dengan Berita), alamat (slug), status **Draf/Terbit**, centang **tampil di footer** (+ urutan), dan SEO. Satu slug untuk dua bahasa: `/id/{slug}` dan `/en/{slug}`. Slug tidak boleh sama dengan halaman bawaan (mis. `karir`, `berita`, `kontak`, `tentang-kami`) atau kata cadangan (`admin`, `storage`, `sitemap`, `search`). Halaman Draf memberi 404 ke pengunjung.
- **Kolom footer:** saat "tampil di footer" menyala, pilih kolom **Perusahaan** atau **Pilar Bisnis** (tautan tampil di bawah daftar pilar); urutan berlaku di dalam kolomnya.
- **Gambar share:** di kartu SEO pilih gambar (ideal 1200 x 630 piksel) yang muncul saat tautan dibagikan; kosong = gambar bawaan situs.
- **Riwayat Revisi:** tiap kali halaman disimpan atau dikembalikan, kondisi sebelumnya disalin (20 terakhir disimpan). Panel di bawah formulir menampilkan versi, waktu, dan pengubahnya dengan tombol **Kembalikan**. Yang dikembalikan hanya **judul, isi, dan SEO teks**; alamat (slug), status Draf/Terbit, dan pilihan footer tidak ikut. Versi sebelum pengembalian ikut tersimpan, jadi bisa dibatalkan. Halaman lama baru punya riwayat setelah disimpan ulang pertama kali; tombol Terbit/Draf di daftar tidak membuat revisi.

---

## 5. Pesan Masuk

Pesan dari formulir Kontak. Titik biru = belum dibaca; membuka pesan menandainya **dibaca** otomatis. Fitur: cari (nama, email, isi), filter status dan subjek, **Balas Lewat Email** (membuka aplikasi email dengan kutipan pesan), **Tandai Belum Dibaca**, **hapus**, dan **Ekspor CSV** (mengikuti filter). Tiap pesan menampilkan status **Notifikasi email: Terkirim/Gagal**: bila gagal, pesan tetap tersimpan di sini, jadi **periksa kotak masuk ini secara rutin**. Pesan disimpan 12 bulan lalu dihapus otomatis. Pengunjung otomatis menerima balasan "pesan diterima".

---

## 6. Situs

### Media Library
Pusat semua gambar. **Unggah banyak** (maks. 20 berkas, 5 MB); aktifkan saklar **Ini logo** untuk logo (ruang kosong dipangkas). Cari, filter sumber/folder/**alt kosong**. Halaman detail: ganti nama tampilan, isi alt ID/EN, salin alamat, dan lihat daftar **Dipakai di**. **Gambar yang masih dipakai tidak bisa dihapus** (sistem menolak dan menunjukkan di mana dipakai). Berkas bawaan dari kode tidak bisa dihapus dari sini. Banyak formulir (Berita, Proyek, Penghargaan, Klien, Brand, SEO, Pengaturan Situs) punya tombol **Pilih dari Media Library**.

### Menu
Mengatur 7 item menu utama: ubah **label** (ID/EN; kosong = label bawaan), **sembunyikan**, dan **urutkan**. Anak menu (Tentang Kami, Pilar Bisnis, Portofolio) otomatis. Minimal satu item tampil. Item yang disembunyikan hilang dari header, footer, dan daftar halaman di pencarian, tetapi **alamatnya tetap bisa dibuka**.

### SEO Halaman
Judul (ideal 60, maks. 70 karakter) dan deskripsi (ideal 160, maks. 200) untuk 13 halaman tetap, plus gambar yang muncul saat tautan dibagikan (WhatsApp, Facebook, LinkedIn), serta gambar bawaan situs. Bila EN kosong, memakai ID. Akhiran " | DECAA.ID" ditambahkan otomatis. SEO Berita, Proyek, Pilar, Brand, dan Halaman Kustom diisi di formulir masing-masing; bila kosong, sistem memakai isi item itu.

### SEO & Redirect
Redirect mengalihkan alamat lama ke alamat baru agar pengunjung dan Google tidak menemui halaman mati.
- Tambah manual: alamat lama (path seperti `/lama` atau URL lengkap) dan tujuan (`/id/...` atau `https://...`), jenis **301** (permanen, pilihan biasa) atau 302 (sementara).
- **Impor Banyak:** tempel hingga 500 baris berformat `alamat-lama -> alamat-baru`.
- Redirect hanya berjalan bila alamat lama **tidak ada** (tidak pernah menimpa halaman hidup). Daftar menampilkan jumlah pemakaian.
- Redirect berawalan **"Otomatis: ..."** dibuat sistem saat slug berubah; boleh dinonaktifkan atau dihapus.

### Pengaturan Situs
Slogan, **teks hak cipta footer** (tulis `{year}` untuk tahun otomatis), **logo terang, logo gelap, dan favicon** (dari Media Library), telepon, **nomor WhatsApp** (tautan dibuat otomatis), 5 sosial media (alamat diawali `https://`), dan **daftar kantor** (maks. 6; kantor pertama = pusat; tautan peta opsional). Kolom telepon, WhatsApp, dan sosmed yang dikosongkan memakai data contoh, jadi **isi semuanya sebelum tayang** (tombol di sosmed contoh mengarah ke `#`). Email umum tetap `info@decaa.id`.

---

## 7. Sistem (Super Admin)

### Pengguna
Tambah pengguna (email, nama, peran), **nonaktifkan** akun (tidak bisa masuk, data tetap), dan **atur ulang password** (pengguna wajib menggantinya saat masuk). Daftar menampilkan lencana **2FA Aktif** atau **Belum 2FA**. Bila pengguna kehilangan HP dan kode pemulihannya, buka **Pengguna > ubah > Hapus Verifikasi 2 Langkah**; pengguna itu akan diminta menyiapkan 2FA lagi saat masuk berikutnya (tindakan tercatat di Log Aktivitas). Berikan peran terkecil yang cukup: **Editor** untuk penulis berita, **Admin** untuk pengelola konten.

### Log Aktivitas
Catatan siapa melakukan apa dan kapan (tambah, ubah, hapus, masuk, dst.). Gunakan untuk menelusuri perubahan yang tidak dikenali.

### Pengaturan Sistem
- **Mode pemeliharaan:** saat menyala, pengunjung melihat halaman "sedang dalam pemeliharaan". Admin yang sudah login dan `/admin` tetap bisa masuk, begitu juga siapa pun yang membuka **tautan rahasia** (tombol **Buat Otomatis**, lalu **Salin Tautan**; berlaku 12 jam per perangkat). Banner merah tampil di semua layar admin selama menyala; **matikan setelah selesai**. Mengganti kata rahasia mencabut semua tautan lama. Pilihan tambahan:
  - **Judul dan pesan** halaman pemeliharaan (ID/EN; kosong = teks bawaan tiap bahasa; teks polos tanpa tautan).
  - **Pengecualian alamat IP:** satu IP per baris (maks. 20); IP terdaftar membuka situs tanpa login atau tautan rahasia. Cocok persis (tanpa rentang), dan IP bersama (mis. jaringan seluler) meloloskan semua penggunanya. Layar menampilkan IP Anda saat ini.
  - **Mulai otomatis / Selesai otomatis** (tanggal dan jam, zona Asia/Jakarta): pemeliharaan hanya aktif di dalam jendela waktu itu bila saklar menyala. Tanpa cron. Matikan saklar bila tidak ingin pemeliharaan menyala lagi pada jadwal berikutnya.
- **Analitik:** pilih Google Analytics 4, Plausible, atau Umami lalu isi ID. Hanya aktif di situs produksi.
- **Verifikasi mesin pencari:** tempel hanya nilai `content` dari tag meta Google Search Console atau Bing Webmaster (bukan tag lengkap).
- **Email pengirim** dan **noindex staging** diatur tim teknis di berkas `.env`, bukan di sini.

---

## 8. Tugas rutin yang disarankan

| Kapan | Apa |
|---|---|
| Harian/mingguan | Baca **Pesan Masuk**; balas atau tandai |
| Tiap ada kegiatan | Tulis **Berita** (gambar utama, ringkasan, kategori); gunakan **Draf** lalu **Terbit** |
| Bulanan | Perbarui **Proyek** dan **Penghargaan** baru; cek **Media Library** untuk alt kosong |
| Berkala | Tinjau **Kebijakan Privasi** (ubah tanggal pembaruan); cek **Log Aktivitas** |
| Sebelum tayang | Selesaikan **daftar periksa Dashboard**; isi **Pengaturan Situs**, **SEO Halaman**; tinjau hukum untuk Kebijakan Privasi |

Cadangan database dan media dibuat otomatis tiap malam oleh server; **salinannya perlu diunduh atau disinkronkan ke tempat lain** oleh tim teknis.

---

## 9. Pemecahan masalah

| Masalah | Penyebab dan solusi |
|---|---|
| Gambar ditolak | Pastikan JPG/PNG/WebP, di bawah 5 MB, sisi terpanjang di bawah 8000 piksel. Kecilkan dengan aplikasi foto lalu coba lagi. |
| "Alamat (slug) sudah dipakai" atau ditolak | Slug harus unik dan bukan kata cadangan (lihat bagian Berita dan Halaman Kustom). Tambahkan kata pembeda. |
| Perubahan belum tampil di situs | Muat ulang paksa (Ctrl+F5). Pastikan status **Terbit/Tampil** dan tombol simpan sudah ditekan. Berita **Terjadwal** tampil saat waktunya tiba. |
| Halaman hilang dari menu tetapi alamatnya bisa dibuka | Item disembunyikan di **Menu**. Tampilkan lagi lewat ikon mata. |
| Tidak bisa menghapus gambar | Gambar masih dipakai; lihat daftar **Dipakai di** pada halaman detail, ganti gambarnya dahulu. |
| Tidak bisa menghapus kategori atau pilar | Masih berisi item. Pindahkan atau hapus itemnya dahulu. |
| Pesan Kontak tidak ada notifikasi email | Lihat status **Notifikasi email** di Pesan Masuk; pesan tetap tersimpan. Laporkan ke tim teknis bila selalu Gagal. |
| Lupa password | Klik **Lupa password?** di halaman masuk dan ikuti email (berlaku 60 menit). Bila email tidak datang, minta Super Admin mengatur password sementara lewat **Pengguna**. |
| Kode 2FA ditolak | Pastikan jam HP otomatis (waktu keliru membuat kode salah), pakai kode terbaru (berganti tiap 30 detik) dan jangan memakai kode yang sama dua kali. Bila tetap gagal, pakai salah satu kode pemulihan. |
| HP hilang dan kode pemulihan habis | Minta Super Admin memilih **Hapus Verifikasi 2 Langkah** pada akun Anda di **Pengguna**, lalu siapkan 2FA lagi. |
| Muncul halaman "Terlalu banyak permintaan" (429) | Terlalu sering mengirim dalam waktu singkat; tunggu sesuai hitungan lalu coba lagi. |
| Situs menampilkan "sedang dalam pemeliharaan" | Mode pemeliharaan menyala. Super Admin mematikannya di **Pengaturan Sistem** (masuk lewat `/admin`). |

**Bantuan teknis:** hubungi tim pengembang dan sertakan alamat halaman, waktu kejadian, dan tangkapan layar pesan galat.
