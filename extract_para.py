import docx
doc = docx.Document(r'C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx')
with open('extract_para.txt', 'w', encoding='utf-8') as f:
    for p in doc.paragraphs:
        lower = p.text.lower()
        if 'warna' in lower or 'tabel' in lower or 'ikon' in lower or 'interaksi' in lower:
            f.write(p.text + '\n---\n')
