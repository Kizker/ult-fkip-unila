import docx

doc_path = r'c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx'
doc = docx.Document(doc_path)

found = False
content = []

for p in doc.paragraphs:
    text = p.text.strip()
    if text == "4.1.2 Tahap Desain" or text.startswith("4.1.2 Tahap Desain"):
        found = True
        content.append(text)
        continue
    if found:
        if text.startswith("4.1.3 Tahap Pengembangan"):
            break
        content.append(text)

with open('c:/laragon/www/ult-fkip-unila/docs/skripsi/hasil_update/tahap_desain.txt', 'w', encoding='utf-8') as f:
    f.write('\n'.join(content))
