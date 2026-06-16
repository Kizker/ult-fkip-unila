import docx

def read_section(doc_path):
    doc = docx.Document(doc_path)
    body = doc._body._body
    
    elements = []
    for child in body:
        if child.tag.endswith('p'):
            p = docx.text.paragraph.Paragraph(child, doc._body)
            elements.append(p.text.strip())
            
    # Find 4.1.2
    start_idx = -1
    end_idx = -1
    for i, text in enumerate(elements):
        if text.startswith('4.1.2') or "Tahap Desain" in text:
            if start_idx == -1:
                start_idx = i
        if start_idx != -1 and text.startswith('4.1.3'):
            end_idx = i
            break
            
    if start_idx != -1:
        end_idx = end_idx if end_idx != -1 else min(start_idx + 100, len(elements))
        print(f"--- Section 4.1.2 Content ---")
        for i in range(start_idx, end_idx):
            if elements[i]:
                print(f"[{i}] {elements[i][:150]}")
    else:
        print("Section not found.")

if __name__ == '__main__':
    read_section(r'001_Skripsi_Andricha Dea Mitra_Clean.docx')
