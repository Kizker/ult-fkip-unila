import docx

def get_tempat_waktu(filename):
    doc = docx.Document(filename)
    in_tempat = False
    for p in doc.paragraphs:
        text = p.text.strip()
        if p.style.name.startswith('Heading 2') and "Tempat dan Waktu Penelitian" in text:
            in_tempat = True
            print(f"--- START TEMPAT ---")
            continue
        elif in_tempat and p.style.name.startswith('Heading 2'):
            print(f"--- END TEMPAT ---")
            break
            
        if in_tempat and text:
            print(text)

get_tempat_waktu("001_Skripsi_Andricha Dea Mitra_Clean.docx")
