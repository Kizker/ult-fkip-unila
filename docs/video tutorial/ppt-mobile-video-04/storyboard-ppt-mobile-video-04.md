# Storyboard PPT Mobile Video 4

## Judul

Panduan Mahasiswa: Cara Cek Status, Revisi, dan Unduh Hasil Layanan

## Tujuan

Dokumen ini menjadi acuan pembuatan PPT tutorial berbentuk layar handphone. Materi mengikuti naskah natural Video 4 dan menggunakan UI asli website ULT FKIP. Video ini dirancang dengan interaksi dinamis (seperti perubahan status, simulasi revisi form, dan preview PDF) agar lebih komprehensif dari video sebelumnya.

## Format Slide

- Ukuran desain: 954 x 1920 px.
- Rasio: portrait mobile.
- Konversi PowerPoint 96 DPI: 9.9375 x 20 inch.
- Gaya visual: screenshot UI asli sebagai latar penuh, highlight area penting, tap pointer (SVG), panah scroll, simulasi notifikasi unduhan, preview dokumen, dan transisi frame cepat.
- Teks layar: minimalis, berupa ringkasan singkat dari voice over.

## Sumber Utama

- Naskah: `docs/video tutorial/narasi natural/04-status-revisi-dan-unduh-hasil-natural.md`
- Draft Lama: `docs/video tutorial/04-status-revisi-dan-unduh-hasil.md`
- UI riwayat permohonan: `resources/views/student/requests/index.blade.php`
- UI detail permohonan: `resources/views/student/requests/show.blade.php`
- UI form revisi: `resources/views/student/requests/edit.blade.php`

## Catatan Akurasi UI & Skenario Spesial

- **Progresi Status:** Karena narasi menyebutkan BANYAK status, kita akan menampilkan list permohonan yang *scroll* secara perlahan, menunjukkan beberapa permohonan dengan *badge* status yang berbeda-beda sebagai background visual, sebelum akhirnya masuk ke satu permohonan untuk dilihat detailnya.
- **Skenario Revisi:** Pada narasi "Perlu Perbaikan", kita akan masuk ke detail permohonan yang memiliki kotak "Catatan Petugas" (berwarna kuning/merah muda). Kita akan mensimulasikan klik tombol perbaiki, masuk ke form edit, mengganti satu input/file, lalu menekan Submit.
- **Skenario Selesai & Download:** Di akhir, kita akan menunjukkan status "Selesai" (Hijau), tombol "Unduh Hasil" muncul. Simulasi menekan unduh, memunculkan notifikasi OS (misal: "File berhasil diunduh"), dan dilanjutkan transisi ke tampilan dokumen PDF terbuka sebagai hasil akhir.

## Struktur Storyboard

| Scene | Durasi | Narasi Voice Over | Tampilan UI | Aksi Visual | Teks Layar |
| --- | ---: | --- | --- | --- | --- |
| 01 | 5 detik | Pada video ini, kita akan membahas apa yang perlu dilakukan setelah permohonan berhasil dikirim. | Cover portrait dengan judul video dan mockup HP. | Tampilkan judul dan label "Video 4". | Cek Status, Revisi, & Unduh Hasil |
| 02 | 4 detik | Langkah pertama adalah membuka menu daftar permohonan atau riwayat permohonan. | Dashboard pemohon. | Tap menu/sidebar, pilih Riwayat Permohonan. | Buka riwayat permohonan |
| 03 | 5 detik | Di halaman ini, Anda bisa melihat semua pengajuan yang pernah dibuat beserta status terbarunya. | Halaman Riwayat Permohonan. | Tampilkan list kartu permohonan. | Cek daftar pengajuan |
| 04 | 6 detik | Status ini penting untuk dipahami, karena setiap status menunjukkan posisi permohonan Anda sedang berada di tahap yang mana. | Halaman Riwayat Permohonan. | Scroll perlahan menunjukkan ragam warna badge status (Kuning, Merah, Hijau). | Perhatikan warna dan nama status |
| 05 | 4 detik | Pilih salah satu permohonan untuk melihat detailnya. | Kartu Permohonan (contoh: Status Diajukan). | Highlight kartu dan tap. | Klik untuk lihat detail |
| 06 | 5 detik | Pada halaman detail permohonan, sistem akan menampilkan informasi pengajuan, dokumen yang sudah diunggah, dan status layanan. | Halaman Detail Permohonan. | Scroll ringan menampilkan seluruh blok informasi. | Tampilan detail permohonan |
| 07 | 6 detik | Kalau statusnya diajukan, artinya permohonan Anda sudah berhasil dikirim dan sedang menunggu pemeriksaan awal dari petugas. | Detail Permohonan (Badge Abu-abu/Biru: Diajukan). | Highlight badge "Diajukan". | Diajukan = Menunggu pemeriksaan |
| 08 | 7 detik | Kalau statusnya diverifikasi unit atau jurusan, berarti permohonan Anda sudah diperiksa pada jurusan atau bagian terkait dan sedang lanjut ke tahap berikutnya. | Detail Permohonan berubah ke (Badge: Diverifikasi Unit). | Highlight badge berubah. | Diverifikasi Unit = Pemeriksaan fakultas/jurusan |
| 09 | 5 detik | Kalau statusnya review ULT, artinya permohonan sedang ditinjau oleh petugas ULT untuk memastikan data dan berkas sudah sesuai. | Detail Permohonan berubah ke (Badge: Review ULT). | Highlight badge berubah. | Review ULT = Pengecekan akhir berkas |
| 10 | 7 detik | Kalau statusnya menunggu tanda tangan unit atau menunggu tanda tangan fakultas, berarti permohonan Anda sudah masuk ke tahap persetujuan atau penandatanganan oleh pihak yang berwenang. | Detail Permohonan berubah ke (Badge: Menunggu TTD). | Highlight badge berubah. | Menunggu TTD = Persetujuan pejabat |
| 11 | 6 detik | Pada beberapa layanan, Anda juga bisa melihat status seperti nomor terbit atau nomor surat diisi. Ini menunjukkan dokumen sedang masuk ke tahap penomoran administrasi. | Detail Permohonan berubah ke (Badge: Nomor Terbit). | Highlight badge berubah. | Nomor Terbit = Tahap penomoran |
| 12 | 5 detik | Jika muncul status penandatangan, artinya dokumen sedang berada pada proses tanda tangan dan Anda hanya perlu menunggu proses itu selesai. | Detail Permohonan berubah ke (Badge: Penandatanganan). | Highlight badge berubah. | Proses tanda tangan dokumen |
| 13 | 6 detik | Kalau statusnya diproses, secara umum artinya permohonan Anda sudah diterima dan sedang dikerjakan lebih lanjut oleh petugas sesuai alur layanan. | Detail Permohonan berubah ke (Badge Kuning: Diproses). | Highlight badge berubah. | Diproses = Layanan sedang dikerjakan |
| 14 | 7 detik | Kalau statusnya perlu perbaikan, baca catatan dari petugas dengan teliti. Catatan ini akan menjelaskan bagian mana yang perlu diperbaiki. | Detail Permohonan (Badge Merah: Perlu Perbaikan). | Highlight badge lalu sorot kotak Catatan Petugas. | Cek Catatan Petugas |
| 15 | 5 detik | Setelah itu, buka kembali permohonan tersebut dan lakukan perbaikan sesuai arahan. | Detail Permohonan (Perbaikan). | Scroll ke tombol "Perbaiki Permohonan" dan tap. | Klik Perbaiki Permohonan |
| 16 | 6 detik | Perbaikannya bisa berupa mengubah data pada formulir, menambahkan informasi yang kurang, atau mengunggah ulang dokumen yang benar. | Form Edit Permohonan. | Simulasi hapus lampiran lama, klik pilih file baru (file picker). | Unggah/perbaiki data sesuai catatan |
| 17 | 5 detik | Kalau semua sudah diperbaiki, cek kembali isinya lalu kirim ulang permohonan. | Form Edit Permohonan (Bawah). | Scroll ke tombol "Kirim Ulang" dan tap. | Klik Kirim Ulang |
| 18 | 7 detik | Selain itu, ada juga status ditolak, ditolak admin, atau ditolak saat penandatanganan. Jika status seperti ini muncul, artinya permohonan tidak dapat dilanjutkan... | Layar kembali ke Riwayat, sorot permohonan lain (Badge: Ditolak). | Highlight badge Ditolak. | Ditolak = Permohonan tidak dapat lanjut |
| 19 | 6 detik | Kalau status layanan sudah selesai, biasanya akan tersedia tombol unduh atau download pada halaman detail permohonan. | Detail Permohonan (Badge Hijau: Selesai). | Highlight badge "Selesai" lalu sorot tombol "Unduh Hasil". | Status Selesai |
| 20 | 4 detik | Klik tombol tersebut untuk mengunduh hasil layanan. | Tombol Unduh Hasil. | Tap tombol Unduh Hasil. | Klik Unduh Hasil |
| 21 | 6 detik | Setelah file berhasil diunduh, buka dokumennya dan periksa kembali isi utamanya supaya sesuai dengan kebutuhan Anda. | Layar Notifikasi OS/Browser. | Muncul notifikasi "Unduhan Selesai", klik buka. Transisi ke layar PDF Preview (contoh surat). | Buka dan cek dokumen hasil |
| 22 | 5 detik | Simpan file hasil layanan tersebut dengan baik agar mudah ditemukan saat diperlukan. | Layar PDF Preview. | Highlight ikon simpan/share di dokumen. | Simpan dokumen dengan aman |
| 23 | 6 detik | Dengan fitur riwayat permohonan ini, Anda bisa memantau seluruh proses layanan dengan lebih mudah tanpa harus mengecek secara manual di luar sistem. | Layar Riwayat Permohonan (keseluruhan). | Kembali ke list Riwayat. Scroll santai. | Pantau semua di satu tempat |
| 24 | 6 detik | Sampai di sini, seluruh alur penggunaan sistem untuk pemohon layanan sudah selesai, mulai dari daftar akun sampai mengunduh hasil layanan. | Layar Riwayat (zoom out/fade). | Muncul ringkasan 4 langkah (Daftar -> Lengkapi Profil -> Ajukan -> Selesai). | Alur layanan selesai |
| 25 | 4 detik | Semoga tutorial ini membantu Anda menggunakan Sistem Layanan ULT FKIP Unila dengan lebih mudah. | Penutup Video. | Tampilkan logo ULT dan Unila. | Terima Kasih |

## Checklist Produksi

- [ ] Siapkan dataset *dummy* di database lokal untuk menghasilkan riwayat permohonan dengan ragam status yang berbeda (Diajukan, Diproses, Ditolak, Perlu Perbaikan, Selesai).
- [ ] Render SVG Pointer dan Arrow Scroll sesuai skrip dari Video 02/03.
- [ ] Buat aset notifikasi unduhan OS mobile (bisa SVG/PNG khusus).
- [ ] Siapkan dokumen *dummy* PDF/Sertifikat untuk di-screenshot sebagai preview layar di akhir.
- [ ] Render animasi dengan durasi transisi yang pas (fade fast) agar mulus.
