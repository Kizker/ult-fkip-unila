import docx

def read_early_paras(filename):
    doc = docx.Document(filename)
    count = 0
    for p in doc.paragraphs:
        text = p.text.strip()
        if len(text) > 100 and p.style.name == 'Normal':
            print(f"--- Paragraph {count+1} ---")
            print(text)
            count += 1
            if count >= 3:
                break

read_early_paras("001_Skripsi_Andricha Dea Mitra_Clean.docx")
