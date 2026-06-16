# Asset Capture Plan PPT Mobile Video 4

Dokumen ini mencatat daftar *screenshot* (tangkapan layar) UI asli yang harus disiapkan untuk merealisasikan storyboard Video 4. Seluruh *screenshot* harus beresolusi portrait mobile (954 x 1920 px) dan menggunakan data yang masuk akal (*dummy data* yang baik).

## Persiapan Data (Seeding)

Sebelum melakukan tangkapan layar, pastikan database lokal di-*seed* dengan data pengguna (Mahasiswa) yang memiliki beberapa riwayat permohonan layanan. Setiap permohonan diatur *status*-nya sesuai dengan kebutuhan skenario.
*   Permohonan A: Status **Diajukan**
*   Permohonan B: Status **Diverifikasi Unit**
*   Permohonan C: Status **Review ULT**
*   Permohonan D: Status **Menunggu Tanda Tangan** / **Nomor Terbit** / **Penandatanganan**
*   Permohonan E: Status **Diproses**
*   Permohonan F: Status **Perlu Perbaikan** (Wajib diisi "Catatan Petugas" dengan pesan: *Mohon ganti file lampiran dengan scan KTP asli, bukan fotokopi.*)
*   Permohonan G: Status **Ditolak**
*   Permohonan H: Status **Selesai** (Pastikan tombol "Unduh Hasil" muncul)

## Daftar Screenshot

| ID | Nama File | Deskripsi / Fokus UI | Status |
| :--- | :--- | :--- | :--- |
| 1 | `01-riwayat-permohonan-list.png` | Halaman Riwayat Permohonan penuh, menampilkan *scroll* panjang dari berbagai kartu permohonan dengan ragam warna *badge* status. | Belum |
| 2 | `02-detail-diajukan.png` | Detail Permohonan dengan *badge* "Diajukan". | Belum |
| 3 | `03-detail-diverifikasi-unit.png` | Detail Permohonan dengan *badge* "Diverifikasi Unit". | Belum |
| 4 | `04-detail-review-ult.png` | Detail Permohonan dengan *badge* "Review ULT". | Belum |
| 5 | `05-detail-menunggu-ttd.png` | Detail Permohonan dengan *badge* "Menunggu Tanda Tangan" / "Menunggu Tanda Tangan Fakultas". | Belum |
| 6 | `06-detail-nomor-terbit.png` | Detail Permohonan dengan *badge* "Nomor Terbit" / "Nomor Surat Diisi". | Belum |
| 7 | `07-detail-penandatanganan.png` | Detail Permohonan dengan *badge* "Penandatanganan". | Belum |
| 8 | `08-detail-diproses.png` | Detail Permohonan dengan *badge* "Diproses" (Kuning). | Belum |
| 9 | `09-detail-perlu-perbaikan.png` | Detail Permohonan dengan *badge* "Perlu Perbaikan" (Merah) dan kotak "Catatan Petugas" terlihat jelas. Terdapat tombol "Perbaiki Permohonan". | Belum |
| 10 | `10-detail-ditolak.png` | Detail Permohonan dengan *badge* "Ditolak". | Belum |
| 11 | `11-detail-selesai.png` | Detail Permohonan dengan *badge* "Selesai" (Hijau). Tombol "Unduh Hasil" terlihat jelas. | Belum |
| 12 | `12-form-edit-revisi.png` | Form Edit Permohonan (setelah klik Perbaiki). Bagian *upload file* terlihat. | Belum |
| 13 | `13-form-edit-revisi-filepicker.png` | *Overlay* UI *File Picker* OS mobile (simulasi pilih file baru). | Belum |
| 14 | `14-form-edit-revisi-bottom.png` | Bagian bawah Form Edit yang menampilkan tombol "Kirim Ulang" atau "Update". | Belum |
| 15 | `15-os-download-notification.png` | *Overlay* notifikasi sistem (seperti notifikasi Chrome Android: *File downloaded. Open.*) | Belum |
| 16 | `16-pdf-preview.png` | Layar penuh menampilkan pratinjau dokumen PDF (contoh: Surat Persetujuan Pra Penelitian dengan kop, stempel, dan tanda tangan). | Belum |

## Catatan Penting
- Semua tangkapan layar utama UI harus langsung dari browser agar *font* dan *spacing* akurat.
- *File picker* dan *download notification* boleh dibuat secara desain vector/image-editing sebagai aset transparan (`.png`) lalu di-*overlay* ke dalam slide PPT, agar lebih fleksibel saat dianimasikan.
