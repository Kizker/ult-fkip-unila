# Catatan Produksi PPT Mobile Video 3

## File PPT

- File utama: `ppt-source/video-03-cara-mengajukan-layanan-mobile-clean-pointer.pptx`
- Ukuran slide: 954 x 1920 px.
- Format: portrait mobile.
- Status: sudah dibuat.

## Isi PPT

- PPT mengikuti narasi natural Video 3.
- Visual memakai screenshot UI asli website.
- Contoh utama memakai layanan `Surat Persetujuan Pra Penelitian`.
- Field yang tampil mengikuti data aktif terbaru di database: `Penerima Instansi`, `Penerima Jabatan`, `Penerima Kota`, dan `Semester`.
- Sistem produksi mengikuti Video 2: generator `.mjs`, rangkaian frame slide, transition fade cepat, dan auto advance timing.
- Tap pointer dan panah scroll memakai SVG image tunggal.
- Logo Unila dan FKIP Unila diperkuat di generator PPT sebagai overlay header/footer agar konsisten tampil pada semua frame capture.

## Cara Regenerasi

Jalankan dari root project:

```powershell
node "docs\video tutorial\ppt-mobile-video-03\ppt-source\create-ppt-mobile-video-03-recordlike.mjs"
```

## Validasi Terakhir

- File PPT berhasil dibuat: `ppt-source/video-03-cara-mengajukan-layanan-mobile-clean-pointer.pptx`.
- Jumlah slide/frame: 146.
- Jumlah media internal PPTX: 613.
- Semua slide memiliki transition `fade` dengan `spd="fast"`.
- Semua slide memiliki auto advance timing.
- Estimasi durasi auto advance: 25,59 detik.
- Screenshot capture berhasil dari Laragon domain `http://ult-fkip-unila.test`.
- Package PPTX sudah dibersihkan dari deklarasi `slideMaster` yang tidak memiliki part fisik, agar PowerPoint tidak meminta repair saat membuka file.
- Audit koordinat terbaru memperbaiki highlight/tap pada dashboard, hero layanan, katalog layanan, tombol Detail, detail layanan, persyaratan, tombol Ajukan layanan, lampiran, submit, dan tampilan berhasil.
- Audit logo terbaru memperbaiki tampilan header/footer yang sebelumnya kadang hanya menampilkan mark `ULT`; generator sekarang menambahkan pasangan logo Unila dan FKIP Unila pada header semua frame serta footer frame yang menampilkan footer.
