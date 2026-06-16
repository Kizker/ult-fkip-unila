import docx
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

def add_highlight(run, is_highlighted):
    if is_highlighted:
        rPr = run._r.get_or_add_rPr()
        highlight = OxmlElement('w:highlight')
        highlight.set(qn('w:val'), 'yellow')
        rPr.append(highlight)

def insert_p_before(ref_p, text, style='Paragraph', is_highlighted=False, indent=False):
    new_p = ref_p.insert_paragraph_before(style=style)
    if text:
        run = new_p.add_run(text)
        add_highlight(run, is_highlighted)
    return new_p

def apply_table_paragraphs(doc_path, is_highlighted):
    doc = docx.Document(doc_path)
    
    # We will map table titles to paragraphs
    injections = {
        "Waktu Pelaksanaan Penelitian": "Rincian jadwal pelaksanaan penelitian tersebut dirancang secara sistematis untuk memastikan seluruh tahapan mulai dari analisis awal hingga evaluasi akhir berjalan terukur. Alokasi waktu selama satu semester penuh memungkinkan peneliti untuk melakukan iterasi perbaikan secara mendalam tanpa mengorbankan kualitas validitas ahli maupun pengujian di lapangan.",
        "Kisi-kisi Validasi Ahli Materi": "Instrumen penilaian ahli materi difokuskan pada aspek kelayakan konten dan kualitas substansi informasi. Indikator ini disusun guna memastikan bahwa deskripsi layanan, kelengkapan syarat dokumen, dan alur prosedur yang disajikan dalam website telah sepenuhnya akurat serta sesuai dengan standar operasional baku akademik di lingkungan fakultas.",
        "Kisi-kisi Validasi Ahli Media": "Indikator penilaian ahli media menitikberatkan pada aspek estetika antarmuka, ergonomi navigasi, dan keterbacaan visual. Aspek ini dievaluasi secara ketat untuk menjamin pengalaman pengguna yang intuitif serta tata letak yang tetap proporsional dan responsif saat diakses melalui berbagai macam perangkat.",
        "Kisi-kisi Validasi Ahli Sistem": "Kisi-kisi penilaian ahli sistem ditujukan untuk menguji keandalan infrastruktur perangkat lunak, fungsionalitas basis data, serta keamanan autentikasi. Validasi di tahap ini memegang peranan vital dalam mendeteksi potensi celah keamanan maupun cacat fungsional (bug) guna menjamin stabilitas performa sebelum website dirilis ke lingkungan pengguna nyata.",
        "Kisi-kisi Uji Kepraktisan (Angket)": "Penjabaran indikator uji kepraktisan ini diarahkan pada pengukuran efektivitas dan efisiensi produk saat dioperasikan langsung oleh pengguna akhir. Instrumen ini sekaligus menjadi alat pengukur tingkat penerimaan mahasiswa dan staf terhadap transisi digitalisasi pelayanan administrasi akademik berbasis web.",
        "Kriteria Penafsiran Kepraktisan Produk": "Penerapan rentang kriteria ini berfungsi sebagai tolok ukur final untuk menyimpulkan keberhasilan produk dari perspektif operasional lapangan. Klasifikasi kategori tersebut membantu peneliti dalam menginterpretasikan data kuantitatif menjadi keputusan kualitatif mengenai seberapa praktis dan mudah sistem ULT dioperasikan oleh civitas akademika.",
        "Rekapitulasi Hasil Validasi Ahli": "Distribusi perolehan nilai dari ketiga validator ahli tersebut mengafirmasi bahwa prototipe sistem telah melampaui batas ambang kelayakan pengembangan produk. Pencapaian ini mengindikasikan arsitektur website, desain antarmuka, dan substansi layanan sudah terintegrasi dengan kokoh dan amat siap untuk didistribusikan pada tahap implementasi lapangan."
    }
    
    body = doc._body._body
    
    elements = []
    # keep track of both xml element and docx paragraph wrapper
    for child in body:
        if child.tag.endswith('p'):
            p = docx.text.paragraph.Paragraph(child, doc._body)
            elements.append({'type': 'p', 'text': p.text.strip(), 'obj': p})
        elif child.tag.endswith('tbl'):
            elements.append({'type': 't', 'text': 'TABLE', 'obj': None})

    injected_count = 0
    # Process elements
    for i, elem in enumerate(elements):
        if elem['type'] == 't':
            # find title backwards
            title_match = None
            for j in range(i-1, -1, -1):
                if elements[j]['type'] == 'p' and 'Tabel' in elements[j]['text']:
                    for key in injections:
                        if key in elements[j]['text']:
                            title_match = key
                            break
                    break
            
            if title_match:
                # Find the insertion point:
                # Look at the elements directly after the table.
                # If there's a "Sumber:", skip it. We want to insert BEFORE the first paragraph
                # that is NOT empty and NOT "Sumber:".
                insert_idx = i + 1
                while insert_idx < len(elements):
                    if elements[insert_idx]['type'] == 'p':
                        txt = elements[insert_idx]['text']
                        if not txt.startswith('Sumber:') and txt != '':
                            break
                    insert_idx += 1
                
                # Check if we already injected this text so we don't duplicate
                # We check the paragraph immediately before insert_idx
                already_injected = False
                if insert_idx - 1 > i and elements[insert_idx-1]['type'] == 'p':
                    if elements[insert_idx-1]['text'] == injections[title_match]:
                        already_injected = True
                
                if not already_injected:
                    if insert_idx < len(elements) and elements[insert_idx]['type'] == 'p':
                        target_p = elements[insert_idx]['obj']
                        # Insert empty line first for spacing
                        insert_p_before(target_p, "", 'Normal')
                        insert_p_before(target_p, injections[title_match], 'Paragraph', is_highlighted)
                        # We also want an empty line after
                        insert_p_before(target_p, "", 'Normal')
                        injected_count += 1
                        print(f"Injected paragraph for: {title_match}")
                    else:
                        print(f"Could not find a place to inject for {title_match}")

    if injected_count > 0:
        doc.save(doc_path)
        print(f"Saved {doc_path} with {injected_count} injections.")
    else:
        print(f"No injections needed or found for {doc_path}")

if __name__ == '__main__':
    apply_table_paragraphs(r'001_Skripsi_Andricha Dea Mitra_Clean.docx', is_highlighted=False)
    apply_table_paragraphs(r'001_Skripsi_Andricha Dea Mitra_Highlighted.docx', is_highlighted=True)
