import docx

doc = docx.Document(r'c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx')
printing = False
for p in doc.paragraphs:
    if 'Kesimpulan' in p.text and len(p.text) < 50:
        printing = True
    elif 'Saran' in p.text and len(p.text) < 50:
        printing = False
    if printing and p.text.strip():
        print(p.text.strip())
