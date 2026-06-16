# Audit Instrumen Uji Validitas Ahli

Dokumen ini mengaudit tiga instrumen:

- `instrumen-uji-validitas-ahli-materi.docx`
- `instrumen-uji-validitas-ahli-media.docx`
- `instrumen-uji-validitas-ahli-sistem.docx`

## Ringkasan

Secara umum, instrumen sudah memiliki kekuatan awal:

- Sudah dipisahkan menjadi tiga domain penilaian: materi, media, dan sistem.
- Bahasa item relatif sederhana dan mudah dipahami validator.
- Jumlah item per instrumen masih wajar untuk penilaian ahli.

Namun, secara metodologis masih ada beberapa masalah utama:

1. Batas konstruk antar instrumen belum tegas, sehingga beberapa item saling tumpang tindih.
2. Beberapa butir tidak benar-benar mengukur validitas isi, tetapi lebih dekat ke efektivitas, manfaat, atau kepuasan penggunaan.
3. Belum ada petunjuk analisis kuantitatif yang jelas untuk menetapkan item valid, direvisi, atau dibuang.
4. Skala penilaian dan keputusan kelayakan akhir belum terhubung ke kriteria interpretasi yang operasional.

## Temuan Utama

### 1. Instrumen ahli materi masih memuat butir manfaat dan efisiensi

File: `instrumen-uji-validitas-ahli-materi.docx`

Butir berikut lebih dekat ke dampak penggunaan sistem daripada validitas materi:

- "Website membantu mahasiswa memperoleh layanan administrasi dengan lebih mudah."
- "Penggunaan website berpotensi mengurangi antrean dan pengurusan berkas berulang."
- "Website memberikan nilai tambah nyata dibandingkan mekanisme layanan manual yang berjalan saat ini."

Masalah:

- Ahli materi seharusnya fokus pada ketepatan isi, kelengkapan informasi, relevansi layanan, kejelasan istilah, dan kesesuaian prosedur.
- Butir manfaat, kemudahan, dan efisiensi lebih cocok masuk ke uji kepraktisan atau usability, bukan validitas ahli materi.

Dampak:

- Skor validitas materi bisa bias tinggi karena validator menilai manfaat sistem, bukan mutu isi layanan.

### 2. Instrumen ahli media masih bercampur dengan fungsi sistem

File: `instrumen-uji-validitas-ahli-media.docx`

Butir yang bercampur dengan domain sistem/fungsi:

- "Fitur utama website mendukung proses layanan administrasi ULT FKIP secara memadai."
- "Fitur utama website membantu pengguna menyelesaikan kebutuhan layanan administrasi."
- "Secara umum, fungsi website mendukung peningkatan kualitas layanan administratif di ULT FKIP."

Masalah:

- Ahli media idealnya menilai tampilan, navigasi, hierarki visual, keterbacaan, konsistensi, interaksi antarmuka, dan kemudahan penggunaan.
- Penilaian apakah fungsi inti sistem "mendukung proses layanan" lebih tepat untuk ahli sistem.

Dampak:

- Ada duplikasi konstruk dengan instrumen ahli sistem.
- Hasil validasi media menjadi kurang murni karena mencampur aspek UI dengan kecukupan fungsi.

### 3. Instrumen ahli sistem memuat aspek yang sulit dinilai dari sisi pengguna

File: `instrumen-uji-validitas-ahli-sistem.docx`

Butir yang berisiko tidak observable bila validator hanya menguji dari antarmuka:

- "Proteksi otorisasi pada halaman atau endpoint penting sudah memadai."
- "Struktur modul atau komponen sistem cukup jelas untuk dipelihara lebih lanjut."
- "Dokumentasi teknis minimal atau struktur implementasi sistem sudah cukup mendukung proses pemeliharaan."

Masalah:

- Butir keamanan endpoint, maintainability, dan dokumentasi teknis tidak dapat dinilai akurat jika ahli hanya diberi akses ke website jadi.
- Item seperti ini memerlukan artefak tambahan: source code, dokumentasi teknis, atau walkthrough arsitektur.

Dampak:

- Validator bisa memberi skor berdasarkan perkiraan, bukan bukti.
- Validitas isi item menjadi lemah karena indikator tidak selaras dengan objek yang diamati.

### 4. Ada tumpang tindih antarinstrumen

Contoh overlap:

- Ahli materi: relevansi layanan, kejelasan panduan, informasi umpan balik.
- Ahli media: kemanfaatan fitur utama, interaktivitas sistem, kemudahan penggunaan.
- Ahli sistem: kualitas interaksi antarmuka, umpan balik sistem, navigasi konsisten.

Masalah:

- Satu konstruk dinilai di lebih dari satu instrumen dengan istilah berbeda.
- Hal ini menyulitkan saat menjelaskan batas peran validator di bab metode.

Dampak:

- Potensi double counting.
- Sulit menjelaskan mengapa suatu temuan masuk revisi media atau revisi sistem.

### 5. Seluruh butir berbentuk positif

Masalah:

- Semua item menggunakan arah penilaian yang mengundang persetujuan.
- Tidak ada item penyeimbang atau setidaknya variasi redaksi yang mengurangi acquiescence bias.

Dampak:

- Validator cenderung memberi skor tinggi secara konsisten.

Catatan:

- Untuk instrumen ahli, item negatif tidak wajib. Namun, variasi redaksi dan indikator yang lebih spesifik tetap diperlukan agar skor tidak terlalu longgar.

### 6. Skala Likert dan keputusan akhir belum operasional

Semua instrumen memakai skala:

- 5 = Sangat setuju
- 4 = Setuju
- 3 = Cukup setuju
- 2 = Tidak setuju
- 1 = Sangat tidak setuju

Masalah:

- "Cukup setuju" bukan titik tengah yang netral. Secara psikometrik, label ini masih condong positif.
- Opsi akhir "layak digunakan tanpa revisi / dengan revisi / tidak layak" belum diberi kriteria angka.

Dampak:

- Interpretasi hasil menjadi subjektif.
- Sulit mempertanggungjawabkan batas keputusan di bab hasil.

### 7. Metadata validator masih minimal

Semua instrumen hanya memuat:

- Nama validator

Sebaiknya ditambah:

- Bidang keahlian
- Instansi
- Jabatan
- Tanggal penilaian
- Lama pengalaman

Alasan:

- Memperkuat kredibilitas validator.
- Memudahkan justifikasi pemilihan ahli pada bab metode.

## Analisis Per Instrumen

### Ahli Materi

Jumlah item: 20

Sudah kuat pada:

- kesesuaian konten layanan
- kejelasan alur dan persyaratan
- relevansi layanan
- kemudahan memahami informasi

Perlu direvisi pada:

- indikator manfaat layanan berbasis website
- indikator efisiensi waktu dan akses

Rekomendasi fokus konstruk:

- akurasi isi layanan
- kelengkapan informasi
- kesesuaian dengan prosedur resmi
- kejelasan bahasa dan istilah
- relevansi layanan terhadap kebutuhan target pengguna

### Ahli Media

Jumlah item: 18

Sudah kuat pada:

- warna tampilan
- tata letak menu dan navigasi
- kejelasan teks
- kualitas visual
- kemudahan penggunaan

Perlu direvisi pada:

- indikator kelengkapan fitur layanan
- indikator kemanfaatan fitur utama

Rekomendasi fokus konstruk:

- konsistensi tampilan
- keterbacaan
- hierarki visual
- kemudahan navigasi
- kejelasan tombol dan feedback visual
- aksesibilitas dasar

### Ahli Sistem

Jumlah item: 21

Sudah kuat pada:

- fungsi autentikasi dan otorisasi
- alur pengajuan layanan
- validasi input
- tracking status
- kinerja dasar

Perlu direvisi atau dipisah pada:

- keamanan endpoint bila tidak ada akses teknis
- maintainability
- dokumentasi teknis

Rekomendasi fokus konstruk:

- correctness fungsi utama
- reliability proses
- keamanan akses yang dapat diuji
- stabilitas
- performa dasar
- integritas input dan output

## Rekomendasi Revisi Struktural

### Struktur instrumen yang disarankan

#### Ahli materi

Kelompok indikator:

- Kesesuaian isi dengan SOP/kebijakan
- Kelengkapan informasi layanan
- Kejelasan alur, syarat, dan output
- Ketepatan istilah/status layanan
- Relevansi layanan dengan kebutuhan pengguna

Yang dipindahkan keluar:

- manfaat website
- efisiensi waktu
- nilai tambah dibanding sistem manual

#### Ahli media

Kelompok indikator:

- Desain visual dan konsistensi
- Keterbacaan teks
- Navigasi dan tata letak
- Interaksi dan feedback antarmuka
- Kemudahan penggunaan

Yang dipindahkan ke ahli sistem:

- kecukupan fungsi inti
- dukungan fitur terhadap proses layanan

#### Ahli sistem

Kelompok indikator:

- Fungsionalitas inti
- Keandalan proses
- Validasi input dan output
- Keamanan akses
- Kinerja sistem

Yang dibuat opsional atau lampiran teknis:

- maintainability
- dokumentasi teknis

## Rekomendasi Analisis Data

Untuk validitas ahli, instrumen ini lebih kuat bila dianalisis dengan pendekatan berikut:

### Opsi 1. Aiken's V

Cocok jika:

- validator adalah ahli
- skala penilaian ordinal 1-5
- fokus pada validitas isi per butir

Kelebihan:

- bisa menunjukkan validitas setiap item
- mudah dijustifikasi dalam penelitian pengembangan

Saran praktik:

- hitung nilai Aiken's V per item
- item dengan nilai rendah direvisi atau dibuang
- tampilkan juga komentar kualitatif validator

### Opsi 2. CVI

Cocok jika:

- item diubah menjadi skala relevansi
- misalnya 1 = tidak relevan sampai 4 = sangat relevan

Catatan:

- Bila tetap memakai skala persetujuan umum, Aiken's V biasanya lebih mudah dipertahankan.

## Perbaikan Teknis yang Disarankan

1. Ganti label skala tengah dari "Cukup setuju" menjadi label yang lebih netral, atau gunakan skala relevansi khusus validitas ahli.
2. Tambahkan kolom komentar per aspek atau per bagian, bukan hanya komentar umum.
3. Tambahkan identitas validator yang lebih lengkap.
4. Tetapkan aturan keputusan, misalnya item valid, valid dengan revisi, atau tidak valid berdasarkan hasil analisis.
5. Kurangi item yang overlap antar instrumen.
6. Pastikan setiap butir hanya menilai satu konstruk.

## Prioritas Revisi

Prioritas tinggi:

- Bersihkan overlap antara ahli media dan ahli sistem.
- Hapus atau pindahkan butir manfaat/efisiensi dari ahli materi.
- Hapus atau jadikan opsional butir maintainability pada ahli sistem bila validator tidak melihat source code.

Prioritas menengah:

- Rapikan skala penilaian.
- Tambahkan metadata validator.
- Tambahkan kriteria keputusan kelayakan.

Prioritas rendah:

- Seragamkan istilah antar dokumen.
- Tambahkan ruang saran per indikator atau per aspek.

## Kesimpulan

Instrumen yang ada sudah layak sebagai draft awal, tetapi belum sepenuhnya kuat sebagai instrumen validitas ahli yang ketat. Masalah terbesarnya adalah kebocoran konstruk antar instrumen dan beberapa butir yang lebih menilai manfaat penggunaan daripada validitas isi. Revisi paling penting adalah menegaskan batas domain:

- ahli materi menilai isi layanan
- ahli media menilai antarmuka dan komunikasi visual
- ahli sistem menilai fungsi, keandalan, keamanan, dan kinerja

Setelah batas ini dirapikan dan metode analisis item ditetapkan, instrumen akan jauh lebih defensible untuk kebutuhan skripsi atau penelitian pengembangan.
