import docx

def revise_summary_paragraph(doc_path):
    doc = docx.Document(doc_path)
    
    old_text = "Berdasarkan rincian tersebut, secara keseluruhan subjek dalam penelitian ini berjumlah 27 orang yang dipilih melalui teknik purposive sampling. Tahap awal melibatkan 9 validator ahli yang bertugas mengevaluasi kelayakan aspek materi, media, dan sistem sebelum prototipe diujicobakan. Setelah dinyatakan valid, tahap selanjutnya melibatkan 18 responden pengguna dari berbagai elemen (mahasiswa, staf ULT, dan admin jurusan) untuk mengukur tingkat kepraktisan sistem di lapangan. Pelibatan berbagai unsur civitas akademika ini bertujuan agar umpan balik yang diperoleh bersifat komprehensif, representatif, dan relevan dengan alur birokrasi pelayanan dokumen di Fakultas Keguruan dan Ilmu Pendidikan Universitas Lampung."
    
    new_text = "Penetapan 27 orang subjek dalam penelitian ini dilandasi oleh kebutuhan untuk mendapatkan umpan balik yang komprehensif dari pihak-pihak yang bersinggungan langsung dengan sistem. Sembilan validator ahli di tahap awal memegang peran krusial dalam memastikan prototipe benar-benar layak secara fungsional, tepat secara konten, dan rumpang dari galat sebelum menyentuh lapangan. Delapan belas responden uji coba yang merupakan representasi proporsional mahasiswa, staf operasional ULT, serta administrator jurusan kemudian dihadirkan untuk mengukur efektivitas dan kemudahan penggunaan aplikasi secara nyata. Komposisi terarah ini dirancang spesifik agar evaluasi produk benar-benar mencerminkan dinamika birokrasi dan kebutuhan pelayanan dokumen administrasi di lingkungan Fakultas Keguruan dan Ilmu Pendidikan Universitas Lampung."
    
    replaced = False
    for p in doc.paragraphs:
        if p.text.strip() == old_text:
            p.text = new_text
            # Preserve the highlight if it's the highlighted doc
            if 'Highlighted' in doc_path:
                for r in p.runs:
                    from docx.oxml.ns import qn
                    from docx.oxml import OxmlElement
                    rPr = r._r.get_or_add_rPr()
                    highlight = OxmlElement('w:highlight')
                    highlight.set(qn('w:val'), 'yellow')
                    rPr.append(highlight)
            replaced = True
            print(f"Replaced text in {doc_path}")

    if replaced:
        doc.save(doc_path)
    else:
        print(f"Could not find the target text in {doc_path}")

if __name__ == '__main__':
    revise_summary_paragraph(r'001_Skripsi_Andricha Dea Mitra_Clean.docx')
    revise_summary_paragraph(r'001_Skripsi_Andricha Dea Mitra_Highlighted.docx')
