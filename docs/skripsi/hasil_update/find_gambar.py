import docx

def find_images(doc_path):
    doc = docx.Document(doc_path)
    body = doc._body._body
    
    elements = []
    for child in body:
        if child.tag.endswith('p'):
            p = docx.text.paragraph.Paragraph(child, doc._body)
            elements.append(('p', p.text.strip()))
        elif child.tag.endswith('tbl'):
            elements.append(('t', 'TABLE'))
            
    for i, (etype, text) in enumerate(elements):
        if etype == 'p' and text.startswith('Gambar '):
            print(f"Image Caption Found (Index {i}): {text}")
            print("  Following paragraphs:")
            count = 0
            for j in range(i+1, len(elements)):
                if elements[j][0] == 'p':
                    txt = elements[j][1]
                    if txt != '':
                        print(f"    - {txt[:100]}")
                        count += 1
                    if count >= 2:
                        break
            print("-" * 50)

if __name__ == '__main__':
    find_images(r'001_Skripsi_Andricha Dea Mitra_Clean.docx')
