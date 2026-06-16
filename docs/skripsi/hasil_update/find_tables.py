import docx

def find_tables_context(doc_path):
    doc = docx.Document(doc_path)
    
    # In docx, elements (paragraphs, tables) are sequential in the document body.
    # To get their sequence, we can iterate over doc._body._body
    
    body = doc._body._body
    
    elements = []
    for child in body:
        if child.tag.endswith('p'):
            # paragraph
            p = docx.text.paragraph.Paragraph(child, doc._body)
            elements.append(('p', p.text.strip()))
        elif child.tag.endswith('tbl'):
            elements.append(('t', 'TABLE'))
            
    # Now let's print context around tables
    for i, (etype, text) in enumerate(elements):
        if etype == 't':
            # find previous paragraph
            prev_text = "(None)"
            for j in range(i-1, -1, -1):
                if elements[j][0] == 'p' and elements[j][1]:
                    prev_text = elements[j][1]
                    break
            # find next paragraph
            next_text = "(None)"
            for j in range(i+1, len(elements)):
                if elements[j][0] == 'p' and elements[j][1]:
                    next_text = elements[j][1]
                    break
                    
            print(f"Table Found (Index {i}):")
            print(f"  Prev: {prev_text[:150]}")
            print(f"  Next: {next_text[:150]}")
            print("-" * 50)

if __name__ == '__main__':
    find_tables_context(r'001_Skripsi_Andricha Dea Mitra_Clean.docx')
