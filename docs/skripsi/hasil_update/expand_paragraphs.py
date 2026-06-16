import docx

def count_sentences(text):
    return len([s for s in text.split('. ') if s.strip()])

def expand_paragraphs(doc_path):
    doc = docx.Document(doc_path)
    
    # Text mapping replacements to ensure everything I generated has at least 3 sentences.
    replacements = {
        "Rincian jadwal pelaksanaan penelitian tersebut dirancang secara sistematis untuk memastikan seluruh tahapan mulai dari analisis awal hingga evaluasi akhir berjalan terukur. Alokasi waktu selama satu semester penuh memungkinkan peneliti untuk melakukan iterasi perbaikan secara mendalam tanpa mengorbankan kualitas validitas ahli maupun pengujian di lapangan.":
        "Rincian jadwal pelaksanaan penelitian tersebut dirancang secara sistematis untuk memastikan seluruh tahapan mulai dari analisis awal hingga evaluasi akhir berjalan terukur. Alokasi waktu selama satu semester penuh memungkinkan peneliti untuk melakukan iterasi perbaikan secara mendalam tanpa mengorbankan kualitas validitas ahli maupun pengujian di lapangan. Penjadwalan yang disiplin ini juga selaras dengan target waktu penyelesaian akademik tanpa perlu bersikap tergesa-gesa.",

        "Struktur instrumen analisis kebutuhan ini dirancang sedemikian rupa untuk menggali perspektif pengguna secara mendalam terkait urgensi digitalisasi layanan. Respons dari partisipan terhadap kisi-kisi ini akan menjadi landasan empiris bagi peneliti dalam merumuskan arsitektur awal dan fitur-fitur esensial yang harus dibangun pada prototipe website.":
        "Struktur instrumen analisis kebutuhan ini dirancang sedemikian rupa untuk menggali perspektif pengguna secara mendalam terkait urgensi digitalisasi layanan. Respons dari partisipan terhadap kisi-kisi ini akan menjadi landasan empiris bagi peneliti dalam merumuskan arsitektur awal dan fitur-fitur esensial yang harus dibangun pada prototipe website. Penggalian informasi awal ini sangat menentukan presisi solusi yang ditawarkan agar sesuai dengan ekspektasi nyata pengguna.",

        "Instrumen penilaian ahli materi difokuskan pada aspek kelayakan konten dan kualitas substansi informasi. Indikator ini disusun guna memastikan bahwa deskripsi layanan, kelengkapan syarat dokumen, dan alur prosedur yang disajikan dalam website telah sepenuhnya akurat serta sesuai dengan standar operasional baku akademik di lingkungan fakultas.":
        "Instrumen penilaian ahli materi difokuskan pada aspek kelayakan konten dan kualitas substansi informasi. Indikator ini disusun guna memastikan bahwa deskripsi layanan, kelengkapan syarat dokumen, dan alur prosedur yang disajikan dalam website telah sepenuhnya akurat serta sesuai dengan standar operasional baku akademik di lingkungan fakultas. Penilaian komprehensif pada tahap ini juga bertujuan meminimalisasi potensi bias interpretasi yang dapat menghambat alur kerja tata usaha.",

        "Indikator penilaian ahli media menitikberatkan pada aspek estetika antarmuka, ergonomi navigasi, dan keterbacaan visual. Aspek ini dievaluasi secara ketat untuk menjamin pengalaman pengguna yang intuitif serta tata letak yang tetap proporsional dan responsif saat diakses melalui berbagai macam perangkat.":
        "Indikator penilaian ahli media menitikberatkan pada aspek estetika antarmuka, ergonomi navigasi, dan keterbacaan visual. Aspek ini dievaluasi secara ketat untuk menjamin pengalaman pengguna yang intuitif serta tata letak yang tetap proporsional dan responsif saat diakses melalui berbagai macam perangkat. Umpan balik yang diperoleh dari validasi ini akan menjadi tolok ukur kesiapan visual sebelum desain akhir diimplementasikan secara teknis.",

        "Kisi-kisi penilaian ahli sistem ditujukan untuk menguji keandalan infrastruktur perangkat lunak, fungsionalitas basis data, serta keamanan autentikasi. Validasi di tahap ini memegang peranan vital dalam mendeteksi potensi celah keamanan maupun cacat fungsional (bug) guna menjamin stabilitas performa sebelum website dirilis ke lingkungan pengguna nyata.":
        "Kisi-kisi penilaian ahli sistem ditujukan untuk menguji keandalan infrastruktur perangkat lunak, fungsionalitas basis data, serta keamanan autentikasi. Validasi di tahap ini memegang peranan vital dalam mendeteksi potensi celah keamanan maupun cacat fungsional (bug) guna menjamin stabilitas performa sebelum website dirilis ke lingkungan pengguna nyata. Pemeriksaan teknis secara menyeluruh ini sekaligus menjadi perisai pertama dari ancaman potensi gangguan ketika sistem dijalankan penuh.",

        "Penjabaran indikator uji kepraktisan ini diarahkan pada pengukuran efektivitas dan efisiensi produk saat dioperasikan langsung oleh pengguna akhir. Instrumen ini sekaligus menjadi alat pengukur tingkat penerimaan mahasiswa dan staf terhadap transisi digitalisasi pelayanan administrasi akademik berbasis web.":
        "Penjabaran indikator uji kepraktisan ini diarahkan pada pengukuran efektivitas dan efisiensi produk saat dioperasikan langsung oleh pengguna akhir. Instrumen ini sekaligus menjadi instrumen validasi penerimaan mahasiswa maupun staf terhadap transisi digitalisasi pelayanan administrasi akademik berbasis web. Data yang terkumpul akan memetakan elemen spesifik yang masih memerlukan pembenahan dari sudut pandang pengalaman pengguna di lapangan.",

        "Penerapan rentang kriteria ini berfungsi sebagai tolok ukur final untuk menyimpulkan keberhasilan produk dari perspektif operasional lapangan. Klasifikasi kategori tersebut membantu peneliti dalam menginterpretasikan data kuantitatif menjadi keputusan kualitatif mengenai seberapa praktis dan mudah sistem ULT dioperasikan oleh civitas akademika.":
        "Penerapan rentang kriteria ini berfungsi sebagai tolok ukur final untuk menyimpulkan keberhasilan produk dari perspektif operasional lapangan. Klasifikasi kategori tersebut membantu peneliti dalam menginterpretasikan data kuantitatif menjadi keputusan kualitatif mengenai seberapa praktis dan mudah sistem ULT dioperasikan oleh civitas akademika. Ketegasan dalam pembagian interval skala ini menjamin bahwa konklusi akhir penelitian bersifat objektif serta terukur secara statistik.",

        "Distribusi perolehan nilai dari ketiga validator ahli tersebut mengafirmasi bahwa prototipe sistem telah melampaui batas ambang kelayakan pengembangan produk. Pencapaian ini mengindikasikan arsitektur website, desain antarmuka, dan substansi layanan sudah terintegrasi dengan kokoh dan amat siap untuk didistribusikan pada tahap implementasi lapangan.":
        "Distribusi perolehan nilai dari ketiga validator ahli tersebut mengafirmasi bahwa prototipe sistem telah melampaui batas ambang kelayakan pengembangan produk. Pencapaian ini mengindikasikan arsitektur website, desain antarmuka, dan substansi layanan sudah terintegrasi dengan solid dan amat siap untuk diujicobakan pada tahap implementasi nyata. Rekomendasi konstruktif yang menyertai perolehan nilai tersebut juga telah diakomodasi sepenuhnya untuk menuntaskan penyempurnaan mikro sistem.",

        "Standar batas minimum produk website ULT yang dikembangkan dapat dikategorikan layak apabila pencapaian persentase dari validator menyentuh angka ≥ 61% (Cukup Valid).":
        "Standar batas minimum produk website ULT yang dikembangkan dapat dikategorikan layak apabila pencapaian persentase dari validator menyentuh angka ≥ 61% (Cukup Valid). Parameter ini disesuaikan dengan urgensi kebutuhan di lapangan yang menuntut peluncuran produk secara efisien. Pencapaian nilai di bawah ambang batas akan secara otomatis mewajibkan peneliti untuk mengulangi perombakan arsitektur dari awal.",

        "Kajian terhadap berbagai riset terdahulu menunjukkan bahwa evaluasi sistem layanan kampus berbasis website umumnya menekankan kualitas layanan digital dari sisi persepsi pengguna (End-User Computing Satisfaction).":
        "Kajian terhadap berbagai riset terdahulu menunjukkan bahwa evaluasi sistem layanan kampus berbasis website umumnya menekankan kualitas layanan digital dari sisi persepsi pengguna (End-User Computing Satisfaction). Orientasi tersebut menegaskan bahwa fungsionalitas teknis harus selalu dikalibrasi dengan kemudahan dan kenyamanan penggunaan antarmuka. Paradigma riset terdahulu ini juga mengukuhkan pentingnya keterlibatan sivitas akademika dalam setiap tahapan pengembangan prototipe perangkat lunak."
    }

    count_replaced = 0
    for p in doc.paragraphs:
        txt = p.text.strip()
        for k, v in replacements.items():
            if txt == k or txt.startswith(k[:50]):
                p.text = v
                count_replaced += 1
                if 'Highlighted' in doc_path:
                    from docx.oxml.ns import qn
                    from docx.oxml import OxmlElement
                    for r in p.runs:
                        rPr = r._r.get_or_add_rPr()
                        highlight = OxmlElement('w:highlight')
                        highlight.set(qn('w:val'), 'yellow')
                        rPr.append(highlight)
    
    if count_replaced > 0:
        doc.save(doc_path)
        print(f"Replaced {count_replaced} paragraphs to have 3 sentences in {doc_path}")

if __name__ == '__main__':
    expand_paragraphs(r'001_Skripsi_Andricha Dea Mitra_Clean.docx')
    expand_paragraphs(r'001_Skripsi_Andricha Dea Mitra_Highlighted.docx')
