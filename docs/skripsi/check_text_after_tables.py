import docx

doc_path = r'c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx'
doc = docx.Document(doc_path)

paragraphs = [p.text.strip() for p in doc.paragraphs if p.text.strip()]

for i, p in enumerate(paragraphs):
    if 'Tabel' in p and 'Rekapitulasi' in p:
        print(f"FOUND: {p}")
        print("Following text:")
        for j in range(1, 4):
            if i+j < len(paragraphs):
                print(f"+{j}: {paragraphs[i+j][:150]}")
