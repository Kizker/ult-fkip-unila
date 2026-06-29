import docx

def find_headings(filename):
    doc = docx.Document(filename)
    in_chap2 = False
    for i, p in enumerate(doc.paragraphs):
        text = p.text.strip()
        if "TINJAUAN PUSTAKA" in text.upper():
            in_chap2 = True
            
        if "METODE PENELITIAN" in text.upper():
            break
            
        if in_chap2 and p.style.name.startswith('Heading'):
            print(f"[{i}] {p.style.name}: {text}")

find_headings("001_Skripsi_Andricha Dea Mitra_Clean.docx")
