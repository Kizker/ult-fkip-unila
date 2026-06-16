# Storyboard PPT Mobile Video 2

## Judul

Panduan Mahasiswa: Cara Daftar, Login, dan Melengkapi Profil

## Tujuan

Dokumen ini menjadi acuan pembuatan PPT tutorial berbentuk layar handphone. Materi mengikuti naskah natural video 2 dan memakai tampilan UI asli website ULT FKIP agar pengguna mudah memahami setiap langkah.

## Format Slide

- Ukuran desain: 954 x 1920 px.
- Rasio: portrait mobile.
- Konversi PowerPoint 96 DPI: 9.9375 x 20 inch.
- Safe area teks: minimal 48 px dari kiri dan kanan, 80 px dari atas dan bawah.
- Gaya visual: gunakan screenshot UI asli sebagai latar utama, lalu tambahkan highlight, tap indicator, zoom ringan, dan teks singkat.
- Teks layar: ringkas, maksimal 1-2 baris per step.

## Sumber Utama

- Naskah: `docs/video tutorial/narasi natural/02-daftar-login-dan-lengkapi-profil-natural.md`
- Storyboard lama: `docs/video tutorial/storyboard/02-daftar-login-dan-lengkapi-profil-storyboard.md`
- UI daftar: `resources/views/auth/register.blade.php`
- UI login: `resources/views/auth/login.blade.php`
- UI verifikasi email: `resources/views/auth/verify-email.blade.php`
- UI pengaturan profil: `resources/views/profile/edit.blade.php`
- Aset publik tersedia: `docs/video tutorial/assets/public-full-pages/007-login.png` dan `docs/video tutorial/assets/public-full-pages/008-register.png`

## Catatan Akurasi UI

- Tutorial difokuskan untuk pengguna Mahasiswa.
- Jumlah scene tetap 30 scene dan tidak diringkas, supaya tutorial rinci dan mudah diikuti.
- Form daftar pada UI asli memuat Nama, Email, Jenis Akun, NPM/NIP, Jurusan, Program Studi, Foto Profil, Password, dan Konfirmasi Password.
- Halaman verifikasi email di UI asli menampilkan tombol Kirim ulang dan Logout.
- Halaman profil berada pada menu Pengaturan dan memuat Informasi Profil, Foto Profil, Nama, NPM/NIP, Jurusan, Program Studi, serta tombol Simpan Perubahan.
- Narasi menyebut dokumen pendukung secara umum, tetapi visual harus tetap mengikuti UI asli. Pada halaman profil yang dibaca, bagian unggah yang tersedia adalah Foto Profil, jadi scene unggah file direpresentasikan melalui area Foto Profil tanpa menambahkan komponen dokumen pendukung yang tidak ada.
- Simulasi inbox email boleh dibuat sebagai ilustrasi, tetapi harus dibuat mirip tampilan email sungguhan, jelas, dan tetap diberi label sebagai simulasi agar tidak dianggap bagian dari website ULT.

## Struktur Storyboard

| Scene | Durasi | Narasi Voice Over | Tampilan UI | Aksi Visual | Teks Layar | Animasi PPT |
| --- | ---: | --- | --- | --- | --- | --- |
| 01 | 4 detik | Pada video ini, kita akan belajar cara daftar akun, login ke sistem, dan melengkapi profil pengguna. | Cover portrait dengan judul video dan mockup HP. | Tampilkan judul dan label "Video 2". | Daftar, Login, dan Lengkapi Profil | Fade in judul, mockup HP masuk perlahan. |
| 02 | 5 detik | Pertama, buka halaman pendaftaran akun. | Halaman publik atau halaman register. | Sorot tombol/tautan Daftar, lalu pindah ke form Register. | Buka halaman Daftar | Tap indicator pada tombol Daftar, transisi slide ke register. |
| 03 | 7 detik | Di halaman ini, isi data yang diminta sesuai identitas Anda. | Form Register bagian atas. | Sorot field Nama dan Email. | Isi nama dan email aktif | Highlight kotak input, efek ketik singkat. |
| 04 | 6 detik | Masukkan nama lengkap, alamat email, nomor induk mahasiswa, dan data lain yang diperlukan oleh sistem. | Form Register bagian Jenis Akun dan NPM. | Pilih Jenis Akun Mahasiswa, lalu isi NPM. | Pilih Mahasiswa dan isi NPM | Tap dropdown, highlight field NPM. |
| 05 | 6 detik | Pastikan semua data yang dimasukkan benar. | Form Register dengan data terisi. | Sorot Nama, Email, dan NPM secara bergantian. | Cek data sebelum lanjut | Pulse highlight pada tiga field penting. |
| 06 | 7 detik | Jangan sampai ada kesalahan pada nama, email, atau nomor identitas, karena data ini akan dipakai pada proses berikutnya. | Close-up form identitas. | Tampilkan ikon cek pada field yang benar. | Nama, email, dan NPM harus benar | Zoom ringan ke area identitas. |
| 07 | 6 detik | Setelah itu, buat password untuk akun Anda. | Form Register bagian Password. | Sorot field Password. | Buat password akun | Tap field password, efek titik password muncul. |
| 08 | 6 detik | Gunakan password yang mudah Anda ingat, tetapi tetap aman. | Field Password dan Konfirmasi Password. | Sorot tombol mata/password otomatis jika perlu. | Gunakan password yang aman | Highlight ikon password dan konfirmasi password. |
| 09 | 7 detik | Kalau semua kolom sudah terisi, cek kembali formulir pendaftaran. | Form Register lengkap. | Scroll ringkas dari atas ke bawah form. | Periksa semua isian | Simulasi scroll vertikal dalam frame HP. |
| 10 | 5 detik | Jika sudah sesuai, klik tombol daftar atau registrasi. | Tombol Register/Daftar. | Tap tombol daftar. | Klik Daftar | Tap indicator membesar lalu fade. |
| 11 | 6 detik | Setelah pendaftaran berhasil, akun belum langsung dipakai. Anda perlu verifikasi email terlebih dahulu. | Halaman Verifikasi Email. | Sorot judul Verifikasi Email. | Verifikasi email dulu | Fade ke halaman verifikasi, highlight header. |
| 12 | 7 detik | Buka inbox dari email yang tadi didaftarkan, lalu cari pesan verifikasi dari sistem. | Simulasi inbox email mobile yang mirip tampilan email sungguhan. | Tampilkan daftar email dan sorot pesan verifikasi dari sistem. | Buka inbox email | Slide masuk kartu email, label kecil "Simulasi inbox email". |
| 13 | 5 detik | Kalau belum terlihat, cek juga folder spam atau junk mail. | Simulasi sidebar/folder email mobile. | Sorot folder spam/junk. | Cek spam jika email belum ada | Panah pendek ke folder spam, label kecil "Simulasi inbox email". |
| 14 | 6 detik | Setelah email ditemukan, buka pesannya lalu klik tautan verifikasi agar akun aktif. | Simulasi pesan email verifikasi yang mirip email asli. | Tap tautan verifikasi. | Klik tautan verifikasi | Tap indicator pada link verifikasi, label kecil "Simulasi inbox email". |
| 15 | 6 detik | Kalau email verifikasi belum masuk, gunakan tombol kirim ulang verifikasi yang tersedia pada halaman verifikasi email. | Halaman Verifikasi Email. | Sorot tombol Kirim ulang. | Gunakan Kirim ulang jika perlu | Highlight tombol, tap indicator. |
| 16 | 4 detik | Setelah email berhasil diverifikasi, barulah akun Anda siap digunakan. | Status sukses sederhana atau transisi ke login. | Munculkan ikon cek dan teks akun aktif. | Akun sudah aktif | Checkmark fade in. |
| 17 | 6 detik | Selanjutnya, masuk ke halaman login. | Halaman Login. | Sorot judul Login dan field Email. | Masuk ke halaman Login | Transisi geser ke halaman login. |
| 18 | 7 detik | Masukkan email atau akun yang tadi sudah didaftarkan, lalu ketik password Anda. | Form Login. | Isi Email dan Password. | Isi email dan password | Efek ketik pada dua field. |
| 19 | 5 detik | Klik tombol masuk untuk membuka dashboard pemohon. | Tombol Login/Masuk. | Tap tombol Masuk. | Klik Masuk | Tap indicator, tombol diberi glow singkat. |
| 20 | 5 detik | Setelah berhasil login, Anda akan melihat halaman utama akun pemohon. | Dashboard pemohon. | Tampilkan dashboard dalam frame HP. | Dashboard pemohon terbuka | Fade in dashboard. |
| 21 | 6 detik | Sebelum mengajukan layanan, sebaiknya periksa dulu bagian Pengaturan untuk mengecek dan melengkapi data diri Anda. | Topbar dashboard dengan avatar/menu akun. | Tap avatar/menu akun. | Buka Pengaturan | Tap avatar, dropdown muncul. |
| 22 | 5 detik | Buka menu pengaturan, lalu cek apakah data pribadi dan data akademik Anda sudah lengkap. | Menu akun dengan link Pengaturan. | Sorot pilihan Pengaturan. | Pilih Pengaturan | Highlight menu Pengaturan, transisi ke profil. |
| 23 | 7 detik | Perhatikan nama lengkap, nomor induk mahasiswa, program studi, alamat email, nomor telepon, dan data lain yang diminta oleh sistem. | Halaman Pengaturan/Informasi Profil. | Sorot kartu Informasi Profil dan field utama. | Cek data profil | Scroll pendek ke area form. |
| 24 | 7 detik | Kalau masih ada data yang kosong atau kurang tepat, langsung perbarui pada kolom yang tersedia. | Form profil. | Simulasi edit Nama, NPM, Jurusan, atau Program Studi. | Lengkapi data yang kosong | Highlight field kosong, efek ketik. |
| 25 | 6 detik | Jika sistem menyediakan bagian unggah dokumen pendukung, unggah file yang diminta sesuai ketentuan. | Area Foto Profil pada halaman profil. | Sorot tombol Pilih file. | Unggah file yang diminta | Tap tombol Pilih file. |
| 26 | 6 detik | Pastikan file yang dipilih benar, jelas, dan bisa dibuka. | Preview Foto Profil atau nama file terpilih. | Tampilkan preview/nama file. | Pastikan file benar dan jelas | Preview muncul dengan checkmark. |
| 27 | 5 detik | Setelah semua data profil sudah sesuai, simpan perubahan yang sudah Anda lakukan. | Tombol Simpan Perubahan. | Tap tombol Simpan Perubahan. | Simpan perubahan | Tap indicator, tombol diberi highlight. |
| 28 | 5 detik | Langkah ini penting karena profil yang lengkap akan membantu proses pengajuan layanan menjadi lebih lancar. | Halaman profil dengan data lengkap. | Tampilkan ringkasan data lengkap. | Profil lengkap membantu proses layanan | Checklist muncul satu per satu. |
| 29 | 5 detik | Kalau akun sudah berhasil dibuat, login sudah berhasil, dan profil sudah lengkap, berarti Anda sudah siap untuk masuk ke tahap berikutnya, yaitu mengajukan layanan. | Ringkasan tiga tahap. | Tampilkan tiga checklist: Daftar, Login, Profil. | Siap mengajukan layanan | Checklist berurutan. |
| 30 | 5 detik | Pada video selanjutnya, kita akan membahas cara memilih layanan, membaca syaratnya, mengisi formulir, dan mengirim permohonan. | Penutup video. | Tampilkan judul video berikutnya. | Berikutnya: Mengajukan Layanan | Fade out ke penutup. |

## Daftar Screenshot yang Dibutuhkan

| No | Kebutuhan Visual | Sumber yang Disarankan | Status |
| --- | --- | --- | --- |
| 1 | Halaman Register full mobile | `screenshots/01-register-top.png` | Selesai |
| 2 | Bagian atas form Register | `screenshots/01-register-top.png` | Selesai |
| 3 | Bagian Jurusan dan Program Studi | `screenshots/02-register-academic.png` | Selesai |
| 4 | Bagian Foto Profil Register | `screenshots/03-register-photo.png` | Selesai |
| 5 | Bagian Password dan tombol Daftar | `screenshots/04-register-password.png` | Selesai |
| 6 | Halaman Verifikasi Email | `screenshots/05-verify-email.png` | Selesai |
| 7 | Simulasi inbox email | `email-simulation/01-inbox-verification-list.png`, `02-spam-folder.png`, `03-verification-email-opened.png` | Selesai |
| 8 | Halaman Login | `screenshots/06-login.png` | Selesai |
| 9 | Dashboard Pemohon | `screenshots/07-dashboard-pemohon.png` | Selesai |
| 10 | Menu akun/topbar Pengaturan | `screenshots/08-menu-pengaturan.png` | Selesai |
| 11 | Halaman Pengaturan Profil | `screenshots/09-profil-top.png`, `10-profil-photo.png` | Selesai |
| 12 | Tombol Simpan Perubahan | `screenshots/11-profil-save.png` | Selesai |

## Catatan Produksi PPT

- Setiap scene dapat menjadi satu slide jika animasi sederhana.
- Scene yang membutuhkan scroll atau pengisian form sebaiknya dibuat 2-3 slide agar gerakannya halus saat diekspor video.
- Hindari menaruh semua narasi sebagai teks di layar. Teks layar hanya ringkasan aksi.
- Gunakan warna highlight yang kontras tetapi tetap sesuai UI, misalnya ungu/indigo sesuai aksen sistem.
- Untuk simulasi email, buat visualnya mirip inbox email sungguhan, tetapi beri label visual "Simulasi inbox email" agar tidak dianggap tampilan asli website.
- Jangan membuat komponen unggah dokumen pendukung baru pada profil jika tidak ada di UI asli. Gunakan area Foto Profil sesuai tampilan asli.
- Jika target akhir harus tepat 954 x 1920 px, ekspor video dari PPT perlu dicek ulang dan bisa disesuaikan lagi memakai tool video seperti FFmpeg.

## Checklist Sebelum Membuat PPT

- [x] Pastikan tutorial hanya untuk Mahasiswa.
- [x] Pastikan jumlah scene tidak diringkas.
- [x] Pastikan scene simulasi inbox email boleh dibuat sebagai ilustrasi yang mirip tampilan email sungguhan.
- [x] Pastikan unggah file profil mengikuti UI asli, yaitu area Foto Profil.
- [x] Ambil screenshot mobile sesuai ukuran atau rasio 954 x 1920.
- [ ] Tentukan apakah narasi voice over akan direkam terpisah atau hanya dipakai sebagai teks panduan timing.
- [ ] Setelah PPT selesai, cek ulang urutan slide terhadap naskah natural.
