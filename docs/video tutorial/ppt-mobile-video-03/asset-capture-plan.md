# Rencana Aset PPT Mobile Video 3

## Tujuan

Dokumen ini menjadi panduan aset untuk PPT mobile Video 3: `Panduan Mahasiswa: Cara Mengajukan Layanan Surat dan Sertifikat`. Sistem produksi mengikuti pola Video 2, yaitu memakai screenshot UI asli website dalam format portrait mobile dan generator PPT berbasis frame slide.

## Keputusan Produksi

- Format slide: 954 x 1920 px.
- Fokus pengguna: Mahasiswa.
- Contoh utama: `Surat Persetujuan Pra Penelitian`, karena layanan ini memiliki field formulir dan lampiran umum.
- Alur tidak mengirim data produksi; capture memakai akun demo dan data tutorial.
- Tap pointer dan panah scroll memakai SVG image tunggal seperti versi final Video 2.
- Tidak memakai aset storyboard landscape lama sebagai basis utama PPT.

## Struktur Folder

| Folder | Fungsi |
| --- | --- |
| `screenshots/` | Screenshot UI asli website dalam rasio mobile. |
| `ppt-source/` | Script capture, aset pointer, dan generator PPT. |
| `highlight-audit/` | Preview audit visual jika dibutuhkan. |

## Daftar Screenshot UI Asli

| No | Nama File | Route/Halaman | Kebutuhan |
| --- | --- | --- | --- |
| 00 | `00-dashboard.png` | `/mahasiswa/dashboard` | Pembuka setelah login. |
| 01 | `01-layanan-index-top.png` | `/layanan` | Halaman daftar layanan. |
| 02 | `02-layanan-index-card.png` | `/layanan` | Kartu layanan yang dipilih. |
| 03 | `03-detail-layanan-top.png` | `/layanan/{slug}` | Detail layanan dan judul. |
| 04 | `04-detail-layanan-syarat.png` | `/layanan/{slug}` | Persyaratan dan SOP. |
| 04b | `04-detail-layanan-ajukan.png` | `/layanan/{slug}` | Tombol ajukan layanan di bawah detail. |
| 05 | `05-form-top-empty.png` | `/mahasiswa/permohonan/buat/{slug}` | Form pengajuan awal. |
| 06 | `06-form-field-instansi.png` | form | Field penerima instansi terisi. |
| 07 | `07-form-field-jabatan.png` | form | Field penerima jabatan terisi. |
| 08 | `08-form-field-kota.png` | form | Field penerima kota terisi. |
| 09 | `09-form-field-semester.png` | form | Field semester terisi. |
| 10 | `10-form-attachment-empty.png` | form | Area lampiran umum. |
| 11 | `11-form-attachment-picker.png` | form + overlay | Simulasi pemilih file lampiran. |
| 12 | `12-form-attachment-selected.png` | form | Lampiran terpilih. |
| 13 | `13-form-submit.png` | form | Tombol submit. |
| 14 | `14-requests-success.png` | `/mahasiswa/permohonan` | Notifikasi/riwayat setelah submit. |

## Validasi Aset

- Semua screenshot harus 954 x 1920 px.
- Screenshot harus berasal dari UI asli website.
- Overlay file picker hanya dipakai untuk simulasi pemilihan file, bukan dianggap bagian UI website.
- Setiap aset harus bisa dipakai langsung oleh generator PPT.
