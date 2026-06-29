import docx
doc = docx.Document(r'C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx')
with open('extract.txt', 'w', encoding='utf-8') as f:
    for p in doc.paragraphs:
        lower = p.text.lower()
        if any(w in lower for w in ['ahli', 'validator', 'revisi', 'saran', 'tabel', 'ikon', 'warna', 'interaksi']):
            f.write(p.text + '\n')
