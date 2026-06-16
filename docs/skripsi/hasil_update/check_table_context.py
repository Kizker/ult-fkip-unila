import docx

def check_table_context(doc_path):
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
        if etype == 't':
            # find title of the table (it's usually before)
            title = ""
            for j in range(i-1, -1, -1):
                if elements[j][0] == 'p' and 'Tabel' in elements[j][1]:
                    title = elements[j][1]
                    break
            
            print(f"Table Found (Index {i}): {title}")
            
            # Print the next 5 paragraphs after the table
            print("  Following paragraphs:")
            count = 0
            for j in range(i+1, len(elements)):
                if elements[j][0] == 'p':
                    txt = elements[j][1]
                    print(f"    - {txt[:100]}")
                    count += 1
                    if count >= 3:
                        break
            print("-" * 50)

if __name__ == '__main__':
    check_table_context(r'001_Skripsi_Andricha Dea Mitra_Clean.docx')
