# Catatan Produksi PPT Mobile Video 2

## File PPT

- File utama: `ppt-source/video-02-daftar-login-lengkapi-profil-mobile-clean-pointer.pptx`
- File clean-avatar sebelumnya: `ppt-source/video-02-daftar-login-lengkapi-profil-mobile-clean-avatar.pptx`
- File final-polished sebelumnya: `ppt-source/video-02-daftar-login-lengkapi-profil-mobile-final-polished.pptx`
- File field-by-field sebelumnya: `ppt-source/video-02-daftar-login-lengkapi-profil-mobile-field-by-field.pptx`
- File input-flow sebelumnya: `ppt-source/video-02-daftar-login-lengkapi-profil-mobile-input-flow.pptx`
- File record-like sebelumnya: `ppt-source/video-02-daftar-login-lengkapi-profil-mobile-recordlike.pptx`
- File lama pembanding: `ppt-source/video-02-daftar-login-lengkapi-profil-mobile-fixed.pptx`
- Ukuran slide: 954 x 1920 px.
- Jumlah frame slide: 277 slide.
- Format: portrait mobile.

## Isi PPT

- Slide mengikuti 30 scene pada storyboard, tetapi dipecah menjadi 277 frame agar saat diekspor video terasa lebih halus seperti rekaman layar.
- Alur pembuka dimulai dari beranda publik, membuka menu mobile, lalu memilih tombol Daftar sebelum masuk ke form pendaftaran.
- Screenshot UI asli website dipakai sebagai layar penuh utama.
- Simulasi input data sudah dibuat per field: nama, email, jenis akun, NPM, jurusan, program studi, pilih file foto, password, konfirmasi password, email login, dan password login.
- Dropdown jenis akun, jurusan, dan program studi ditampilkan terbuka sebelum pilihan dipilih.
- Proses pemilihan file foto ditampilkan sebagai bottom sheet simulasi pemilih file, lalu dilanjutkan ke UI asli yang menampilkan file terpilih.
- Pada bagian melengkapi profil, proses pilih file foto juga ditampilkan lengkap: sebelum pilih file, dialog pilih file, file terpilih, lalu lanjut simpan profil.
- Simulasi inbox email memakai visual mobile yang mirip email sungguhan dan diberi label simulasi.
- Lingkaran `.tap` bawaan pada PNG simulasi email sudah dihapus agar penunjuk ketukan hanya berasal dari overlay PPT.
- Tidak memakai caption aksi atau badge scene pada layar.
- Highlight area penting, tap indicator, dan arah scroll dibuat sebagai overlay visual.
- Highlight dan tap indicator sudah diaudit ulang agar lebih presisi ke field, item dropdown, tombol, kartu, dan panel yang benar-benar sedang ditunjuk.
- Highlight tombol verifikasi email sudah disesuaikan ke ukuran tombol asli, dan dropdown akun dashboard dibiarkan tampil asli tanpa highlight panel penuh.
- Screenshot verifikasi email sudah diperbarui agar header dan footer menampilkan logo Unila + FKIP Unila lengkap seperti tampilan lain.
- Tap pointer sudah diaktifkan kembali sebagai marker panah kecil non-lingkaran yang muncul hanya pada frame ketukan, dengan posisi berdasarkan pusat elemen yang sedang ditekan. Geraknya sekarang dibuat lebih rapat per frame agar tidak terasa patah.
- Tap pointer dan panah scroll sekarang dirender sebagai SVG image tunggal agar lebih ringan dan lebih halus di export video.
- Preview foto profil memakai avatar abu-abu dengan siluet putih seperti referensi.
- Setiap frame slide sudah diberi transisi fade cepat dan auto advance timing untuk kebutuhan export video.
- Tap pointer dan panah scroll sekarang memakai langkah animasi yang lebih rapat dan stabil per frame, dengan render image SVG untuk mengurangi patah visual.
- Timing aksi sudah dipadatkan, frame input dan pilihan diberi jeda perpindahan lalu frame stabil agar tidak terjadi ghosting full-slide.
- Perpindahan scroll diberi frame perantara yang bersih tanpa overlay transparan agar geraknya terasa lebih natural saat diekspor menjadi video.

## Cara Regenerasi

Jalankan dari root project:

```powershell
node "docs\video tutorial\ppt-mobile-video-02\ppt-source\create-ppt-mobile-video-02-recordlike.mjs"
```

## Validasi Terakhir

- File PPTX berhasil dibuat.
- Struktur PPTX clean-pointer berisi 277 slide.
- Media di dalam PPTX: 656 file.
- Semua frame slide memiliki transition fade cepat dan auto advance timing.
- Durasi auto advance berada pada rentang 120-1012 ms dengan total durasi frame sekitar 52,15 detik sebelum audio voice over dimasukkan.
- Semua XML part valid.
- Tap pointer non-lingkaran muncul pada 161 frame ketukan dan koordinatnya divalidasi agar berada di dalam area highlight target.
- Arrow scroll muncul pada 189 frame.
- Tidak ada shape ellipse/ripple circle pada PPT versi clean-pointer.
- Screenshot input detail 17 sampai 35 berukuran 954 x 1920 px.
- Preview audit highlight tersimpan di `highlight-audit/highlight-audit-updated.png`.
- Preview audit tap indicator terbaru tersimpan di `highlight-audit/tap-audit-updated.png`.

## Catatan Lanjutan

- PPT record-like adalah versi utama untuk export video.
- Efek animasi dibuat sebagai rangkaian frame slide bertiming cepat agar stabil dan tidak memicu repair PowerPoint.
- Efek fade cepat dan delay antar frame sudah dimasukkan sebagai transition dan auto advance timing.
- Pointer dan panah scroll tidak lagi memakai shape berlapis; keduanya sudah dirender sebagai SVG image tunggal untuk mengurangi lag visual.
- Tap pointer, highlight presisi, dan arah scroll sudah dimasukkan sebagai elemen visual pada frame.
- Audio voice over belum dimasukkan.
- Setelah animasi dan audio selesai, file dapat diekspor ke video dari PowerPoint.
