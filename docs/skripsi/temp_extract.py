import docx

doc = docx.Document(r'c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx')
printing = False
for p in doc.paragraphs:
    if 'Rumusan Masalah' in p.text:
        printing = True
    elif 'Manfaat Penelitian' in p.text:
        printing = False
        break
    if printing and p.text.strip():
        print(p.text.strip())
