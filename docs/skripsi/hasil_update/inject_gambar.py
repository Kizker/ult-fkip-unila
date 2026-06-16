import docx
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

def add_highlight(run, is_highlighted):
    if is_highlighted:
        rPr = run._r.get_or_add_rPr()
        highlight = OxmlElement('w:highlight')
        highlight.set(qn('w:val'), 'yellow')
        rPr.append(highlight)

def insert_p_before(ref_p, text, style='Paragraph', is_highlighted=False):
    new_p = ref_p.insert_paragraph_before(style=style)
    if text:
        run = new_p.add_run(text)
        add_highlight(run, is_highlighted)
    return new_p

def inject_gambar_explanations(doc_path, is_highlighted):
    doc = docx.Document(doc_path)
    
    injections = {
        "Kerangka Berpikir": "Skema kerangka berpikir tersebut memaparkan alur logis penelitian mulai dari identifikasi masalah konvensional hingga perumusan solusi digital yang komprehensif. Titik berat permasalahan mengenai inefisiensi pengelolaan dokumen fisik dijawab secara langsung melalui perancangan arsitektur sistem informasi terintegrasi. Konstruksi pemikiran ini memandu arah pengembangan penelitian agar senantiasa berfokus pada urgensi digitalisasi pelayanan di Unit Layanan Terpadu.",
        "Alur Model Pengembangan ADDIE": "Visualisasi model ADDIE di atas menjabarkan kelima fase siklus hidup pengembangan sistem yang berjalan secara sekuensial dan berkesinambungan. Fase analisis bertindak sebagai fondasi awal, sedangkan fase desain hingga evaluasi menjadi pilar eksekusi teknis untuk mematangkan produk sebelum dirilis secara resmi. Siklus iteratif ini menjamin kualitas produk akhir yang dihasilkan terukur secara ilmiah, teruji secara praktis, dan siap merespons dinamika pelayanan."
    }
    
    body = doc._body._body
    
    elements = []
    for child in body:
        if child.tag.endswith('p'):
            p = docx.text.paragraph.Paragraph(child, doc._body)
            elements.append({'type': 'p', 'text': p.text.strip(), 'obj': p})

    injected_count = 0
    for i, elem in enumerate(elements):
        txt = elem['text']
        if txt.startswith("Gambar ") and "Kerangka Berpikir" in txt:
            # insertion point is right after
            insert_idx = i + 1
            while insert_idx < len(elements) and elements[insert_idx]['text'] == '':
                insert_idx += 1
            
            target_text = injections["Kerangka Berpikir"]
            already_injected = False
            if insert_idx - 1 > i and elements[insert_idx-1]['text'] == target_text:
                already_injected = True
                
            if not already_injected and insert_idx < len(elements):
                target_p = elements[insert_idx]['obj']
                insert_p_before(target_p, "", 'Normal')
                insert_p_before(target_p, target_text, 'Paragraph', is_highlighted)
                insert_p_before(target_p, "", 'Normal')
                injected_count += 1
                
        elif txt.startswith("Gambar ") and "Alur Model Pengembangan ADDIE" in txt:
            insert_idx = i + 1
            while insert_idx < len(elements) and (elements[insert_idx]['text'] == '' or elements[insert_idx]['text'].startswith('Sumber:')):
                insert_idx += 1
                
            target_text = injections["Alur Model Pengembangan ADDIE"]
            already_injected = False
            if insert_idx - 1 > i and elements[insert_idx-1]['text'] == target_text:
                already_injected = True
                
            if not already_injected and insert_idx < len(elements):
                target_p = elements[insert_idx]['obj']
                insert_p_before(target_p, "", 'Normal')
                insert_p_before(target_p, target_text, 'Paragraph', is_highlighted)
                insert_p_before(target_p, "", 'Normal')
                injected_count += 1

    if injected_count > 0:
        doc.save(doc_path)
        print(f"Saved {doc_path} with {injected_count} injections.")

if __name__ == '__main__':
    inject_gambar_explanations(r'001_Skripsi_Andricha Dea Mitra_Clean.docx', is_highlighted=False)
    inject_gambar_explanations(r'001_Skripsi_Andricha Dea Mitra_Highlighted.docx', is_highlighted=True)
