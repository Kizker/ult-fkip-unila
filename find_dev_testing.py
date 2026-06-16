import docx

doc = docx.Document(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx")

for i, p in enumerate(doc.paragraphs):
    text = p.text.strip()
    if "Development" in text or "Testing" in text or "testing" in text or "developmental" in text.lower():
        print(f"Line {i}: {text}")
