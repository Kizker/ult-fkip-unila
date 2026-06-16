import docx

doc = docx.Document(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx")

start_printing = False
for i, p in enumerate(doc.paragraphs):
    text = p.text.strip()
    if text == "c. Developmental Testing":
        start_printing = True
    if start_printing:
        print(f"Line {i}: {text[:100]}...")
    if text == "4.1.4 Tahap Implementasi (Implementation)":
        break
