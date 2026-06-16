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

def patch_missing_and_template_texts(doc_path, is_highlighted):
    doc = docx.Document(doc_path)
    body = doc._body._body
    
    elements = []
    for child in body:
        if child.tag.endswith('p'):
            p = docx.text.paragraph.Paragraph(child, doc._body)
            elements.append({'type': 'p', 'text': p.text.strip(), 'obj': p})
        elif child.tag.endswith('tbl'):
            elements.append({'type': 't', 'text': 'TABLE', 'obj': None})

    injections = 0
    replacements = 0

    # Process elements
    for i, elem in enumerate(elements):
        if elem['type'] == 't':
            # find title backwards
            title_match = None
            for j in range(i-1, -1, -1):
                if elements[j]['type'] == 'p' and 'Tabel' in elements[j]['text']:
                    title_match = elements[j]['text']
                    break
            
            if title_match and "Kisi-kisi Analisis Kebutuhan" in title_match:
                # Find insertion point
                insert_idx = i + 1
                while insert_idx < len(elements):
                    if elements[insert_idx]['type'] == 'p':
                        txt = elements[insert_idx]['text']
                        if not txt.startswith('Sumber:') and txt != '':
                            break
                    insert_idx += 1
                
                # Check if already injected
                target_text = "Struktur instrumen analisis kebutuhan ini dirancang sedemikian rupa untuk menggali perspektif pengguna secara mendalam terkait urgensi digitalisasi layanan. Respons dari partisipan terhadap kisi-kisi ini akan menjadi landasan empiris bagi peneliti dalam merumuskan arsitektur awal dan fitur-fitur esensial yang harus dibangun pada prototipe website."
                already_injected = False
                if insert_idx - 1 > i and elements[insert_idx-1]['type'] == 'p':
                    if elements[insert_idx-1]['text'] == target_text:
                        already_injected = True
                
                if not already_injected and insert_idx < len(elements):
                    target_p = elements[insert_idx]['obj']
                    insert_p_before(target_p, "", 'Normal')
                    insert_p_before(target_p, target_text, 'Paragraph', is_highlighted)
                    insert_p_before(target_p, "", 'Normal')
                    injections += 1

        elif elem['type'] == 'p':
            txt = elem['text']
            if txt.startswith("Berdasarkan tabel di atas, standar batas minimum produk website ULT"):
                new_text = txt.replace(
                    "Berdasarkan tabel di atas, standar batas minimum produk website ULT yang dikembangkan dapat dikategorikan",
                    "Standar batas minimum produk website ULT yang dikembangkan dapat dikategorikan"
                )
                elem['obj'].text = new_text
                if is_highlighted:
                    for r in elem['obj'].runs:
                        add_highlight(r, True)
                replacements += 1
            
            elif txt.startswith("Berdasarkan penelitian terdahulu, evaluasi sistem layanan kampus"):
                new_text = txt.replace(
                    "Berdasarkan penelitian terdahulu, evaluasi sistem layanan kampus",
                    "Kajian terhadap berbagai riset terdahulu menunjukkan bahwa evaluasi sistem layanan kampus"
                )
                elem['obj'].text = new_text
                if is_highlighted:
                    for r in elem['obj'].runs:
                        add_highlight(r, True)
                replacements += 1

    if injections > 0 or replacements > 0:
        doc.save(doc_path)
        print(f"Saved {doc_path} with {injections} injections and {replacements} replacements.")
    else:
        print(f"No changes needed for {doc_path}")

if __name__ == '__main__':
    patch_missing_and_template_texts(r'001_Skripsi_Andricha Dea Mitra_Clean.docx', is_highlighted=False)
    patch_missing_and_template_texts(r'001_Skripsi_Andricha Dea Mitra_Highlighted.docx', is_highlighted=True)
