import docx

def verify_all_tables(doc_path):
    doc = docx.Document(doc_path)
    body = doc._body._body
    
    elements = []
    for child in body:
        if child.tag.endswith('p'):
            p = docx.text.paragraph.Paragraph(child, doc._body)
            elements.append(('p', p.text.strip()))
        elif child.tag.endswith('tbl'):
            elements.append(('t', 'TABLE'))
            
    print(f"--- Verification for {doc_path} ---")
    for i, (etype, text) in enumerate(elements):
        if etype == 't':
            # find title of the table backwards
            title = ""
            for j in range(i-1, -1, -1):
                if elements[j][0] == 'p' and 'Tabel' in elements[j][1]:
                    title = elements[j][1]
                    break
            
            # Find the true following paragraph
            following = ""
            for j in range(i+1, len(elements)):
                if elements[j][0] == 'p':
                    txt = elements[j][1]
                    # skip empty lines and "Sumber:"
                    if txt != '' and not txt.startswith('Sumber:'):
                        following = txt
                        break
            
            # Print only if it has a title
            if title:
                print(f"Tabel: {title[:80]}...")
                print(f"  --> Follow-up: {following[:100]}...\n")

if __name__ == '__main__':
    verify_all_tables(r'001_Skripsi_Andricha Dea Mitra_Clean.docx')
