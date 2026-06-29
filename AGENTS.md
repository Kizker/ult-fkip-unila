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

### C. Aturan Ketat Eksekusi Perubahan (Strict Rules)
1. **Analisis Sebelum Eksekusi**: Jangan melakukan perubahan apa pun sebelum memahami secara penuh instruksi pengguna.
2. **Eksekusi Sesuai Permintaan**: Jangan melakukan perubahan apa pun jika tidak diminta secara eksplisit oleh pengguna.
3. **Pelestarian Format Manual**: Jika pengguna telah melakukan perubahan format penulisan secara manual, format tersebut **WAJIB disimpan, diingat, dan dipertahankan**. Dilarang merusak, mengubah, atau menimpa format kepenulisan (formatting, margin, font, indentasi, alignment, spacing, dll.) yang sudah disesuaikan secara manual oleh pengguna pada pekerjaan-pekerjaan selanjutnya.

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

Berdasarkan komparasi detail antara draf hasil format sebelumnya dengan file baseline orisinal `docs\skripsi\update skripsi terakhir-seminar proposal\001_Skripsi_Andricha Dea Mitra.docx`, berikut hasil audit dan rencana aksi perbaikan:

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
- **Status**: Eksekusi injeksi teks telah sukses dilakukan ke bagian akhir dokumen Word.

## 18. Pemformatan Ulang Daftar Pustaka (Strict Rules)
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

## 20. Restrukturisasi Tata Letak Halaman Utama (Portal Publik) — v2 Premium
- **Analisis Awal**: Dirombak ulang tata letak *Quick-Access Grid* di bawah *Hero Banner* pada `home.blade.php`. Versi sebelumnya (v1) menggunakan utility Tailwind secara intensif (`grid-cols-12`, `col-span-8/4`, `x-card`, `x-button`) yang tidak terintegrasi dengan desain kustom homepage (CSS custom properties `--home-*`, glassmorphism, animasi). Versi baru (v2) sepenuhnya mengadopsi *design system* proyek.
- **Perubahan CSS** (`resources/css/app.css`):
  - Menambahkan ~710 baris CSS baru di blok `Homepage Quick-Access Grid` (sebelum blok *Public listing/detail pages*).
  - Kelas BEM: `.ult-quick-access`, `.ult-panduan-section`, `.ult-guide-book-card`, `.ult-video-grid`, `.ult-video-card`, `.ult-popular-services-section`, `.ult-popular-panel`, `.ult-popular-item`.
  - Semua komponen menggunakan `--home-primary`, `--home-secondary`, `--home-border`, `--home-text` CSS custom properties.
  - Efek glassmorphism (`radial-gradient`, `inset box-shadow`, `backdrop-filter`) pada panel Panduan dan Layanan Terpopuler.
  - Animasi sheen (`home-panel-sheen`) dan floating blob (`home-float`) pada kartu buku panduan dan panel sidebar.
  - Dukungan penuh Dark Mode (`.dark` prefix) untuk seluruh komponen baru.
  - Responsif: Grid 2 kolom (`1fr 380px`) pada desktop ≥1024px, stack 1 kolom pada mobile. Video grid 3 kolom pada desktop, 1 kolom pada mobile ≤639px.
  - Sidebar layanan memiliki `position: sticky` dengan offset dari header.
- **Perubahan Template** (`resources/views/public/home.blade.php`):
  - Mengganti `<section class="section ult-layout-grid">` menjadi `<section class="ult-quick-access">` dengan struktur HTML semantik menggunakan kelas BEM kustom.
  - Kartu buku panduan menggunakan `<a>` tag langsung (bukan `<x-card>`) agar terintegrasi dengan hover CSS dan sheen animation.
  - Kartu video tutorial mengekstrak YouTube video ID secara dinamis untuk menampilkan thumbnail nyata (`img.youtube.com/vi/{id}/mqdefault.jpg`).
  - Video yang memiliki URL YouTube membuka link di tab baru (`target="_blank"`).
  - Layanan terpopuler menampilkan 6 item (naik dari 5) dengan ikon, kategori badge, dan animasi hover.
  - Tombol "Lihat Semua Layanan" menggunakan kelas kustom `ult-popular-panel__viewall` (bukan `<x-button>`).
  - Section Panduan & Layanan Terpopuler masing-masing memiliki *eyebrow badge* (`ult-section-header__eyebrow`) dengan ikon Heroicons.
- **Data Integritas**: Controller `PublicController@home` tidak diubah — data `$guideBook`, `$guideVideos`, `$services` tetap digunakan sebagaimana adanya. Section Pengumuman Terbaru dan Blog Terbaru tetap utuh di bawah layout grid baru.
- **Status**: Perubahan dieksekusi langsung. Vite build dijalankan untuk mengompilasi ulang CSS.

## 21. Perbaikan Bug Duplikasi dan Pengurutan Status KPI
- **Analisis Awal**: Bug duplikasi pada Dashboard Admin dan Dashboard Mahasiswa terjadi karena ada perbedaan tipe string di backend (contoh: `SELESAI` vs `COMPLETED`, serta `IN_SIGNING` vs `READY_FOR_FINAL`) yang di database tersimpan sebagai enum berbeda, namun pada saat ditampilkan ke UI dikelompokkan menjadi satu nama (seperti `Penandatanganan` dan `Selesai`). Hal ini menyebabkan `GROUP BY` query SQL memberikan 2 *record* dengan jumlah yang berbeda. Selain itu, belum ada pengurutan manual (*custom sorting*) untuk mengatur workflow status sesuai urutan logis yang diminta pengguna.
- **Tindakan**: Menerapkan grouping ulang menggunakan operasi `reduce` di Controller, mengubah logic di `AdminDashboardController.php` dan `Student\RequestController.php` dengan menambahkan metode mapping status untuk menggabungkan `COMPLETED` menjadi `SELESAI`, dan `READY_FOR_FINAL` menjadi `IN_SIGNING`. Kemudian diurutkan dengan array urutan *custom* melalui fungsi `uksort()` sesuai alur workflow (Diajukan -> Review ULT -> Perlu Perbaikan -> Penandatanganan -> Selesai -> Ditolak Admin). Terakhir, memperbaiki tipografi di file `status-badge.blade.php` menjadi `Penandatanganan` agar sesuai dengan permintaan.
- **Kendala & Solusi**: Karena query default langsung memakai metode `pluck('c', 'current_status')`, data langsung ter-render mentah. Solusinya, mapping array dan grouping dilakukan di level Controller PHP sebelum di-inject ke Blade UI, karena modifikasi di query level (SQL) akan jauh lebih kompleks dan tidak sejalan dengan implementasi enum internal saat ini.

## 22. Restrukturisasi Tata Letak Layanan Terpopuler (Portal Publik)
- **Analisis Awal**: Permintaan untuk mengubah daftar layanan terpopuler dari card terpisah menjadi list bersih (clean list) di dalam satu container putih besar, dan memastikan query hanya mengambil tepat 5 layanan.
- **Tindakan (Controller)**: Memodifikasi `app/Http/Controllers/PublicController.php` dengan mengubah `limit(8)` menjadi `limit(5)` dan menyesuaikan logika fallback agar hanya me-return `take(5)`.
- **Tindakan (View)**: Memodifikasi `resources/views/public/home.blade.php` pada bagian `ult-popular-list` dan `ult-popular-panel__footer` dengan mengganti class BEM spesifik komponen ke standard TailwindCSS utility classes agar terbentuk clean list vertikal dengan label ungu dan judul layanan bold, serta full-width button abu-abu terang sesuai lampiran gambar.
- **Status**: Berhasil diimplementasikan langsung pada view dan logic backend.
- **Refinement Visual**: Melakukan penyesuaian gaya tipografi (font-extrabold, tracking-widest) dan warna (slate-900, slate-800) pada judul panel, kategori layanan, dan tautan layanan agar selaras secara presisi dengan referensi gambar kedua yang meminta desain font gelap dan pekat tanpa warna ungu, serta mengubah gaya tombol 'Lihat Semua Layanan' menjadi border transparan sesuai gambar.

## 23. Redesain UI "Layanan Terpopuler" — Outlined Box & Solid Purple Button (Juni 2026)
- **Analisis Awal**: Permintaan untuk merombak tampilan list layanan di sidebar kanan (Layanan Terpopuler) pada halaman utama agar sesuai gambar referensi baru. Gambar menunjukkan desain "outlined box" per item layanan dengan border ungu muda, badge kategori berwarna ungu, dan tombol CTA solid ungu penuh di bawah.
- **Constraint Kritis**: Tinggi card "Layanan Terpopuler" harus SELALU SEJAJAR dengan total tinggi konten kolom kiri ("Panduan & Tutorial").
- **Tindakan (CSS - `resources/css/app.css`)**:
  - Menghapus `align-self: start` dari `.ult-popular-services-section` pada breakpoint `≥1024px` agar grid `align-items: stretch` (default) bekerja untuk menyamakan tinggi kedua kolom.
  - Menambahkan `display: flex; flex-direction: column` pada `.ult-popular-services-section` dan `flex: 1` pada `.ult-popular-panel` untuk mengisi penuh ruang vertikal.
  - Menambahkan `flex: 1` pada `.ult-popular-list` agar list mengisi ruang tengah.
  - Mengubah `margin-top` pada `.ult-popular-panel__footer` menjadi `margin-top: auto` untuk mendorong tombol CTA ke paling bawah card.
  - Mengubah border item dari `rgba(var(--home-border), .72)` menjadi `rgba(var(--home-primary), .20)` — border ungu muda.
  - Menambahkan style badge kategori: `background: rgba(var(--home-primary), .10)`, `padding: 2px 8px`, `border-radius: 6px`.
  - Menambahkan modifier class baru `ult-popular-panel__viewall--solid` dengan `background: linear-gradient(135deg, rgb(var(--home-primary)), rgba(var(--home-primary), .88))`, `color: #fff`, `box-shadow` ungu, dan efek hover translateY + intensifikasi shadow. Mendukung dark mode.
  - Menghapus CSS `.ult-popular-item__icon` yang tidak lagi digunakan.
- **Tindakan (View - `resources/views/public/home.blade.php`)**:
  - Menghapus semua Tailwind utility classes inline pada kolom kanan, digantikan oleh BEM classes yang sudah didefinisikan di CSS: `ult-popular-panel__header`, `ult-popular-panel__title`, `ult-popular-panel__subtitle`, `ult-popular-list`, `ult-popular-item`, `ult-popular-item__body`, `ult-popular-item__category`, `ult-popular-item__name`, `ult-popular-item__arrow`.
  - Menghapus elemen ikon document (`heroicons:document-text`) dari setiap item layanan.
  - Menambahkan ikon `heroicons:chevron-right-20-solid` sebagai panah navigasi di sisi kanan setiap item.
  - Mengubah tombol CTA dari `bg-slate-900` inline ke class `ult-popular-panel__viewall--solid` (solid ungu).
  - Mengubah ikon tombol CTA ke `heroicons:arrow-right-20-solid`.
- **Status**: Berhasil diimplementasikan dan di-build dengan Vite.
