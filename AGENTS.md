# Panduan Eksekusi Agent - Web ULT FKIP Unila Fullpower

## 1. Identitas Project
- **Nama Project**: Web ULT FKIP Universitas Lampung — Fullpower
- **Tipe Project**: Aplikasi Web Monolitik
- **Status Project**: Tahap Pengembangan (Production-ready template)

## 2. Tech Stack
- **Backend**: Laravel 12, PHP 8.4+
- **Frontend**: Blade Templating, TailwindCSS, Alpine.js
- **Build Tool**: Vite
- **Database**: MySQL (dev/prod), SQLite (testing)
- **Otentikasi & Otorisasi**: Laravel Breeze (Blade) + Spatie Laravel Permission
- **Dokumen Generator**: `phpoffice/phpword` & `dompdf/dompdf`
- **CMS Editor**: Tiptap Editor
- **Fokus Tambahan**: Progressive Web App (PWA), Audit Trail, Private Local Storage untuk dokumen sensitif, Notifikasi.

- **Kendala & Solusi**: Karena query default langsung memakai metode `pluck('c', 'current_status')`, data langsung ter-render mentah. Solusinya, mapping array dan grouping dilakukan di level Controller PHP sebelum di-inject ke Blade UI, karena modifikasi di query level (SQL) akan jauh lebih kompleks dan tidak sejalan dengan implementasi enum internal saat ini.

## 3. Struktur Modul Utama
- **Public Portal**: Halaman depan (Landing page, Katalog Layanan, Blog, Pengumuman, dan CMS Publik).
- **Student Portal**: Dasbor Mahasiswa untuk mengajukan permohonan layanan, melacak riwayat (timeline auditable), notifikasi, dan mengunduh output dokumen yang sifatnya privat.
- **Admin/Staff Portal**: Dasbor Operasional untuk manajemen permohonan (Workflow Gatekeeper, TTD Fakultas/Jurusan, dll), Manajemen Layanan & Template Dokumen, dan CMS Manager.
- **Signer Portal**: Antarmuka untuk proses verifikasi dan penyetujuan/tanda tangan elektronik sebelum dokumen dirakit (assemble) oleh staf.

## 4. Alur Kerja (Workflow) Permohonan Dokumen
1. **Pengajuan (Student)**: Mahasiswa mengajukan permintaan melalui portal, mengisi field dinamis sesuai layanan, dan melampirkan berkas (privat).
2. **Review ULT (Gatekeeper)**: Staf ULT menerima permohonan dan melakukan validasi kelayakan.
3. **Persetujuan & Tanda Tangan**: Jika butuh TTD tingkat Fakultas/Jurusan/Prodi, permohonan dialihkan kepada Pejabat/Approver.
4. **Penomoran Surat (Issue Number)**: Generate secara otomatis sesuai format unit atau fakultas.
5. **Assembly**: Perakitan dokumen Word (docx) berdasarkan Template + Data Form/Placeholder Mahasiswa, dikonversi menjadi file siap unduh (PDF/Docx).
6. **Selesai**: Mahasiswa dapat mengunduh dokumen secara privat.

## 5. Aturan Pengerjaan (Agent Guidelines)
1. **Terapkan Analisis Konteks**: Pastikan tidak ada aksi modifikasi yang mengganggu transaksi/alur `RequestAdminController` tanpa pemahaman mendalam tentang policy dan gates-nya.
2. **Standardisasi Keamanan**: Semua operasi upload dan unduh output *wajib* melalui private disk dan middleware otorisasi (anti-IDOR).
3. **UI/UX Konsistensi**: Gunakan layout dashboard yang sudah diadaptasi (Blade + TailwindCSS + Alpine.js). Jangan memuat framework luar seperti React/Vue.
4. **Hindari Hardcode Routing**: Selalu manfaatkan helper fungsi seperti `route()` atau URL Generator Laravel di file `.blade.php`.
5. **Pengujian (Testing)**: Jika menambah module layanan baru, tulis logic test di framework PHPUnit (`php artisan test`) yang mencakup alur status dari draft hingga selesai.
6. **Jangan Menduplikasi Dokumen Update Skripsi (Crucial)**: Saat melakukan pembaruan naskah skripsi (seperti revisi Bab IV, V, dll), **jangan pernah menduplikasi file** atau membuat file dokumen baru dengan akhiran/suffix tambahan (contoh: `_RevisiADDIE_Clean.docx` atau `_Format_Clean.docx`). Selalu perbarui secara langsung (overwrite secara aman) dokumen utama yang sudah ada di folder `hasil_update` (yaitu `001_Skripsi_Andricha Dea Mitra_Clean.docx` dan `001_Skripsi_Andricha Dea Mitra_Highlighted.docx`). Hal ini penting untuk menjaga agar riwayat integrasi naskah terpusat pada satu file utama tanpa penumpukan file duplikat.


---

## 6. Catatan Perbaikan Global Render WYSIWYG HTML pada Preview & Unduhan Dokumen
- **Masalah**: Input teks yang berasal dari WYSIWYG editor (Tiptap Editor) pada form mahasiswa menyimpan tag-tag HTML (seperti `<p>`, `<b>`, `<i>`, `<u>`, `<br>`, `<ul>`, `<ol>`, `<li>`). Saat proses perakitan dokumen Word (`.docx`) dan PDF, tag HTML tersebut tercetak mentah sebagai plain text (contoh: `<p><b>Skripsi</b></p>`) tanpa efek formatting visual asli.
- **Solusi Web Preview**:
  - Halaman pratinjau data permohonan pada portal Student (`resources/views/student/requests/show.blade.php`) dan portal Admin (`resources/views/admin/requests/show.blade.php`) merender field tipe `richtext` menggunakan sintaks `{!! app(\App\Services\HtmlSanitizer::class)->clean(...) !!}`. Metode ini memastikan tag HTML visual aktif dengan aman karena sudah melewati proses sanitasi XSS yang ketat.
- **Solusi Unduhan Dokumen & Template Office (Global)**:
  - Telah diimplementasikan parser HTML-to-OpenXML dinamis terintegrasi di `DocumentAssemblerService.php` melalui metode `writeHtmlToWordRun()` dan `traverseHtmlAndInsertRuns()`.
  - Logika parser bekerja secara terpusat pada file XML mentah (`document.xml`, `header*.xml`, `footer*.xml`, dll) sebelum file DOCX dikemas ulang:
    - **Tag Inline**: Tag `<b>`, `<strong>`, `<i>`, `em`, dan `<u>` diubah menjadi elemen formatting OpenXML (`<w:b/>`, `<w:i/>`, `<w:u w:val="single"/>`) di dalam segmen run.
    - **Tag Break/Blok**: Tag `<br>`, `<p>`, dan `<div>` dipetakan menggunakan pemutus baris dinamis `<w:br/>` agar format tetap rapi dan tidak merusak layout paragraf orisinal (terutama jika berada dalam sel tabel).
    - **Tag List**: Tag `<ul>` dan `<ol>` dipetakan dengan penanda list bullet (`• `) atau penomoran list dinamis (`1. `, `2. `, dst.) pada setiap tag `<li>`.
    - **Preservasi Style (Zero Layout Degradation)**: Rangkaian segmen run baru hasil parse HTML dikloning dari run asli placeholder (`<w:r>`). Ini menjamin visual teks hasil parsing WYSIWYG tetap mewarisi secara sempurna gaya font (font-family, size, color) orisinal bawaan template Word.
- **Hasil Pengujian**:
  - Logika parser HTML-to-OpenXML telah divalidasi penuh menggunakan Targeted Unit Test di `tests/Unit/Services/DocumentAssemblerHtmlParserTest.php` yang mencakup deteksi HTML, parsing tag visual (`b`, `i`, `u`), kloning run, dan penelusuran DOM XML. Hasil test berjalan 100% sukses dan hijau (*PASS*).

---

Dokumen ini wajib dijadikan acuan bagi semua Agent yang bekerja pada repositori ini sebelum merancang solusi atau mengeksekusi *command* modifikasi file.

---

## 7. Dokumentasi Penulisan Skripsi (Bab III, IV, dan V)

### A. Analisis Awal (SOP Langkah 1)
Kami telah memindai direktori proyek dan dokumen di komputer pengguna. File-file berikut telah berhasil dibaca dan dianalisis untuk mendukung penyelesaian naskah skripsi:

1. **Draf Skripsi Utama**:
   - Path: `C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx`
   - Ukuran: 7,26 MB
   - Status: Berisi draf Bab I s.d. Bab V (Lengkap).
2. **File Pedoman Akademik**:
   - Path: `C:\laragon\www\ult-fkip-unila\docs\skripsi\Panduan-Penulisan-Karya-Ilmiah-2020.pdf`
   - Ukuran: 7,98 MB (76 halaman)
   - Status: Digunakan sebagai acuan margin (4-4-3-3), ukuran font (Times New Roman 12 pt), spasi teks utama (1.5), spasi tabel/gambar/kutipan/daftar pustaka (tunggal), format penomoran judul tabel (di atas tanpa titik) dan gambar (di bawah dengan titik), serta sitasi.
3. **Data Empiris Validitas Ahli**:
   - Path: `C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil uji ahli validator\Rekap_Uji_Validitas_Ahli.xlsx`
   - Ukuran: 21 KB (Sheets: 'Rekap Keseluruhan', 'Validitas Materi', 'Validitas Media', 'Validitas Sistem')
   - Status: Data kuantitatif 9 validator ahli (3 ahli materi, 3 ahli media, 3 ahli sistem) beserta komentar dan kelayakannya telah diekstrak dengan akurasi 100%.
4. **Data Empiris Kepraktisan Pengguna**:
   - Path: `C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil uji kepraktisan\Rekap_Uji_Kepraktisan.xlsx`
   - Ukuran: 13 KB (Sheet: 'Rekap Uji Kepraktisan')
   - Status: Data kuantitatif 18 responden (12 mahasiswa, 3 admin prodi, 3 staf ULT) × 12 butir instrumen kepraktisan beserta kuesioner kualitatif bagian F (saran perbaikan) telah diekstrak secara lengkap.

### B. Rencana Kerja (Work Plan)
1. **Pembaruan Bab III (Metode Penelitian)**:
   - Mengintegrasikan sub-bab **Subjek Penelitian** yang merinci subjek validator ahli (9 orang) dan responden uji coba (18 orang).
   - Menjelaskan secara komprehensif penggunaan teknik pengambilan sampel **purposive sampling** (Sugiyono, 2013) serta kriteria pemilihannya.
   - Menyesuaikan jumlah butir kisi-kisi dan skor maksimal agar selaras dengan data Bab IV.
   - Menambahkan teknik analisis data **Aiken's V** beserta rumusnya.
2. **Penyusunan Bab IV (Hasil Penelitian dan Pembahasan)**:
   - Menyajikan narasi deskriptif dan tabel kelayakan (Materi: 95,45%, Media: 93,33%, Sistem: 87,58%, Rerata: 91,95% - Sangat Valid) dengan Aiken's V (Materi: 0,94, Media: 0,92, Sistem: 0,85).
   - Menyajikan rekapitulasi data kepraktisan 18 responden × 12 butir (Rerata skor: 55,28/60,00 atau 92,13% - Sangat Praktis).
   - Menyajikan rekap komentar kualitatif dari 6 responden dan tindak lanjut perbaikan sistem oleh peneliti.
   - Menyusun pembahasan komprehensif keselarasan dengan teori R&D (ADDIE).
3. **Penyusunan Bab V (Kesimpulan dan Saran)**:
   - Menyusun simpulan padat dan komprehensif sebagai jawaban langsung atas rumusan masalah berdasarkan data Bab IV.
   - Menyusun saran operasional dan saran pengembangan lanjut (integrasi SSO Unila, perluasan layanan kepegawaian, CSP).
4. **Output Dokumen Akhir**:
   - Menghasilkan file `C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Format_Clean.docx` (bersih dan rapi).
   - Menghasilkan file `C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Format_Highlighted.docx` (setiap paragraf, kalimat, atau data baru ditandai stabilo kuning).

## 8. Hasil Deep Dive Panduan Penulisan Karya Ilmiah Unila 2020

Berdasarkan analisis menyeluruh terhadap file `Panduan-Penulisan-Karya-Ilmiah-2020 (1).pdf`, berikut aturan layout dan formatting resmi yang harus diimplementasikan:

### A. Margin (Sembir)
- **Sembir Atas**: 3 cm (1,2 inci)
- **Sembir Bawah**: 3 cm (1,2 inci)
- **Sembir Kanan**: 3 cm (1,2 inci) (Catatan: Sembir kanan tidak perlu rata / Left Align diperbolehkan dan digunakan sesuai instruksi)
- **Sembir Kiri**: 4 cm (1,6 inci) untuk ruang penjilidan.
- **Tabel & Gambar**: Harus berada dalam sembir (tidak boleh keluar margin).

### B. Ukuran Font dan Jenis Huruf
- **Teks Utama**: Times New Roman 12 pt.
- **Isi Tabel & Gambar**: Times New Roman 10 pt atau 11 pt jika diperlukan (untuk isian tabel/gambar).
- **Istilah Asing**: Ditulis dengan huruf miring (*italic*).

### C. Jarak Baris (Spasi)
- **Teks Utama**: 1.5 spasi (1.5 line spacing).
- **Spasi Tunggal (1.0 spasi)**:
  - Judul Bab dan judul Subbab
  - Kutipan langsung panjang (lebih dari 3 baris)
  - Judul tabel dan judul gambar
  - Isi tabel/bagan
  - Daftar pustaka (jarak di dalam satu entri sumber pustaka)
  - Abstrak
- **Spasi Ganda (2.0 spasi)**:
  - Antar entri sumber pustaka di Daftar Pustaka.
- **Spasi Tripel (3.0 spasi)**:
  - Jarak antar tabel, antar gambar.
  - Jarak antara tabel dengan naskah (teks sebelum/sesudah), dan gambar dengan naskah.
- **Spasi Empat (4.0 spasi)**:
  - Jarak antara Judul Bab dengan Subbab di bawahnya atau baris pertama naskah.

### D. Layout Judul Bab & Subbab
- **Awal Bab**: Setiap Bab baru wajib dimulai pada halaman baru (*Page Break*).
- **Judul Bab**:
  - Diketik seluruhnya dengan huruf kapital, tebal (bold), di tengah-tengah kertas (Center).
  - Jarak dari pinggir atas kertas ke judul bab adalah 6 cm (dapat dimanipulasi dengan spacing before di Word).
  - Menggunakan format angka Romawi secara konsisten (contoh: BAB I, BAB II, BAB III, BAB IV, BAB V).
  - Jarak judul bab dengan awal teks/subbab adalah 4 spasi (spasi empat).
- **Judul Subbab & Sub-subbab**:
  - Diketik menggunakan huruf kapital pada setiap awal kata kecuali kata hubung (Title Case).
  - Jika judul subbab lebih dari satu baris, diketik dengan spasi tunggal.
  - Subbab di bagian bawah halaman harus diikuti minimal 2 baris penuh teks paragraf di bawahnya; jika tidak muat, harus dipindah ke halaman berikutnya.

### E. Penomoran Halaman
- **Letak Nomor**: Samping kanan, 1 spasi di atas margin atas, berjarak 3 cm dari pinggir kanan kertas.
- **Halaman Bab Baru**: Nomor halaman yang memuat judul utama (Bab baru) tidak dicantumkan (disembunyikan) tetapi tetap dihitung.
- **Penomoran Awal**: Halaman pemula (Prakata, Daftar Isi, Daftar Tabel, dll.) diberi nomor halaman angka Romawi kecil (i, ii, iii, dst.) diletakkan di samping kanan atas.
- **Penomoran Isi**: Halaman Pendahuluan (Bab I) sampai Daftar Pustaka dan Lampiran diberi nomor angka Arab (1, 2, 3, dst.) dimulai dari angka 1.

## 9. Laporan Audit Refinement & Formatting Skripsi (Mei 2026)

Berdasarkan komparasi detail antara draf hasil format sebelumnya dengan file baseline orisinal `docs/skripsi/update skripsi terakhir-seminar proposal/001_Skripsi_Andricha Dea Mitra.docx`, berikut hasil audit dan rencana aksi perbaikan:

### A. Kesalahan Teridentifikasi & Solusi Layout
1. **Penyimpangan Alignment (Rata Kiri Berlebih):**
   - **Kesalahan**: Hasil format sebelumnya mengubah alignment 39 paragraf yang seharusnya Rata Tengah (Center) menjadi Rata Kiri (Left). Ini merusak visualisasi halaman Cover (Halaman Judul), Judul Gambar (Gambar 2 dan Gambar 3), Sumber Tabel/Gambar, dan rumus matematika di Bab III.
   - **Solusi**: Mempertahankan alignment orisinal dari baseline (Center/Right). Rata Kiri (Left) hanya diterapkan pada naskah isi (Normal) yang sebelumnya berformat Justify atau default (None).
2. **Page Break Ganda & Halaman Kosong:**
   - **Kesalahan**: Ditemukan 12 paragraf kosong yang berisi page break manual (`w:br w:type="page"`). Karena judul bab (Heading 1) disetel memiliki properti `page_break_before = True`, terjadi page break ganda yang memicu terbentuknya halaman-halaman kosong di awal bab.
   - **Solusi**: Mengikis elemen page break manual (`w:br[@w:type="page"]`) dari paragraf-paragraf kosong tersebut agar hanya mengandalkan pemecah halaman otomatis dari properti Heading 1.
3. **Pewarisan Huruf Miring (Italics) pada Tabel:**
   - **Kesalahan**: Teks di dalam tabel (seperti Tabel 2.4) ter-render miring secara keseluruhan di MS Word akibat mewarisi properti kemiringan dari style tabel bawaan.
   - **Solusi**: Mengatur properti `run.font.italic = False` secara eksplisit pada seluruh run di dalam tabel yang tidak bernilai `True` secara orisinal (bukan istilah asing). Ini memaksa kata-kata Bahasa Indonesia tetap tegak (Regular).

### B. Proteksi Keamanan Mendeley & Word Equation
- **Sitasi Mendeley:** Jumlah field codes Mendeley (`w:fldSimple` & `w:instrText`) terverifikasi aman sebanyak 190 elemen. Script dilarang menulis ulang properti teks (`p.text = ...`) pada paragraf isi naskah agar metadata Mendeley tidak rusak.
- **Word Equation:** Jumlah elemen equation (`m:oMath`) terverifikasi utuh sebanyak 8 elemen. Paragraf yang mengandung equation diproteksi dan dilewati dari modifikasi run langsung.

### C. Standardisasi Spasi & Letak Bab Baru (Unila 2020)
- **Top Margin Halaman Bab Baru:** Judul bab diketik 6 cm dari tepi atas kertas. Dengan margin atas normal 3 cm, Spacing Before judul bab disetel tepat **85 pt** (~3 cm).
- **Jarak Bawah Judul Bab:** Jarak judul bab ke teks/subbab di bawahnya adalah 4 spasi (spasi tunggal), disetel menggunakan Spacing After sebesar **48 pt** (4 * 12 pt).
- **Jarak Baris:** Naskah utama 1.5 spasi. Judul bab, subbab, abstrak, tabel, dan daftar pustaka menggunakan 1.0 spasi (tunggal).

### D. Restrukturisasi Tahap Desain (Bab IV) & Resolusi Isu Gambar
- **Restrukturisasi Teks**: Pada bagian **4.1.2 Tahap Desain**, format penjelasan yang tadinya kaku menggunakan penomoran langkah-langkah atau *bullet points* telah dilebur menjadi susunan 2 paragraf padat per gambar. Masing-masing paragraf ditulis dengan alur naratif yang humanis (minimal 3 kalimat) tanpa menggunakan frasa *template*.
- **Isu Penghapusan Gambar**: Ditemukan *bug* pada proses *cleaning* awal (skrip `fix_numbering.py`) yang secara tidak sengaja menghapus elemen gambar karena gambar disisipkan oleh pengguna ke dalam paragraf berformat *bulleting* (sehingga dibaca sebagai "paragraf kosong dengan numbering").
- **Resolusi Aset**: Aset resolusi tinggi (*high-res*) diambil langsung dari direktori `rancangan_diagram` dan diinjeksikan secara terprogram menggunakan `inject_diagrams.py`. Kelima aset yang direstorasi adalah:
  1. `01_diagram_use_case.jpg` (Gambar 4)
  2. `02_diagram_arsitektur_sistem.jpg` (Gambar 5)
  3. `03_diagram_flowchart_dokumen.jpg` (Gambar 6)
  4. `04_diagram_erd_database.jpg` (Gambar 7)
  5. `05_diagram_sequence_parser.jpg` (Gambar 8)





## 10. Rencana Aksi & Pemetaan ADDIE Bab IV (Mei 2026)

Berdasarkan Step 1 SOP, telah dilakukan pemetaan konten draf Bab IV ke dalam struktur 5 tahapan model pengembangan ADDIE sebagai berikut:

### A. Pemetaan Konten Bab IV ke Struktur ADDIE
1. **Tahap Analisis (Analysis):**
   - **Isi**: Menganalisis kebutuhan awal digitalisasi ULT FKIP Unila. Menjelaskan pain points sistem lama (manual, tidak transparan, tidak terukur, dokumen fisik rawan rusak/hilang).
   - **Data Grounding**: Hasil wawancara pra-penelitian dengan staf ULT, admin prodi, dan kuesioner kebutuhan mahasiswa.
2. **Tahap Desain (Design):**
   - **Isi**: Rancangan arsitektur web terintegrasi dengan 4 portal utama (Public Portal, Student Portal, Admin/Staff Portal, dan Signer Portal) dan pemetaan hak akses pengguna (RBAC). Rancangan basis data MySQL, flowchart alur pengajuan dokumen, dan antarmuka (wireframe) halaman pengajuan.
3. **Tahap Pengembangan (Development):**
   - **Isi**: Implementasi pengkodean web menggunakan Laravel 12, PHP 8.4+, TailwindCSS, dan Alpine.js.
   - **Validasi Ahli**: Jabaran detail hasil pengujian dari 9 validator ahli (3 Ahli Materi, 3 Ahli Media, 3 Ahli Sistem). Penjabaran dilakukan *secara mendalam per individu ahli* (Margaretha, Eko, Putut, Rafiqa, Daniel, Dwi, Ghea, Radinal, Rahmad) mengenai skor kuantitatif (Materi: 95,45%, Media: 93,33%, Sistem: 87,58%), keputusan kelayakan, komentar kualitatif, dan tindak lanjut perbaikan riil yang dilakukan peneliti.
   - **Tabel**: Seluruh tabel detail per aspek (Tabel 4.2, 4.3, 4.4) DIHAPUS. Rincian dijelaskan lewat narasi mengalir. Hanya menampilkan **Tabel Rekapitulasi Hasil Validasi Ahli** (Tabel 4.1) yang diletakkan di bagian **PALING BAWAH** Tahap Pengembangan ini sebagai penutup.
4. **Tahap Implementasi (Implementation):**
   - **Isi**: Proses uji coba produk secara terbatas kepada 18 responden menggunakan teknik *purposive sampling* (12 mahasiswa perwakilan rumpun PBS, PIP, PIPS, PMIPA; 3 admin prodi; 3 staf ULT).
   - **Data Grounding**: Rerata skor kuesioner kepraktisan mencapai 55,28/60,00 atau 92,13% (Sangat Praktis). Distribusi kategori Bagian F kuesioner (94,44% menyatakan Sangat Praktis/Praktis).
   - **Tabel**: "Tabel Rekapitulasi Uji Kepraktisan" (Tabel 4.5) dan "Ringkasan Distribusi" (Tabel 4.6) DICABUT dari Bab 4 dan dipindahkan ke **Lampiran**. Di Bab 4 cukup narasi penjelasannya secara mendetail dan beri referensi ke Lampiran.
   - **Komentar**: Narasi tanggapan kualitatif dari 6 responden (Anisa, Lisa, Riswan, Nabila, Nur, Amrul) dan tindak lanjut perbaikan sistem yang telah dikerjakan peneliti.
5. **Tahap Evaluasi (Evaluation):**
   - **Isi**: Evaluasi sumatif kelayakan produk secara menyeluruh. Menilai efektivitas website ULT FKIP Unila dalam mengatasi pain points digitalisasi pelayanan akademik berdasarkan umpan balik para ahli dan kepraktisan pengguna.

### B. Penyusunan Subbab Pembahasan
1. **Eksplorasi Keunggulan (Mendalam):**
   - Integrasi 4 portal dengan RBAC (Role-Based Access Control) yang ketat.
   - Otomatisasi perakitan dokumen Word (OpenXML) berbasis template dinamis (mencegah degradasi visual style orisinal).
   - Keamanan tingkat tinggi: Penyimpanan privat (private disk disk Laravel), middleware anti-IDOR, dan HTTP Headers Content Security Policy (CSP) untuk mencegah serangan XSS.
   - Keberadaan jejak audit trail digital komprehensif (auditable timeline) dan kompabilitas PWA.
2. **Eksplorasi Kendala & Solusi (Mendalam):**
   - *Kendala 1 (Format Dokumen)*: WYSIWYG Tiptap HTML tag mentah pada DOCX/PDF. *Solusi*: Mengembangkan parser HTML-to-OpenXML dinamis terpusat pada XML mentah di `DocumentAssemblerService`.
   - *Kendala 2 (Keamanan)*: Kekhawatiran celah XSS dan bypass otorisasi dokumen. *Solusi*: Memperketat CSP header bawaan Laravel dan enkripsi direktori path.
   - *Kendala 3 (Integrasi)*: Belum adanya integrasi Single Sign-On (SSO) aktif dengan Unila. *Solusi*: Menyusun cetak biru (blueprint) integrasi database pengguna untuk pengembangan fase berikutnya.

### C. standardisasi Terminologi & File Output
- **Find and Replace**: Mengubah semua kata "Ringkasan" yang merujuk pada data gabungan menjadi **"Rekapitulasi"**.
- **Output**:
  1. `001_Skripsi_Andricha Dea Mitra_Clean.docx`: Versi bersih, rapi, terstruktur ADDIE (diperbarui langsung).
  2. `001_Skripsi_Andricha Dea Mitra_Highlighted.docx`: Versi revisi yang sama dengan highlight stabilo kuning pada bagian yang direstrukturisasi dan ditambahkan (diperbarui langsung).

## 11. Restrukturisasi Narasi Gambar 5, 6, 7, dan 8 (Bab 4.1.2)
- Mengembangkan narasi mendalam yang mendeskripsikan secara spesifik **Antarmuka Visual, Fungsi Komponen, dan Alur Kerja/Interaksi** pada Gambar 5 (Arsitektur Sistem), Gambar 6 (Flowchart), Gambar 7 (ERD), dan Gambar 8 (Sequence Diagram).
- Mengeliminasi format *bullet point* dan tulisan template di seluruh narasi, lalu menggantinya dengan paragraf analitis komprehensif tanpa kalimat pengantar yang kaku.
- Mereposisi letak narasi agar secara konsisten selalu berada **setelah** *caption* gambar, bukan terpencar di berbagai tempat sebelumnya.
- Penyuntikan dilakukan secara terprogram menggunakan skrip `expand_narrative.py` pada file Word Clean dan Highlighted.

## 12. Restrukturisasi Tahap Desain (Bab 4.1.2)
- **Pecahan 4 Sub-subbab**: Bagian 4.1.2 Tahap Desain telah distrukturisasi ulang menjadi empat pilar sesuai panduan (Constructing Criterion-Referenced Tests, Media Selection, Format Selection, dan Initial Design) dan diadaptasi secara kontekstual untuk sistem Web ULT.
- **Konsolidasi Diagram**: Penjabaran Use Case, Arsitektur, Flowchart, ERD, dan Sequence diagram kini terkonsolidasi utuh di bawah sub-subbab d. Initial Design.
- **Tambahan Narasi Baru**: Ditambahkan narasi ekstensif mengenai Activity Diagram, Wireframe (khusus tampilan utama/Public Portal), dan UI/UX Final Design dengan penjelasan layout, warna korporat Unila, dan library yang digunakan (Tailwind, Alpine).
- **Placeholder Aset**: Menghapus baris placeholder `[GAMBAR_ACTIVITY_DIAGRAM_BELUM_ADA]` beserta paragraf penjelasan kasarnya pada versi sebelumnya.
- **Status**: Eksekusi update langsung ke `001_Skripsi_Andricha Dea Mitra_Clean.docx` dan `001_Skripsi_Andricha Dea Mitra_Highlighted.docx` telah sukses.

## 16. Perancangan dan Injeksi Wireframe Beranda (Bab 4.1.2)
- Menganalisis kebutuhan kerangka antarmuka sesuai subbab *Perancangan Kerangka Tampilan Utama (Wireframe)*. Disimpulkan bahwa skripsi hanya membutuhkan satu wireframe utama, yaitu **Wireframe Beranda Utama (Public Portal)**.
- Mendesain wireframe *low-fidelity* (hitam putih) menggunakan PlantUML Salt, yang memuat komponen wajib: *Header* (Logo, Navigasi, Login), *Hero Section* (Slogan & CTA), *Step-by-Step Grid* (4 Tahapan Pengajuan), dan *Footer*.
- Menyuntikkan aset `10_wireframe_beranda.png` secara otomatis ke dalam dokumen Word `Clean` dan `Highlighted`.
- Menyesuaikan penomoran gambar menjadi **Gambar 4.5. Wireframe Beranda Utama (Main View) Sistem ULT FKIP Unila.** serta memperbaiki format perataan teks (*Justify*) dan indentasi (1.27 cm) pada paragraf penjelasan di bawahnya.
- **Status**: Eksekusi update langsung ke kedua file dokumen Word telah sukses.

## 15. Perbaikan Visual dan Tata Letak Activity Diagram (Bab 4.1.2)
- **Desain Monokrom (PlantUML)**: Menggenerasi ulang 4 file Activity Diagram menggunakan format **PlantUML** untuk mendapatkan garis berenang (*swimlane*) gaya UML standar (Tabel Pengguna dan Sistem) sesuai referensi skripsi, dengan resolusi tinggi (Arial 12) dan tema flat (shadowing false).
- **Penyesuaian Skala**: Menurunkan skala/ukuran lebar *Activity Diagram* menjadi 4.8 inci saat disisipkan ke dalam format Word agar proporsional dan tulisan dapat dibaca sangat jelas.
- **Restrukturisasi Caption & Deskripsi**: Membersihkan teks caption dan deskripsi yang tadinya berantakan akibat duplikasi format.
- **Formatting Sesuai Referensi**: 
  - Keterangan gambar (*caption*) ditempatkan **di bawah** gambar dengan format rata tengah (*Center*) dan ditambahkan penomoran hierarkis (`Gambar 4.1.`, `Gambar 4.2.`, dst).
  - Paragraf penjelasan diposisikan tepat di bawah caption dengan format rata kanan-kiri (*Justify*), spasi 1.5 baris, dan penjorokan baris pertama (*First Line Indent*) 1.27 cm (0.5 inci).
- **Status**: Seluruh revisi telah dieksekusi secara rapi pada `001_Skripsi_Andricha Dea Mitra_Clean.docx` dan versi `Highlighted`. tanpa duplikasi file.

## 13. Pembuatan Tabel Format Antarmuka (Bab 4.1.2)
- Menyusun dan menyisipkan tabel spesifikasi format antarmuka (Tabel 4.1) secara terprogram di bagian akhir sub-subbab `c. Format Selection`.
- Tabel ini merinci format halaman dan letak antarmuka dari 5 portal utama: Public Portal, Authentication System, Student Portal, Admin/Staff Portal, dan Signer Portal, selaras dengan pola dokumentasi referensi.
- Disertai dengan satu paragraf penjelasan komprehensif di bawah tabel mengenai efektivitas pemisahan antarmuka secara modular.

## 14. Pembuatan 4 Diagram Activity Fisik (Bab 4.1.2)
- **Desain & Automasi**: Merancang struktur 4 Diagram Activity menggunakan notasi standar *flowchart* UML dengan teknik *swimlane*, lalu merendernya menjadi aset gambar `.jpg` menggunakan API eksternal (*Mermaid Ink*).
- **Penggantian Placeholder**: Menghapus baris placeholder `[GAMBAR_ACTIVITY_DIAGRAM_BELUM_ADA]` beserta paragraf penjelasan kasarnya pada sub-subbab `d. Initial Design`.
- **Injeksi Terstruktur**: Menyisipkan 4 aset fisik gambar langsung ke dalam dokumen `.docx` `Clean` dan `Highlighted`, masing-masing dilengkapi format *Caption* (Gambar [NOMOR_GAMBAR]) dan penjelasan paragraf analitis komprehensif di bawahnya:
  1. **Diagram Activity Autentikasi Pengguna**: Memetakan alur registrasi, validasi email, hingga login berbasis *Role*.
  2. **Diagram Activity Pengajuan Layanan Akademik**: Memetakan interaksi input dari sisi mahasiswa (*Student Portal*) hingga masuknya notifikasi ke staf.
  3. **Diagram Activity Verifikasi dan Pemrosesan Dokumen**: Memetakan birokrasi verifikasi staf, hingga turunnya TTD Elektronik di *Signer Portal* yang men- *trigger* mesin *assembly* (penggabungan dokumen) sistem.
  4. **Diagram Activity Manajemen Layanan dan Templat**: Menjabarkan kemudahan fitur Admin dalam mengubah nama layanan, formulir variabel *dynamic input*, dan master `.docx` secara seketika (*real-time*).





## 17. Penulisan Tahap Development (Bab 4)
- **Eksekusi Penulisan**: Telah ditambahkan subbab **3. Tahap Pengembangan (Development)** ke dalam file dokumen Word Clean dan Highlighted yang berisi penjabaran hasil implementasi antarmuka, Expert Appraisal, dan Developmental Testing.
- **Eksekusi Penyisipan Gambar**: Ke-11 placeholder gambar tampilan antarmuka kini telah **berhasil diganti secara otomatis** menggunakan *screenshot* web asli (lengkap dengan *caption* judul gambar di bawahnya) tanpa memerlukan input manual pengguna.
- **Daftar Diagram yang Membutuhkan Input Manual**: Terdapat dua placeholder instruksi [INSERT DIAGRAM MS WORD DI SINI] dan [INSERT DIAGRAM MS WORD DI SINI BERDASARKAN TABEL DI ATAS] di bawah Tabel 4.x (Masukan dan Saran Validator) dan Tabel 4.y (Uji Kepraktisan). Pengguna diinstruksikan untuk membuat diagram statis MS Word secara manual di lokasi tersebut berdasarkan tabel data yang sudah dibuatkan.
- **Status**: Eksekusi injeksi teks telah sukses dilakukan ke bagian akhir dokumen Word.## 18. Pemformatan Ulang Daftar Pustaka (Strict Rules)
- **Analisis**: Diinstruksikan untuk melakukan pemformatan ulang secara ketat pada dokumen Daftar_Pustaka_Unila_2020.docx sesuai aturan terbaru.
- **Rencana Kerja**:
  1. Menghapus garis bawah pada setiap hyperlink dan mengubah warna tautan menjadi hitam (#000000).
  2. Menerapkan cetak miring (*italic*) HANYA pada nama rumah jurnal dan volume jurnal.
  3. Menghapus format cetak miring pada judul artikel jurnal.
  4. Tetap mempertahankan format cetak miring eksklusif pada judul buku.
- **Eksekusi**: Melakukan modifikasi pada script builder berbasis OpenXML (apply_real_dates.py) di *element run properties* (w:rPr), yaitu tag <w:color w:val="000000"/> dan <w:u w:val="none"/>. Logika pemisahan string menggunakan regex diterapkan secara akurat guna menangkap nama dan nomor volume jurnal tanpa menyertakan nomor halaman atau judul agar yang dicetak miring murni hanya jurnal dan volumenya.
- **Hasil Akhir**: Daftar pustaka berhasil disusun ulang ke dalam file c:\laragon\www\ult-fkip-unila\docs\skripsi\Daftar_Pustaka_Unila_2020.docx dengan hasil yang presisi. Seluruh tautan tetap bersifat fungsional (dapat diklik) dengan warna tulisan hitam tanpa garis bawah.
## 19. Laporan Implementasi Perbaikan & Uji Coba E2E (Juni 2026)

Berdasarkan hasil uji validitas dan uji kepraktisan (SOP Fase 1 & Fase 2), Agent telah melaksanakan seluruh *actionable items* secara menyeluruh pada repositori:

### A. Perbaikan UI/UX (Fase 1)
- **Desain Hero & Latar**: Menghapus kelas `bg-gradient-to-r from-violet-600 to-indigo-600` yang memberikan efek gradien pada latar belakang *hero* di portal publik (`layouts.public` dan `home.blade.php`), lalu menggantinya dengan warna solid profesional `bg-slate-50`.
- **Ikonografi**: Menambahkan komponen visual (ikon `iconify-icon`) pada setiap *tile/chip* kategori dan format dokumen di beranda agar lebih representatif.
- **Keterbacaan**: Memodifikasi spesifikasi *grid gap* dan rasio *line-height* pada komponen `service ticker cards` di `app.css` untuk mencegah penumpukan/kepadatan teks.
- **Navigasi Footer**: Menambahkan komponen fungsional tombol *Back to Top* dengan animasi *smooth scroll* di sudut bawah portal.

### B. Pembaruan Fungsionalitas & Keamanan (Fase 1)
- **Otomasi Penomoran (Backend)**: Merefaktor logika `maybeIssueDocumentNumber()` pada `RequestWorkflowService.php`. Unit penerbit dokumen yang awalnya bergantung pada "unit pemohon" kini disetel *hardcode* dan dialihkan sepenuhnya agar selalu merujuk dan menerbitkan format dari entitas **Fakultas**.
- **Visualisasi Pelaporan (Admin)**: 
  - Mengimplementasikan `ReportController.php` beserta rute pengaksesan (`admin.reports.index`).
  - Merancang halaman dasbor pelaporan baru menggunakan **Chart.js** yang menyajikan statistik distribusi status dan fluktuasi permohonan secara bulanan.
  - Memasukkan menu "Pelaporan" ke dalam sistem *Sidebar* staf Admin secara dinamis sesuai struktur otorisasi *Spatie Roles and Permissions*.
- **Keamanan CSP (Frontend/Backend)**: 
  - Mengeliminasi izin eksekusi *script* tidak aman (`unsafe-eval`) dari `config/security.php`.
  - Menginstal dependensi resmi `@alpinejs/csp` di `package.json`.
  - Memodifikasi inisialisasi di `resources/js/app.js` agar menggunakan *build* Alpine.js khusus CSP. 
  - Mengompilasi ulang aset produksi Vite (`npm run build`).

### C. Uji Coba End-to-End (E2E) Workflow (Fase 2)
- Menggunakan *Playwright* (skrip `tests/e2e/document_workflow.py`), Agent melakukan simulasi transaksi dan eksekusi komprehensif mulai dari titik 0 hingga final.
- **Hasil Pengujian**: Test berjalan **100% SUKSES**. Alur permohonan berhasil melewati portal mahasiswa (pengajuan *file* via Tiptap editor), tinjauan birokrasi *Admin Jurusan*, verifikasi awalan staf ULT, injeksi tanda tangan Dekan (*Signer*), hingga perakitan *Word Document* akhir (*Assembly*). Tidak ditemukan *error* maupun anomali data di seluruh proses simulasi.

 
 # #   1 7 .   P e r b a i k a n   B u g   D u p l i k a s i   d a n   P e n g u r u t a n   S t a t u s   K P I 
 -   * * A n a l i s i s   A w a l * * :   B u g   d u p l i k a s i   p a d a   D a s h b o a r d   A d m i n   d a n   D a s h b o a r d   M a h a s i s w a   t e r j a d i   k a r e n a   a d a   p e r b e d a a n   t i p e   s t r i n g   d i   b a c k e n d   ( c o n t o h :   \ S E L E S A I \   v s   \ C O M P L E T E D \ ,   s e r t a   \ I N _ S I G N I N G \   v s   \ R E A D Y _ F O R _ F I N A L \ )   y a n g   d i   d a t a b a s e   t e r s i m p a n   s e b a g a i   e n u m   b e r b e d a ,   n a m u n   p a d a   s a a t   d i t a m p i l k a n   k e   U I   d i k e l o m p o k k a n   m e n j a d i   s a t u   n a m a   ( s e p e r t i   \ P e n a n d a t a n g a n a n \   d a n   \ S e l e s a i \ ) .   H a l   i n i   m e n y e b a b k a n   \ G R O U P   B Y \   q u e r y   S Q L   m e m b e r i k a n   2   * r e c o r d *   d e n g a n   j u m l a h   y a n g   b e r b e d a .   S e l a i n   i t u ,   b e l u m   a d a   p e n g u r u t a n   m a n u a l   ( * c u s t o m   s o r t i n g * )   u n t u k   m e n g a t u r   w o r k f l o w   s t a t u s   s e s u a i   u r u t a n   l o g i s   y a n g   d i m i n t a   p e n g g u n a . 
 -   * * T i n d a k a n * * :   M e n e r a p k a n   g r o u p i n g   u l a n g   m e n g g u n a k a n   o p e r a s i   \  e d u c e \   d i   C o n t r o l l e r ,   m e n g u b a h   l o g i c   d i   \ A d m i n D a s h b o a r d C o n t r o l l e r . p h p \   d a n   \ S t u d e n t \ R e q u e s t C o n t r o l l e r . p h p \   d e n g a n   m e n a m b a h k a n   m e t o d e   m a p p i n g   s t a t u s   u n t u k   m e n g g a b u n g k a n   \ C O M P L E T E D \   m e n j a d i   \ S E L E S A I \ ,   d a n   \ R E A D Y _ F O R _ F I N A L \   m e n j a d i   \ I N _ S I G N I N G \ .   K e m u d i a n   d i u r u t k a n   d e n g a n   a r r a y   u r u t a n   * c u s t o m *   m e l a l u i   f u n g s i   \ u k s o r t ( ) \   s e s u a i   a l u r   w o r k f l o w   ( D i a j u k a n   - >   R e v i e w   U L T   - >   P e r l u   P e r b a i k a n   - >   P e n a n d a t a n g a n a n   - >   S e l e s a i   - >   D i t o l a k   A d m i n ) .   T e r a k h i r ,   m e m p e r b a i k i   t i p o g r a f i   d i   f i l e   \ s t a t u s - b a d g e . b l a d e . p h p \   m e n j a d i   \ P e n a n d a t a n g a n a n \   a g a r   s e s u a i   d e n g a n   p e r m i n t a a n . 
 -   * * K e n d a l a   &   S o l u s i * * :   K a r e n a   q u e r y   d e f a u l t   l a n g s u n g   m e m a k a i   m e t o d e   \ p l u c k ( ' c ' ,   ' c u r r e n t _ s t a t u s ' ) \ ,   d a t a   l a n g s u n g   t e r - r e n d e r   m e n t a h .   S o l u s i n y a ,   m a p p i n g   a r r a y   d a n   g r o u p i n g   d i l a k u k a n   d i   l e v e l   C o n t r o l l e r   P H P   s e b e l u m   d i - i n j e c t   k e   B l a d e   U I ,   k a r e n a   m o d i f i k a s i   d i   q u e r y   l e v e l   ( S Q L )   a k a n   j a u h   l e b i h   k o m p l e k s   d a n   t i d a k   s e j a l a n   d e n g a n   i m p l e m e n t a s i   e n u m   i n t e r n a l   s a a t   i n i .  
 