# Lupa Password (Admin DECAA.ID)

Bagian ini melengkapi `PANDUAN-ADMIN.md`.

## Bila Anda lupa password

1. Di halaman masuk klik **Lupa password?**.
2. Masukkan email akun admin Anda, klik **Kirim Tautan**. Layar selalu menjawab sama, apa pun emailnya (agar email yang terdaftar tidak bisa ditebak orang lain).
3. Buka email dari DECAA.ID (cek folder spam). Klik tautannya dalam **60 menit**; tautan hanya bisa dipakai sekali.
4. Buat password baru (minimal 10 karakter, ada huruf dan angka), lalu masuk seperti biasa.

Catatan:
- Bila akun memakai **verifikasi 2 langkah**, kode dari aplikasi autentikator tetap diminta saat masuk. Lupa password tidak melewati 2FA (lihat `PANDUAN-2FA.md` bila ponsel juga hilang).
- Akun **nonaktif** tidak dikirimi tautan. Permintaan berulang dibatasi (jeda sekitar 1 menit per akun; 5 permintaan per 10 menit per perangkat).
- Setelah password diganti, semua cookie "ingat saya" lama dicabut.
- Email tidak datang? Hubungi **Super Admin**: Pengguna > ubah akun > **Atur Ulang Password** (password sementara).

## Untuk pengelola server

- Tabel `password_resets` bawaan Laravel dipakai (`php artisan migrate` bila belum ada; `site:check` memeriksanya).
- Email memakai pengaturan `MAIL_*` yang sama dengan form Kontak. Di lokal (`MAIL_MAILER=log`) tautan muncul di `storage/logs/laravel.log` alih-alih terkirim.
- Kegagalan kirim email tidak terlihat oleh pengunjung (sengaja); cek `storage/logs/laravel.log`.
