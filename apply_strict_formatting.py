import docx
import os

FILES_TO_PROCESS = [
    r'docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx',
    r'docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Highlighted.docx'
]

# We will match the paragraphs based on their current prefix.
# Since we just wrote them in the previous step, we know exactly what they start with.
REPLACEMENTS = {
    "Dalam proses pengembangan dan implementa": 
        "Proses pengembangan dan implementasi sistem pada lingkungan Universitas Lampung memunculkan berbagai dinamika operasional yang tidak terhindarkan. Kendala teknis yang ditemukan selama masa pengujian menuntut adanya penyesuaian fungsional maupun arsitektural secara menyeluruh. Resolusi komprehensif atas setiap hambatan tersebut dijabarkan secara terperinci pada bagian ini.",
    
    "Evaluasi pada purwarupa awal menunjukkan": 
        "Evaluasi awal terhadap purwarupa sistem mendeteksi adanya ketidaksesuaian format teks pada dokumen akhir. Ketidaksesuaian ini muncul ketika draf hasil isian formulir pengguna berbasis WYSIWYG editor dikonversi menjadi berkas biner berekstensi .docx. Perbedaan format antara antarmuka web dan dokumen cetak ini menjadi perhatian utama tim pengembang.",
    
    "Sistem bawaan Tiptap Editor pada antarmu": 
        "Sistem bawaan Tiptap Editor pada antarmuka web secara kodrati memproduksi keluaran berupa tag HTML mentah. Konfigurasi bawaan ini menyebabkan elemen pemformatan dasar seperti paragraf baru, teks tebal, maupun penomoran daftar urut tidak dapat dirender secara visual saat diekstraksi. Elemen-elemen visual tersebut justru tercetak secara harfiah sebagai deretan karakter tag HTML seperti <p> dan <b> di dalam berkas Word.",
    
    "Ketidakmampuan sistem merender tag HTML ": 
        "Ketidakmampuan sistem merender tag HTML secara orisinal ini secara langsung memicu disrupsi visual pada dokumen keluaran. Disrupsi tersebut berisiko mengurangi nilai formalitas dan kredibilitas tata naskah persuratan akademik fakultas. Institusi pendidikan tinggi tentu sangat menghindari hal ini mengingat standar legalitas surat resmi mensyaratkan kerapian format yang absolut.",
    
    "Untuk mengatasi isu degradasi pemformata": 
        "Penyelesaian isu degradasi pemformatan ini direalisasikan melalui perancangan algoritma parser HTML-to-OpenXML dinamis yang beroperasi pada tingkat XML mentah (document.xml). Modul penerjemah ini secara efektif menelusuri seluruh tag HTML visual dan mengonversinya menjadi elemen struktur OpenXML secara seketika saat perakitan dokumen berlangsung. Hasil modifikasi ini sukses menduplikasi gaya teks orisinal tanpa merusak tata letak margin, ukuran fon, maupun spasi dokumen bawaan seperti yang terlihat pada Gambar 44.",
    
    "Fase pengujian sistem juga memprioritask": 
        "Fase pengujian sistem turut memprioritaskan validasi keamanan pada seluruh antarmuka masukan data. Prioritas ini ditetapkan mengingat tingginya potensi ancaman siber berbasis Cross-Site Scripting (XSS) di lingkungan aplikasi pendidikan. Perlindungan data identitas sivitas akademika menjadi landasan utama di balik penguatan filter sistem masukan ini.",
    
    "Pada uji coba keamanan awal (Gambar 45),": 
        "Pengujian keamanan tahap awal (Gambar 45) mengungkap celah kerentanan yang cukup berisiko. Simulasi injeksi skrip XSS yang disisipkan pada kolom formulir ternyata berhasil menembus pertahanan awal dan memicu kotak peringatan (alert box) pada peramban klien. Penemuan ini membuktikan bahwa celah tersebut berpotensi besar dimanfaatkan oleh pihak tidak bertanggung jawab untuk mengeksekusi instruksi peretasan dari luar.",
    
    "Sebagai tindakan perbaikan, diterapkan k": 
        "Tindakan perbaikan preventif segera diwujudkan dengan menerapkan kebijakan Content Security Policy (CSP) secara ketat pada header HTTP. Pengembang juga menyuntikkan pustaka sanitasi HTML pihak ketiga (mews/purifier) di tingkat pengendali (controller) untuk menyaring input kotor. Kombinasi pertahanan ganda ini dirancang khusus untuk menjinakkan segala bentuk injeksi kode asing sebelum data mencapai pangkalan data peladen.",
    
    "Hasil uji coba ulang (Gambar 46) menunju": 
        "Hasil uji coba penetrasi ulang (Gambar 46) memperlihatkan bahwa seluruh skrip injeksi XSS berhasil diblokir dengan sempurna. Setiap masukan yang mencurigakan secara otomatis dinetralisasi oleh sistem perlindungan sehingga hanya terbaca sebagai teks biasa yang tidak berbahaya. Keberhasilan mekanisme pertahanan ini berhasil menjamin keamanan integritas pangkalan data sekaligus melindungi sesi otorisasi pimpinan fakultas dari eksploitasi peretas.",
    
    "Selain masalah fungsionalitas, evaluasi ": 
        "Evaluasi kelayakan sistem tidak hanya berfokus pada aspek teknis fungsional, melainkan juga menyoroti ergonomi antarmuka pengguna. Tim evaluator menemukan adanya keluhan krusial yang berkaitan dengan tingkat kepadatan elemen visual. Kepadatan yang berlebih ini khususnya terjadi pada tampilan dasbor tabel manajemen data permohonan staf administrasi.",
    
    "Pada rancangan awal (Gambar 47), susunan": 
        "Rancangan tabel antarmuka versi awal (Gambar 47) memperlihatkan susunan batas matriks dengan jarak antarbaris (padding) yang terlampau sempit. Ketiadaan ruang napas visual ini membuat deretan rincian data pemohon tampak menumpuk sesak. Kondisi visual yang demikian tentu akan sangat membebani daya akomodasi penglihatan staf layanan yang bertugas memantau puluhan hingga ratusan baris data masuk setiap harinya.",
    
    "Revisi arsitektur antarmuka dilakukan de": 
        "Revisi arsitektur antarmuka dilakukan secara komprehensif guna menyelesaikan masalah kepadatan visual tersebut. Langkah pertama difokuskan pada perluasan rasio padding di antara sel tabel untuk memberikan jarak jeda yang melegakan pandangan. Langkah kedua melibatkan penyesuaian parameter ketebalan fon pada setiap judul kolom (tajuk) agar hierarki informasi terlihat lebih menonjol.",
    
    "Penerapan prinsip white space (ruang kos": 
        "Penerapan prinsip white space (ruang kosong) bertindak sebagai strategi utama dalam merekonstruksi kenyamanan pandang komponen antarmuka. Kehadiran ruang luang yang proporsional ini diklaim mampu meningkatkan harmoni visual serta memudahkan sistem navigasi pelacakan informasi. Penyesuaian tata letak ini juga memastikan kepatuhan peranti lunak terhadap standar rasio pedoman perancangan desain pengalaman pengguna modern.",
    
    "Setelah perbaikan (Gambar 48), kelancara": 
        "Kondisi pasca perbaikan (Gambar 48) membuktikan adanya peningkatan signifikan terkait kelancaran pembacaan laju data administrasi oleh staf ULT. Penyesuaian metrik jarak ruang jeda tabel ini berhasil memfasilitasi kenyamanan ergonomi visual petugas penjaga gawang pangkalan dokumen. Modifikasi fungsional ini secara tidak langsung membantu meminimalisasi potensi kelelahan kognitif operator selama memproses permohonan mahasiswa pada jam kerja harian.",
    
    "Penyelesaian atas berbagai disrupsi pemf": 
        "Penyelesaian holistik terhadap berbagai kendala degradasi pemformatan, perisai celah keamanan siber, hingga perombakan ergonomi visual membuktikan urgensi dari uji kelayakan sistem. Konvergensi perbaikan strategis ini sukses mentransformasi kelemahan purwarupa awal menjadi fondasi kekuatan ekosistem digital layanan pendidikan yang modern. Produk akhir peranti lunak kini hadir sebagai sebuah sistem persuratan terpadu yang andal, aman, dan berdaya tahan tinggi menghadapi lonjakan kebutuhan administrasi perguruan tinggi.",
    
    "Selama fase pengujian kepraktisan sistem": 
        "Fase pengujian kepraktisan sistem di lapangan menghadapi sebuah rintangan empiris terkait tingginya mobilitas jadwal responden. Kesibukan ritme operasional harian para pimpinan fakultas, padatnya rutinitas staf loket ULT, hingga padatnya jadwal perkuliahan mahasiswa berakumulasi menjadi kendala manajerial. Benturan jadwal ini menyebabkan lambatnya proses pengembalian instrumen kuesioner yang berisiko memperpanjang durasi waktu observasi penelitian secara keseluruhan.",
    
    "Oleh karena itu, diimplementasikan strat": 
        "Strategi mitigasi lapangan direalisasikan dengan pendekatan persuasi aktif melalui metode pendampingan teknis secara langsung (on-the-spot). Pendekatan \"jemput bola\" ini memanfaatkan sebuah rancangan skenario panduan instruksi pengujian aplikasi yang dikondensasi seringkas mungkin. Teknik penyusutan modul simulasi ini bertujuan untuk meminimalisasi waktu interaksi pengguna tanpa harus memotong langkah-langkah navigasi krusial yang menentukan penilaian akhir tingkat kepraktisan perangkat lunak.",
    
    "Lebih lanjut, tantangan migrasi sistem d": 
        "Tahapan puncak berupa migrasi produk sistem dari lingkungan peladen lokal (localhost) menuju infrastruktur peladen awan (cloud server) menuntut kehati-hatian ekstra. Pihak pengembang wajib melakukan penyelarasan spesifikasi komputasi seperti peningkatan versi bahasa pemrograman PHP ke versi 8.4+ guna memenuhi prasyarat kerangka kerja Laravel 12. Penyesuaian lapisan keamanan pangkalan berkas privat juga diberlakukan melalui manipulasi direktori tautan simbolik (symlink) untuk mengunci sistem dari ancaman eksploitasi peretasan tautan paksa.",
    
    "Secara keseluruhan, rangkaian resolusi d": 
        "Rangkaian intervensi penyelesaian masalah teknis dan manajemen lapangan ini terbukti menjadi bagian esensial dari siklus penyempurnaan kualitas purwarupa arsitektur perangkat lunak. Keberhasilan peneliti dalam mendiagnosis, memetakan, serta merekayasa ulang komponen yang rusak mempertegas kapabilitas sistem dalam merespons tantangan digitalisasi persuratan akademik. Ekosistem portal layanan manajemen administrasi terpadu FKIP Unila ini akhirnya dipastikan telah mencapai titik kesiapan peluncuran komersial yang matang serta berkinerja unggul."
}

def clean_paragraph_text(p):
    return p.text.strip().replace('\u200b', '')

for filepath in FILES_TO_PROCESS:
    print(f"Processing: {filepath}")
    doc = docx.Document(filepath)
    
    for p in doc.paragraphs:
        text = clean_paragraph_text(p)
        if not text:
            continue
            
        for prefix, replacement in REPLACEMENTS.items():
            if text.startswith(prefix[:40]):  # safely match first 40 chars
                p.text = replacement
                if 'Highlighted' in filepath:
                    for run in p.runs:
                        run.font.highlight_color = docx.enum.text.WD_COLOR_INDEX.YELLOW
                break

    doc.save(filepath)
    print("Saved successfully.")
