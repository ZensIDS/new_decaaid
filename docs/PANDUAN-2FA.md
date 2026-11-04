# Panduan Verifikasi 2 Langkah (2FA) untuk Admin DECAA.ID

Bagian ini melengkapi `PANDUAN-ADMIN.md` (gabungkan ke bagian "Masuk dan peran" bila perlu).

## Apa itu dan mengapa wajib

Saat masuk, selain password Anda memasukkan **kode 6 digit** dari aplikasi di ponsel. Kode berubah tiap 30 detik. Jadi bila password bocor, orang lain tetap tidak bisa masuk tanpa ponsel Anda.

- **Wajib** untuk peran **Super Admin** dan **Admin**.
- **Opsional** (disarankan) untuk **Editor**.

## Menyiapkan (sekitar 2 menit)

1. Di ponsel, pasang aplikasi autentikator: Google Authenticator, Microsoft Authenticator, Authy, atau pengelola password (1Password, Bitwarden).
2. Masuk ke admin. Bila peran Anda wajib 2FA, halaman penyiapan terbuka otomatis. Bila tidak: menu pengguna (pojok kanan atas) > **Keamanan Akun** > **Mulai Penyiapan**.
3. Di aplikasi pilih **Tambah akun** > **Masukkan kunci penyiapan**. Isi nama akun dengan email Anda, kunci yang tampil di layar, dan jenis **Berbasis waktu**. (Bila membuka halaman ini dari ponsel yang sama, tautan "Buka langsung di aplikasi autentikator" bisa dipakai.)
4. Ketik kode 6 digit yang muncul di aplikasi, lalu **Aktifkan**.
5. **Simpan 8 kode pemulihan** yang tampil (Salin semua atau Unduh .txt). Kode ini hanya tampil sekali. Simpan di tempat aman, bukan di ponsel yang sama.

## Masuk sehari-hari

Email dan password, lalu kode 6 digit dari aplikasi. Kode yang sama tidak bisa dipakai dua kali; bila ditolak karena "baru saja dipakai", tunggu sekitar 30 detik. Setelah 5 kode salah, masuk dikunci sementara.

## Ponsel hilang, rusak, atau ganti

| Keadaan | Yang dilakukan |
|---|---|
| Punya kode pemulihan | Pada kolom kode ketik salah satu kode pemulihan (XXXXX-XXXXX). Tiap kode sekali pakai. Setelah masuk, buka **Keamanan Akun** > **Buat Kode Baru**. |
| Tidak punya kode pemulihan | Minta **Super Admin** membuka Pengguna > ubah akun Anda > **Hapus Verifikasi 2 Langkah**. Saat masuk berikutnya Anda menyiapkannya lagi di ponsel baru. |
| Super Admin satu-satunya yang kehilangan akses | Lewat server: `php artisan admin:reset-2fa email-anda@decaa.id`, lalu masuk dan siapkan ulang. |

Bila ganti ponsel dengan ponsel lama masih ada: hapus akun lama di aplikasi hanya **setelah** ponsel baru berhasil menampilkan kode untuk DECAA.ID.

## Catatan untuk pengelola server

- Jalankan `php artisan migrate` (menambah 4 kolom 2FA di tabel `users`).
- Rahasia 2FA disimpan **terenkripsi dengan `APP_KEY`**. **Jangan mengganti `APP_KEY`** pada situs yang sudah berjalan: semua 2FA menjadi tidak terbaca. Bila terpaksa, jalankan `admin:reset-2fa` untuk tiap akun.
- Jam server harus akurat (NTP aktif); selisih lebih dari sekitar 30 detik membuat kode ditolak.
- `php artisan site:check` memberi peringatan (FAIL dengan `--production`) bila ada Super Admin/Admin aktif yang belum memakai 2FA.
