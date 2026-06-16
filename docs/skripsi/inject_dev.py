import docx
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.shared import Pt

def extract_body_elements(document):
    return list(document._body._body)

def inject(doc_path, output_path):
    doc = docx.Document(doc_path)
    
    in_dev = False
    in_impl = False
    
    dev_table_found = False
    
    for el in extract_body_elements(doc):
        if el.tag.endswith('p'):
            p = docx.text.paragraph.Paragraph(el, doc)
            text = p.text.strip()
            if 'Tahap Pengembangan' in text:
                in_dev = True
            elif 'Tahap Implementasi' in text:
                in_dev = False
                in_impl = True
            elif 'Tahap Evaluasi' in text:
                in_impl = False
                
        elif el.tag.endswith('tbl'):
            if in_dev and not dev_table_found:
                # We found the Dev Table!
                new_p_xml = docx.oxml.OxmlElement('w:p')
                el.addnext(new_p_xml)
                new_p = docx.text.paragraph.Paragraph(new_p_xml, doc)
                new_p.text = "Berdasarkan Tabel 4.1 di atas, para ahli validator memberikan berbagai masukan kualitatif yang konstruktif untuk penyempurnaan sistem. Beberapa perbaikan utama yang disarankan meliputi penguatan keamanan sistem melalui penerapan Content Security Policy (CSP), perbaikan logika validasi form untuk mencegah input yang terlewat, serta penambahan fitur navigasi pendukung guna meningkatkan pengalaman pengguna (user experience). Seluruh masukan tersebut telah diakomodasi dan ditindaklanjuti secara langsung pada tahap pengembangan."
                new_p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
                new_p.style = doc.styles['Normal']
                new_p.paragraph_format.first_line_indent = Pt(36)
                print("Injected Dev explanation after table.")
                dev_table_found = True
                
    doc.save(output_path)

inject(
    r'c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx',
    r'c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx'
)

inject(
    r'c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Highlighted.docx',
    r'c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Highlighted.docx'
)
