# Storyboard PPT Mobile Video 3

## Judul

Panduan Mahasiswa: Cara Mengajukan Layanan Surat dan Sertifikat

## Tujuan

Dokumen ini menjadi acuan pembuatan PPT tutorial berbentuk layar handphone. Materi mengikuti naskah natural Video 3 dan menggunakan UI asli website ULT FKIP.

## Format Slide

- Ukuran desain: 954 x 1920 px.
- Rasio: portrait mobile.
- Konversi PowerPoint 96 DPI: 9.9375 x 20 inch.
- Gaya visual: screenshot UI asli sebagai latar penuh, highlight area penting, tap pointer, panah scroll, dan transisi frame cepat.
- Teks layar tidak ditambahkan sebagai caption besar agar tampilan tetap seperti rekaman layar.

## Sumber Utama

- Naskah: `docs/video tutorial/narasi natural/03-cara-mengajukan-layanan-natural.md`
- Storyboard lama: `docs/video tutorial/storyboard/03-cara-mengajukan-layanan-storyboard.md`
- Dokumen utama: `docs/video tutorial/03-cara-mengajukan-layanan.md`
- UI daftar layanan: `resources/views/public/services/index.blade.php`
- UI detail layanan: `resources/views/public/services/show.blade.php`
- UI form pengajuan: `resources/views/student/requests/create.blade.php`
- UI riwayat permohonan: `resources/views/student/requests/index.blade.php`

## Catatan Akurasi UI

- Tutorial difokuskan untuk pengguna Mahasiswa.
- Contoh layanan utama adalah `Surat Persetujuan Pra Penelitian`.
- Form layanan ini mengikuti data aktif terbaru di database. Saat capture, field yang tampil adalah Penerima Instansi, Penerima Jabatan, Penerima Kota, Semester, serta Lampiran Umum.
- Layanan sertifikat tetap disebut dalam narasi sebagai perhatian umum, tetapi visual utama memakai satu contoh layanan surat agar alur tidak bercabang terlalu panjang.
- Simulasi file picker boleh dibuat sebagai overlay visual dan harus tampak sebagai simulasi pemilihan file.

## Struktur Storyboard

| Scene | Durasi | Narasi Voice Over | Tampilan UI | Aksi Visual | Animasi PPT |
| --- | ---: | --- | --- | --- | --- |
| 01 | 4 detik | Pada video ini, kita akan melihat cara mengajukan layanan melalui sistem. | Dashboard mahasiswa. | Tampilkan dashboard setelah login. | Fade cepat. |
| 02 | 4 detik | Sebelum mulai, pastikan Anda sudah login dan profil pengguna sudah lengkap. | Dashboard mahasiswa. | Sorot tombol Ajukan layanan. | Highlight dan tap pointer. |
| 03 | 5 detik | Setelah itu, buka menu layanan atau daftar layanan. | Halaman layanan. | Masuk ke daftar layanan. | Transisi ke halaman layanan. |
| 04 | 5 detik | Di halaman ini, Anda bisa melihat berbagai layanan yang tersedia untuk diajukan. | Daftar layanan. | Scroll ke kartu layanan. | Panah scroll. |
| 05 | 5 detik | Pilih layanan yang sesuai dengan kebutuhan Anda. | Kartu Surat Persetujuan Pra Penelitian. | Sorot tombol Detail/Ajukan. | Highlight dan tap pointer. |
| 06 | 6 detik | Sebelum menekan tombol ajukan, baca dulu informasi layanan, persyaratan, dan petunjuk yang tersedia. | Detail layanan. | Sorot ringkasan layanan. | Highlight. |
| 07 | 6 detik | Langkah ini penting supaya Anda tahu data dan dokumen apa saja yang harus disiapkan. | Detail persyaratan/SOP. | Scroll ke bagian persyaratan. | Panah scroll dan highlight. |
| 08 | 5 detik | Kalau sudah paham, klik tombol ajukan layanan untuk membuka formulir permohonan. | Tombol Ajukan layanan di bawah detail layanan. | Scroll ke tombol lalu tap tombol ajukan. | Panah scroll dan tap pointer. |
| 09 | 6 detik | Isi semua kolom pada formulir dengan teliti. | Form pengajuan awal. | Sorot form. | Highlight. |
| 10 | 7 detik | Kalau Anda mengajukan layanan surat, isi data sesuai kebutuhan surat dan lengkapi dokumen pendukung yang diminta. | Field lokasi/instansi. | Isi lokasi dan instansi. | Frame input berurutan. |
| 11 | 7 detik | Kalau Anda mengajukan layanan sertifikat, perhatikan penulisan nama, data kegiatan, dan informasi lain. | Field jabatan/kota/perihal. | Isi field lanjutan. | Frame input berurutan. |
| 12 | 6 detik | Jika ada bagian upload dokumen, unggah file sesuai ketentuan. | Area lampiran. | Tap pemilih file. | Tap pointer dan overlay file picker. |
| 13 | 6 detik | Pastikan format file benar, ukuran file sesuai, dan dokumen bisa dibaca dengan jelas. | Lampiran terpilih. | Tampilkan file terpilih. | Highlight stabil. |
| 14 | 6 detik | Setelah semua data diisi dan dokumen sudah diunggah, cek kembali seluruh isi formulir. | Form bagian bawah. | Scroll dan sorot field. | Panah scroll. |
| 15 | 5 detik | Jika semuanya sudah benar, klik tombol kirim atau submit untuk mengirim permohonan. | Tombol Submit. | Tap submit. | Tap pointer. |
| 16 | 6 detik | Kalau pengajuan berhasil, sistem biasanya akan menampilkan notifikasi. | Riwayat permohonan. | Tampilkan riwayat terbaru. | Fade cepat. |
| 17 | 5 detik | Sampai di tahap ini, proses pengajuan sudah selesai dilakukan dan tinggal menunggu tindak lanjut. | Riwayat permohonan. | Sorot permohonan terbaru. | Highlight. |
| 18 | 5 detik | Pada video berikutnya, kita akan melihat cara cek status, revisi, dan unduh hasil layanan. | Riwayat permohonan. | Akhiri pada daftar permohonan. | Fade out. |

## Checklist Produksi

- [x] Buat folder dan script capture.
- [x] Ambil screenshot mobile UI asli.
- [x] Buat generator PPT berbasis frame seperti Video 2.
- [x] Gunakan pointer dan panah scroll SVG.
- [x] Validasi PPTX: jumlah slide, media, transition, dan timing.
