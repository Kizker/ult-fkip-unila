import docx

def read_chap2(filename):
    doc = docx.Document(filename)
    in_chap2 = False
    for p in doc.paragraphs:
        text = p.text.strip()
        if "BAB II" in text.upper() or "TINJAUAN PUSTAKA" in text.upper() or "KAJIAN PUSTAKA" in text.upper():
            in_chap2 = True
        if "BAB III" in text.upper():
            break
            
        if in_chap2 and p.style.name.startswith('Heading'):
            print(f"Heading: {text}")

read_chap2("001_Skripsi_Andricha Dea Mitra_Clean.docx")
