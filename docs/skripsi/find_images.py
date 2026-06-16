import docx
import os

doc_path = r'c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx'
doc = docx.Document(doc_path)

for p in doc.paragraphs:
    if 'Gambar 4.' in p.text:
        print(p.text.strip())
