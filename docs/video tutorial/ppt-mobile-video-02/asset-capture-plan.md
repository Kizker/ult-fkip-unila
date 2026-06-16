# Rencana Aset PPT Mobile Video 2

## Tujuan

Dokumen ini dipakai untuk menyiapkan aset visual sebelum membuat file PPT. Targetnya adalah semua tampilan utama sesuai UI asli website, berformat portrait mobile, dan siap dipakai pada slide 954 x 1920 px.

## Keputusan Produksi

- Tutorial tetap memakai 30 scene.
- Fokus pengguna adalah Mahasiswa.
- Simulasi inbox email boleh dibuat sebagai ilustrasi, tetapi harus mirip tampilan inbox email sungguhan.
- Visual unggah file pada bagian profil harus mengikuti UI asli. Pada halaman profil, area unggah yang tersedia adalah Foto Profil.
- Aset landscape lama dari `docs/video tutorial/storyboard visual/images` tidak dipakai sebagai basis utama PPT karena dimensinya 1600 x 900.
- Aset publik `007-login.png` dan `008-register.png` bisa menjadi referensi, tetapi untuk PPT portrait sebaiknya dibuat ulang sebagai screenshot mobile.

## Struktur Folder

| Folder | Fungsi |
| --- | --- |
| `screenshots/` | Menyimpan screenshot UI asli website dalam rasio mobile. |
| `email-simulation/` | Menyimpan ilustrasi inbox email, folder spam, dan isi email verifikasi. |
| `ppt-source/` | Menyimpan file kerja PPT atau aset pendukung sebelum export video. |

## Daftar Screenshot UI Asli

| No | Nama File Disarankan | Route/Halaman | Kebutuhan Scene | Catatan |
| --- | --- | --- | --- | --- |
| 01 | `01-register-top.png` | `/register` | Scene 02-04 | Selesai. Bagian judul, Nama, Email, Jenis Akun, NPM. |
| 02 | `02-register-academic.png` | `/register` | Scene 04-06 | Selesai. Bagian Jurusan dan Program Studi. |
| 03 | `03-register-photo.png` | `/register` | Scene 06 | Selesai. Area Foto Profil sesuai UI asli. |
| 04 | `04-register-password.png` | `/register` | Scene 07-10 | Selesai. Password, Konfirmasi Password, tombol Daftar. |
| 05 | `05-verify-email.png` | `/email/verify` | Scene 11 dan 15 | Selesai. Halaman Verifikasi Email dengan tombol Kirim ulang. |
| 06 | `06-login.png` | `/login` | Scene 17-19 | Selesai. Form Login, Email, Password, tombol Masuk. |
| 07 | `07-dashboard-pemohon.png` | `/mahasiswa/dashboard` | Scene 20 | Selesai. Tampilan utama setelah login. |
| 08 | `08-menu-pengaturan.png` | `/mahasiswa/dashboard` | Scene 21-22 | Selesai. Topbar/menu akun dalam keadaan terbuka, pilihan Pengaturan terlihat. |
| 09 | `09-profil-top.png` | `/profil` | Scene 23-24 | Selesai. Header Pengaturan dan Informasi Profil. |
| 10 | `10-profil-photo.png` | `/profil` | Scene 25-26 | Selesai. Area Foto Profil dan tombol Pilih file. |
| 11 | `11-profil-save.png` | `/profil` | Scene 27-28 | Selesai. Tombol Simpan Perubahan. |

## Daftar Ilustrasi Email

| No | Nama File Disarankan | Kebutuhan Scene | Catatan |
| --- | --- | --- | --- |
| 01 | `01-inbox-verification-list.png` | Scene 12 | Selesai. Tampilan inbox mobile, ada email dari sistem ULT FKIP. |
| 02 | `02-spam-folder.png` | Scene 13 | Selesai. Tampilan folder spam/junk sebagai alternatif pengecekan. |
| 03 | `03-verification-email-opened.png` | Scene 14 | Selesai. Isi email dengan tombol/tautan verifikasi yang jelas. |

## Standar Visual Screenshot

- Frame akhir mengikuti slide 954 x 1920 px.
- Jika screenshot asli lebih panjang dari layar, gunakan crop per area yang sesuai scene.
- Jangan stretch screenshot sampai rasio berubah.
- Area penting diberi ruang untuk highlight dan teks layar.
- Teks overlay tidak boleh menutup tombol/form yang sedang dijelaskan.

## Standar Visual Simulasi Email

- Gunakan tampilan mobile portrait.
- Buat layout mendekati inbox email umum: header inbox, daftar pesan, nama pengirim, subjek, waktu, dan preview pesan.
- Gunakan konten yang jelas sebagai simulasi, misalnya pengirim `ULT FKIP Unila` dan subjek `Verifikasi Email Akun`.
- Jangan memakai logo atau tampilan brand email tertentu secara berlebihan.
- Tambahkan label kecil `Simulasi inbox email`.

## Validasi Sebelum PPT

- [x] Semua screenshot UI asli sudah ada di folder `screenshots/`.
- [x] Semua simulasi email sudah ada di folder `email-simulation/`.
- [x] Ukuran/rasio aset sudah cocok untuk frame portrait.
- [x] Setiap scene pada storyboard punya visual yang jelas.
- [x] Tidak ada visual yang menambahkan komponen palsu pada UI website.
