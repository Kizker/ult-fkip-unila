import docx

doc_path = r'c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx'
doc = docx.Document(doc_path)

for p in doc.paragraphs:
    if 'Lampiran 11. Matriks Hasil Skor Kuesioner Uji Kepraktisan Responden' in p.text:
        print("Found target paragraph!")
        print("Parent tag:", p._p.getparent().tag)
        break
