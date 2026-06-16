import docx

doc = docx.Document(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx")

for i, p in enumerate(doc.paragraphs):
    if "Responden 1" in p.text or "Airtable" in p.text or "Admin Program Studi" in p.text:
        print(f"Line {i}: {p.text}")
