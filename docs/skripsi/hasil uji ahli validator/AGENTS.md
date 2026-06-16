# Project Orchestrator - Uji Ahli Validator

## Analisis Awal
- **Project Type**: Data Extraction & Excel Reporting
- **Konteks**: Merekap data uji ahli dari berbagai validator (Materi, Media, Sistem) dan menghasilkan laporan rekapitulasi `Rekap_Uji_Validitas_Ahli.xlsx`.
- **Status Data Mentah**: File yang ditemukan di direktori saat ini berformat `.pdf`, bukan `.csv`, `.xlsx`, atau `.json`.
- **Rencana Folder**:
  - `data_mentah/materi/`
  - `data_mentah/media/`
  - `data_mentah/sistem/`

## Rencana Pengerjaan (Workflow)
1. **Analisis & Pengelompokan**: Identifikasi file yang ada dan kelompokkan ke subfolder. (Membutuhkan konfirmasi user).
2. **Pengolahan Data**: Membaca data hasil uji (saat ini terhalang format `.pdf`, butuh konfirmasi metode ekstraksi).
3. **Pembuatan Excel**: Meng-generate file Excel dengan 4 sheet sesuai instruksi.
4. **Dokumentasi**: Memperbarui `AGENTS.md` sesuai dengan progres.

## Status Pekerjaan
- [x] Membaca isi folder saat ini.
- [x] Memindahkan file ke subfolder (data_mentah/materi, data_mentah/media, data_mentah/sistem).
- [x] Ekstraksi data PDF dilakukan menggunakan Vision AI Agent karena file berupa scan gambar.
- [x] Menerapkan kriteria kelayakan sesuai instrumen (Layak digunakan tanpa revisi, dsb).
- [x] Memproses data 9 validator dan menghitung persentase/skor per item.
- [x] Men-generate `Rekap_Uji_Validitas_Ahli.xlsx`.

## Log Aktivitas
- **[2026-05-21]** Mengelompokkan file mentah (.pdf) ke folder kategori (`materi`, `media`, `sistem`).
- **[2026-05-21]** Menguji bacaan PDF menggunakan `pdfplumber`, ditemukan bahwa dokumen PDF tidak mengandung teks (kemungkinan hasil scan gambar). 
- **[2026-05-21]** Agent Orchestrator membaca dan mengekstrak skor serta komentar dari 9 file PDF secara visual menggunakan `view_file` (Multimodal).
- **[2026-05-21]** Membuat script `create_excel.py` untuk mengkompilasi data menjadi file `Rekap_Uji_Validitas_Ahli.xlsx` dengan 4 sheet sesuai permintaan, sukses dieksekusi.
