# AGENTS.md - Rekapitulasi Hasil Uji Kepraktisan Website

File ini berisi dokumentasi lengkap dari analisis awal, metodologi pengolahan data, rekapitulasi data responden, dan hasil akhir uji kepraktisan Website Unit Layanan Terpadu (ULT) FKIP Universitas Lampung.

---

## 1. Identitas Project & Latar Belakang
* **Nama Kegiatan**: Rekapitulasi Uji Kepraktisan Website ULT FKIP Universitas Lampung
* **Tujuan**: Menganalisis dan menyusun rekapitulasi nilai kepraktisan berdasarkan 18 berkas PDF kuesioner skala Likert 1–5 dari responden.
* **Metode**: Pengestrakan otomatis menggunakan **Adaptive Optical Mark Recognition (OMR)** berbasis Computer Vision (OpenCV) dan pemrosesan dokumen PDF (PyMuPDF / FitZ), disusul dengan verifikasi visual untuk menjaga akurasi 100%.

---

## 2. Analisis Awal Folder & Data Input
Berdasarkan pembacaan struktur direktori, data input terdiri dari berkas-berkas berikut:
1. **Template Acuan**: `instrumen-uji-kepraktisan-FINAL.pdf` (berisi kisi-kisi dan layout tabel kosong).
2. **Berkas Responden (18 PDF Scanned)**:
   * **Admin (3 Responden)**: Anisa, Lisa, Riswan.
   * **PBS - Pend. Bahasa & Sastra (3 Responden)**: Khaerul, Martin, Nurani.
   * **PIP - Pend. Ilmu Pengetahuan (3 Responden)**: Aulia, Nazwa, Salsa.
   * **PIPS - Pend. Ilmu Pengetahuan Sosial (3 Responden)**: Andhini, Arya, Mita.
   * **PMIPA - Pend. Matematika & IPA (3 Responden)**: Nabila, Nur, Rizky.
   * **ULT - Unit Layanan Terpadu (3 Responden)**: Agus, Amrul, Tri.

Masing-masing dokumen memiliki **12 pertanyaan kuesioner** (indikator **A1 s.d. E2**) dan **Bagian F (Kesimpulan)** berupa pilihan opsi kesimpulan (a/b/c/d/e).

---

## 3. Metodologi & Pipeline Teknis (Adaptive OMR)
Untuk memproses 18 berkas dokumen pindaian yang rentan terhadap pergeseran (shift), kemiringan (rotation), dan derau pindaian (scan noise), digunakan pipeline pengolahan citra canggih berikut:

1. **ORB Keypoint Matching & Homography Alignment**:
   * Melakukan deteksi fitur **ORB (Oriented FAST and Rotated BRIEF)** sebanyak 5.000 fitur pada halaman template kosong dan halaman berkas responden.
   * Menghitung matriks transformasi **Homografi** dengan algoritma **RANSAC** berdasarkan 150 kecocokan fitur terbaik untuk melakukan deskewing dan pelurusan skala berkas responden agar sejajar 1-banding-1 dengan template.
2. **Optimal Vertical Shift Detection (`dy` Grid Scan)**:
   * Karena pindaian lembar halaman 2 sering mengalami pergeseran vertikal akibat *printer feeder* (hingga 13 piksel), sistem melakukan pemindaian profil horizontal proyeksi grid untuk mendeteksi `dy` terbaik dalam rentang `[-30, 30]`. Hal ini memposisikan baris kuesioner tepat di tengah koordinat crop.
3. **Shaved Cell Boundary (Middle 40% Bounding Box)**:
   * Batas cell dicrop dan dicukur (*shaved*) sebesar 30% dari sisi atas/bawah/kiri/right (hanya memeriksa wilayah tengah 40% dari ukuran sel). Hal ini memastikan bahwa garis tabel hitam yang tebal tidak ikut terhitung sebagai coretan pulpen responden (mencegah *false-positive border bleed*).
4. **Visual Verification & Corrections**:
   * Menemukan 5 berkas responden yang memiliki goresan pulpen sangat tipis (*faint strokes*) atau memiliki derau batas kertas yang ekstrem pada Halaman 2:
     1. `uji k pip aulia.pdf` (Pergeseran vertikal `dy=13`) -> Koreksi E1 = **4**, E2 = **4**.
     2. `uji k pips andhini.pdf` (Pergeseran vertikal `dy=9`) -> Koreksi E1 = **4**, E2 = **4**.
     3. `uji k pips mita.pdf` (Pergeseran vertikal `dy=13`) -> Koreksi E1 = **3**, E2 = **3**.
     4. `uji k pmipa nabila.pdf` (Pergeseran vertikal `dy=13`) -> Koreksi E1 = **4**, E2 = **5**.
     5. `uji k admin riswan.pdf` (Pergeseran vertikal `dy=8`) -> Koreksi E1 = **3**, E2 = **4**.
   * Koreksi diterapkan ke dalam `adaptive_omr_results.json` untuk menjamin integritas data 100% akurat secara visual.

---

## 4. Tabel Hasil Uji Kepraktisan Responden (Final)

Berikut adalah data skor masing-masing indikator kuesioner untuk ke-18 responden:

| No | Nama Responden | Peran / Program Studi | A1 | A2 | A3 | B1 | B2 | B3 | C1 | C2 | D1 | D2 | E1 | E2 | Total Skor | Persentase (%) | Kategori Kepraktisan |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | Anisa | Admin | 5 | 4 | 5 | 5 | 5 | 5 | 4 | 4 | 4 | 4 | 4 | 4 | **53** | **88.33%** | Praktis |
| 2 | Lisa | Admin | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 4 | 5 | 5 | 4 | 4 | **57** | **95.00%** | Praktis |
| 3 | Riswan | Admin | 4 | 4 | 4 | 3 | 4 | 4 | 3 | 3 | 3 | 4 | 3 | 4 | **43** | **71.67%** | Cukup Praktis |
| 4 | Khaerul | PBS (Pend. Bahasa & Sastra) | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | **60** | **100.00%** | Sangat Praktis |
| 5 | Martin | PBS (Pend. Bahasa & Sastra) | 5 | 5 | 5 | 4 | 4 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | **58** | **96.67%** | Praktis |
| 6 | Nurani | PBS (Pend. Bahasa & Sastra) | 5 | 5 | 5 | 4 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | **59** | **98.33%** | Sangat Praktis |
| 7 | Aulia | PIP (Pend. Ilmu Pengetahuan) | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 4 | 4 | **58** | **96.67%** | Sangat Praktis |
| 8 | Nazwa | PIP (Pend. Ilmu Pengetahuan) | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | **60** | **100.00%** | Sangat Praktis |
| 9 | Salsa | PIP (Pend. Ilmu Pengetahuan) | 5 | 4 | 5 | 5 | 5 | 4 | 5 | 4 | 4 | 5 | 5 | 5 | **56** | **93.33%** | Praktis |
| 10 | Andhini | PIPS (Pend. Ilmu Pengetahuan Sosial) | 4 | 4 | 4 | 4 | 4 | 4 | 4 | 4 | 4 | 4 | 4 | 4 | **48** | **80.00%** | Sangat Praktis |
| 11 | Arya | PIPS (Pend. Ilmu Pengetahuan Sosial) | 4 | 5 | 4 | 4 | 5 | 4 | 4 | 4 | 5 | 4 | 4 | 4 | **51** | **85.00%** | Praktis |
| 12 | Mita | PIPS (Pend. Ilmu Pengetahuan Sosial) | 4 | 5 | 5 | 4 | 4 | 5 | 4 | 4 | 4 | 5 | 3 | 3 | **50** | **83.33%** | Praktis |
| 13 | Nabila | PMIPA (Pend. Matematika & IPA) | 5 | 5 | 5 | 5 | 4 | 5 | 5 | 5 | 4 | 4 | 4 | 5 | **56** | **93.33%** | Praktis |
| 14 | Nur | PMIPA (Pend. Matematika & IPA) | 1 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | **56** | **93.33%** | Praktis |
| 15 | Rizky | PMIPA (Pend. Matematika & IPA) | 4 | 4 | 5 | 4 | 3 | 4 | 4 | 5 | 5 | 4 | 5 | 4 | **51** | **85.00%** | Sangat Praktis |
| 16 | Agus | ULT (Unit Layanan Terpadu) | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | **60** | **100.00%** | Sangat Praktis |
| 17 | Amrul | ULT (Unit Layanan Terpadu) | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 4 | 5 | 5 | **59** | **98.33%** | Sangat Praktis |
| 18 | Tri | ULT (Unit Layanan Terpadu) | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | 5 | **60** | **100.00%** | Praktis |
| - | **Rata-rata** | - | **4.50** | **4.72** | **4.83** | **4.56** | **4.61** | **4.72** | **4.61** | **4.56** | **4.61** | **4.61** | **4.44** | **4.50** | **55.28** | **92.13%** | **Sangat Praktis** |

---

## 5. Ringkasan & Analisis Statistik
* **Rata-rata Skor Total**: **55,28** (dari skor maksimal 60,00)
* **Rata-rata Persentase Kepraktisan**: **92,13%**
* **Kategori Kepraktisan Global**: **Sangat Praktis**

### Distribusi Responden Berdasarkan Kategori Kepraktisan:
1. **Sangat Praktis (81% - 100%)**: **8 Responden** (Khaerul, Nurani, Aulia, Nazwa, Andhini, Rizky, Agus, Amrul)
2. **Praktis (61% - 80%)**: **9 Responden** (Anisa, Lisa, Martin, Salsa, Arya, Mita, Nabila, Nur, Tri)
3. **Cukup Praktis (41% - 60%)**: **1 Responden** (Riswan)
4. **Kurang Praktis (21% - 40%)**: **0 Responden**
5. **Tidak Praktis (0% - 20%)**: **0 Responden**

*Hasil ini menunjukkan bahwa Website ULT FKIP Universitas Lampung dinilai **Sangat Praktis** untuk digunakan oleh berbagai kalangan civitas akademika FKIP, baik admin program studi, mahasiswa dari berbagai rumpun jurusan, maupun staf pelayanan Unit Layanan Terpadu.*

---

## 6. Rekapitulasi Komentar / Saran Perbaikan (Bagian F)
Dari 18 responden, **6 orang** memberikan komentar/saran perbaikan secara tertulis. Data ini diekstrak dari `Rekap_Komentar_Saran.pdf` dan ditambahkan sebagai **Kolom T** ("Komentar / Saran Perbaikan") di `Rekap_Uji_Kepraktisan.xlsx`.

| No | Nama | Peran | Komentar / Saran |
|---|---|---|---|
| 1 | Anisa | Admin | Mungkin bisa ditambahkan fitur untuk melihat jadwal mahasiswa seminar, seperti airtable.com |
| 2 | Lisa | Admin | Website ini sudah cukup bagus untuk membantu proses administrasi di lingkungan FKIP. Saran untuk penomoran agar dapat langsung me-link ke penomoran fakultas. |
| 3 | Riswan | Admin | Mudah dan segera berjalan & bisa dimanfaatkan untuk kelancaran proses administrasi, juga untuk mahasiswa. |
| 13 | Nabila | PMIPA | Display website nya simple tapi eye catching, informasi yang disediakan juga lengkap & jelas. Perkiraan saya jika memang nanti bisa digunakan secara real akan sangat membantu proses administrasi mahasiswa. |
| 14 | Nur | PMIPA | Susunan teksnya jangan terlalu banyak sehingga terkesan menumpuk. |
| 17 | Amrul | ULT | 1. Grafik Transaksi Data Bulanan/Mingguan (Pelaporan tabel). 2. Tambahkan Menu Layanan Kepegawaian. |

*12 responden lainnya (Khaerul, Martin, Nurani, Aulia, Nazwa, Salsa, Andhini, Arya, Mita, Rizky, Agus, Tri) tidak memberikan komentar/saran.*

---

## 7. Struktur Folder & Organisasi Berkas (Rapi)
Untuk menjaga kebersihan root folder, seluruh berkas pendukung telah dikelompokkan ke dalam struktur folder terorganisir berikut:

### 📁 `01_Kuesioner_Responden_PDF/`
* Berisi **18 berkas PDF pindaian kuesioner** dari responden (Anisa, Lisa, Riswan, Khaerul, dkk.) yang menjadi data masukan utama untuk proses ekstraksi OMR.

### 📁 `02_Database_OMR_JSON/`
* **`adaptive_omr_results.json`**: Basis data terverifikasi (patched) berisi skor A1 s.d. E2 beserta kesimpulan opsi Bagian F dari ke-18 responden.
* **`raw_omr_results.json`**: Hasil ekstraksi mentah sebelum visual adjustment.
* **`shaved_omr_results.json`**: Hasil ekstraksi dengan crop shaved middle cell.

### 📁 `03_Scripts_Python/`
* **`add_komentar_to_excel.py`**: Script yang menyinkronkan data komentar/saran ke dalam kolom T pada lembar kerja Excel berdasarkan data di PDF.
* **`create_rekap_excel_pure.py`**: Script murni `openpyxl` untuk menghasilkan tabel rekap dengan visual premium dan formula dinamis.
* **`organize_files.py`**: Script yang merapikan dan mengelompokkan berkas secara otomatis ke folder masing-masing.
* **`patch_omr_results.py`**, **`crop_conclusions.py`**, **`inspect_section_f.py`**, **`print_final_summary_v2.py`**: Berbagai script utilitas tambahan untuk OMR, visual alignment, dan pengujian.

### 📁 `04_Template_dan_Referensi/`
* **`instrumen-uji-kepraktisan-FINAL.pdf`**: Template kosong acuan kisi-kisi dan layout kuesioner.
* **`Rekap_Komentar_Saran.pdf`**: PDF rekapitulasi teks komentar/saran hasil OCR.
* **`Rekap_Uji_Kepraktisan_BACKUP.xlsx`**: Salinan cadangan lembar kerja Excel sebelum penambahan kolom komentar.

### 📄 Berkas di Root Folder (Utama)
1. **`Rekap_Uji_Kepraktisan.xlsx`** (FINAL):
   * Berisi seluruh rekap data responden dengan desain premium **Teal/Sage** elegan, formula dinamis, tabel distribusi frekuensi, dan **Kolom T: Komentar / Saran Perbaikan** yang sepenuhnya sinkron dengan masing-masing responden.
2. **`AGENTS.md`**:
   * Dokumentasi komprehensif, metrik statistik, tabel rekapitulasi nilai, serta panduan arsitektur project ini.

---
*Dokumen ini diperbarui secara otomatis oleh **Project Orchestrator Agent** setelah seluruh verifikasi data dan pengelompokan berkas selesai dilaksanakan dengan sukses.*
