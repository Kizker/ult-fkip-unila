import docx

doc = docx.Document(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx")

# Let's find "4.1.4 Tahap Implementasi"
found = False
for i, p in enumerate(doc.paragraphs):
    if "4.1.4 Tahap Implementasi" in p.text:
        found = True
    if found:
        print(f"Line {i}: {p.text}")
    if "4.1.5 Tahap Evaluasi" in p.text:
        break
