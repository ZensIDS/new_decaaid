# Panduan Admin DECAA.ID

Panduan singkat untuk tim yang mengelola isi website lewat panel admin. Ditulis untuk pengguna non-teknis. Alamat panel: **`/admin`** (mis. `https://new.decaa.id/admin`).

> Versi panduan: sesuai todolist v71. Bagian bertanda **(belum ada)** menjelaskan fitur yang direncanakan tetapi belum dibuat.

---

## 1. Masuk dan peran

**Masuk.** Buka `/admin`, isi email dan password. Setelah beberapa kali salah, akun dikunci sementara; tunggu hitungan mundur yang tampil lalu coba lagi. Pada **login pertama** (atau setelah password direset), sistem meminta Anda mengganti password. Password minimal **10 karakter** dan harus berisi **huruf dan angka**; disarankan lebih panjang (12 karakter atau lebih) dan unik, jangan dipakai di layanan lain.

**Lupa password.** Belum ada tombol "lupa password" **(belum ada)**. Minta **Super Admin** mengatur ulang lewat menu **Pengguna**. Anda akan diminta menggantinya lagi saat masuk.

**Verifikasi dua langkah (2FA).** Direncanakan wajib untuk Super Admin dan Admin, tetapi **belum aktif (belum ada)**. Sampai aktif, jaga password dengan ketat dan jangan dibagikan.

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

---

## 3. Dashboard

Halaman pertama setelah masuk. Menampilkan ringkasan dan **daftar periksa item contoh** yang belum diganti (telepon dan WhatsApp, foto, berita contoh, kebijakan privasi, dst.). Item tercentang otomatis setelah Anda menyimpan isinya.

---

## 4. Konten

### Beranda (`Beranda`)
Satu formulir. Mengatur teks hero, Tentang DECAA.ID, judul bagian Pilar Bisnis, Proyek, Klien, Berita, "Mengapa DECAA.ID" (1-8 butir), poin Keberlanjutan (1-6), dan ajakan Hubungi Kami. Kartu **Susunan Section** mengurutkan dan menyembunyikan 8 section tengah; hero tetap di atas dan ajakan tetap di bawah. Kartu proyek unggulan diatur di menu **Proyek** (centang unggulan), kartu brand di **Pilar & Brand**, dan logo klien di **Klien & Mitra**.

### Tentang Kami
Satu formulir: Profil Perusahaan, Chairman Message (nama, jabatan, pesan), Visi, Misi (1-8), Nilai-nilai (1-8), dan Perjalanan Kami (1-20 peristiwa dengan tahun, label, judul, cerita). Foto dipilih dari **Media Library** pada bagian yang menyediakannya.

### Pilar & Brand
Satu layar berisi 8 pilar beserta brand-nya.
- **Pilar:** nama, alamat (slug), ikon (16 pilihan), tampil/sembunyi, urutan.
- **Brand:** pilar induk (bisa dipindah), nama, logo, deskripsi ID/EN, Instagram, TikTok, tautan situs, butir "Fokus & Layanan Utama" (opsional), tampil/sembunyi.
- Logo diproses otomatis (ruang kosong dipangkas). Tautan `http://` otomatis menjadi `https://`.
- **Hapus hanya oleh Super Admin.** Pilar yang masih punya brand, dan pilar terakhir, tidak bisa dihapus.
- Brand bertanda **Dummy** berisi narasi buatan; ganti sebelum tayang.

### Proyek (portofolio)
Daftar dengan cari dan filter. Satu proyek = **satu pilar** (brand opsional). Wajib: **judul, klien, tahun**. Lainnya: ringkasan, deskripsi, lingkup pekerjaan (satu butir per baris, maks. 12), gambar utama, tampil/sembunyi, **unggulan**, SEO. Beranda menampilkan **3 proyek unggulan terbaru**; bila yang dicentang kurang dari 3, dilengkapi proyek terbaru. Urutan di situs: tahun terbaru dahulu.

### Klien & Mitra dan Kategori Klien
- **Kategori Klien** dikelola dahulu (nama ID/EN, urutan). Kategori yang masih berisi klien tidak bisa dihapus.
- **Klien** = logo + kategori (wajib); nama opsional (dipakai sebagai teks alternatif logo). Urutan per kategori dengan seret-dan-lepas. **Ikon rumah** = tampil di strip logo Beranda. **Izin tampil** hanyalah catatan internal (titik kuning = belum dikonfirmasi) dan tidak menyembunyikan logo.
- **Unggah banyak logo** sekaligus per kategori (maks. 20 berkas per unggahan).

### Penghargaan
Nama (ID/EN), keterangan (opsional, maks. 600 karakter), tahun (wajib), foto atau piagam, tampil/sembunyi. **Ikon piala** menandai penghargaan "terbaru" yang disorot: hanya satu; menandai yang baru melepas yang lama.

### Berita dan Kategori Berita
- **Status:** **Draf** (hanya terlihat admin), **Terbit**, atau **Terjadwal** (tampil otomatis saat waktunya tiba, tanpa tindakan lagi).
- Isi memakai editor teks (format teks, daftar, tautan, gambar). Isi disaring oleh server demi keamanan (tag berbahaya dibuang), jadi periksa tampilannya di situs setelah disimpan, terutama bila menempel teks dari Word atau situs lain.
- **Alamat (slug)** dibuat dari judul dan boleh diubah. Kata yang tidak boleh dipakai: `kategori`, `category`, `page`, `halaman`, `search`, `cari`, `feed`, `sitemap`. **Mengubah slug berita yang sudah terbit membuat redirect otomatis** dari alamat lama sehingga tautan lama tidak mati.
- **Riwayat Revisi** di bawah formulir menyimpan salinan sebelum tiap perubahan; berita dapat dikembalikan ke versi sebelumnya. (Halaman Kustom belum punya riwayat revisi **(belum ada)**.)
- **Terjemahan otomatis:** hanya bekerja bila penyedia terjemahan sudah dinyalakan oleh tim teknis (`TRANSLATE_DRIVER` di server; bawaan mati). Bila belum, isi kedua bahasa sendiri atau biarkan EN kosong.
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
Tambah pengguna (email, nama, peran), **nonaktifkan** akun (tidak bisa masuk, data tetap), dan **atur ulang password** (pengguna wajib menggantinya saat masuk). Berikan peran terkecil yang cukup: **Editor** untuk penulis berita, **Admin** untuk pengelola konten.

### Log Aktivitas
Catatan siapa melakukan apa dan kapan (tambah, ubah, hapus, masuk, dst.). Gunakan untuk menelusuri perubahan yang tidak dikenali.

### Pengaturan Sistem
- **Mode pemeliharaan:** saat menyala, pengunjung melihat halaman "sedang dalam pemeliharaan". Admin yang sudah login dan `/admin` tetap bisa masuk, begitu juga siapa pun yang membuka **tautan rahasia** (tombol **Buat Otomatis**, lalu **Salin Tautan**; berlaku 12 jam per perangkat). Banner merah tampil di semua layar admin selama menyala; **matikan setelah selesai**. Mengganti kata rahasia mencabut semua tautan lama.
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
| Lupa password | Minta Super Admin mengatur ulang lewat **Pengguna**. |
| Muncul halaman "Terlalu banyak permintaan" (429) | Terlalu sering mengirim dalam waktu singkat; tunggu sesuai hitungan lalu coba lagi. |
| Situs menampilkan "sedang dalam pemeliharaan" | Mode pemeliharaan menyala. Super Admin mematikannya di **Pengaturan Sistem** (masuk lewat `/admin`). |

**Bantuan teknis:** hubungi tim pengembang dan sertakan alamat halaman, waktu kejadian, dan tangkapan layar pesan galat.
