# Catatan Produksi PPT Mobile Video 4 (Draft)

## Konsep File PPT
- Nama File Target: `ppt-source/video-04-status-revisi-unduh-mobile-clean-pointer.pptx`
- Ukuran slide: 954 x 1920 px.
- Format: portrait mobile.
- Engine: Node.js dengan `PptxGenJS` (mengikuti standar Video 02 & Video 03).

## Pembaruan Arsitektur Generator untuk Video 04
Berbeda dengan Video 02 dan 03 yang alurnya linier, Video 04 memiliki alur yang meloncat antar status permohonan. Oleh karena itu, script generator `create-ppt-mobile-video-04-recordlike.mjs` (yang akan dibuat) harus mengakomodasi:

1. **Overlay Notifikasi OS Dinamis:**
   Script perlu menambahkan *shape* atau gambar overlay notifikasi "File downloaded" pada scene-scene unduh hasil. Posisi notifikasi harus fixed di atas (top/center) dan bisa dianimasikan fade.

2. **Dinamisasi Teks Catatan Petugas:**
   Untuk menghemat pengambilan *screenshot*, kita bisa menggunakan satu screenshot detail permohonan kosong, lalu menambahkan *Shape Box* kuning/merah muda berisi "Catatan Petugas" langsung menggunakan kode `slide.addShape()` di PPT. Ini membuat revisi teks lebih mudah di masa depan tanpa harus menangkap layar ulang.

3. **SVG Pointers (Tetap Dipertahankan):**
   - Tap pointer SVG non-lingkaran (`tap-pointer.svg`) dan panah scroll (`arrow-scroll.svg`) wajib digunakan kembali untuk menjaga konsistensi visual seperti rekaman *screencast* asli.

4. **Transisi "Timeline":**
   Scene di mana *voice over* membacakan berbagai macam status secara berurutan akan diwujudkan dengan *slide* berisi *screenshot* `01-riwayat-permohonan-list.png` yang perlahan "di-pan" atau disimulasikan nge-*scroll*, lalu panah SVG menunjuk dari satu *badge* ke *badge* lainnya tanpa ganti layar. Hal ini menghemat *frame* secara signifikan.

## Validasi Kelak Saat Diekspor
- Pastikan semua slide menggunakan transition `fade` dengan parameter kecepatan `fast` agar pergantian frame terasa sebagai satu video utuh.
- Audio *voice over* akan diimpor terpisah setelah PPTX bersih selesai dirender.
- Tidak ada deklarasi `slideMaster` yang bermasalah agar file tidak diklaim "corrupt" atau minta repair oleh Microsoft PowerPoint.

## Cara Kerja Selanjutnya
1. Pastikan semua gambar pada `asset-capture-plan.md` terkumpul di folder `screenshots/`.
2. Buat script `create-ppt-mobile-video-04-recordlike.mjs` dengan referensi koordinat pixel dari gambar.
3. Jalankan `node create-ppt-mobile-video-04-recordlike.mjs`.
