import docx

doc_path = r"c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx"
doc = docx.Document(doc_path)

bab4_content = []
in_bab4 = False

for p in doc.paragraphs:
    text = p.text.strip()
    if not text: continue
    
    # Simple logic to capture BAB IV
    if text.upper().startswith("BAB IV") or "HASIL DAN PEMBAHASAN" in text.upper():
        in_bab4 = True
    elif text.upper().startswith("BAB V") or text.upper() == "KESIMPULAN":
        in_bab4 = False
        
    if in_bab4:
        bab4_content.append(text)

with open(r"c:\laragon\www\ult-fkip-unila\docs\skripsi\bab4_only.txt", "w", encoding="utf-8") as f:
    f.write("\n".join(bab4_content))

print("Extraction complete. Lines:", len(bab4_content))
