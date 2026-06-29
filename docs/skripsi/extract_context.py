import docx

doc_path = r"c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx"
doc = docx.Document(doc_path)

latar_belakang = []
landasan_teori = []
pembahasan = []

state = 0
for p in doc.paragraphs:
    text = p.text.strip()
    if not text: continue
    
    if text.lower() == "latar belakang":
        state = 1
    elif text.lower() == "rumusan masalah":
        if state == 1: state = 0
    elif text.lower() == "landasan teori":
        state = 2
    elif text.lower() == "kerangka berpikir":
        if state == 2: state = 0
    elif "pembahasan" in text.lower() and "kendala" in text.lower():
        state = 3
    elif text.lower() == "bab v" or text.lower() == "kesimpulan":
        if state == 3: state = 0
        
    if state == 1:
        latar_belakang.append(text)
    elif state == 2:
        landasan_teori.append(text)
    elif state == 3:
        pembahasan.append(text)

with open(r"c:\laragon\www\ult-fkip-unila\docs\skripsi\extracted_context.txt", "w", encoding="utf-8") as f:
    f.write("=== LATAR BELAKANG ===\n")
    f.write("\n".join(latar_belakang))
    f.write("\n\n=== LANDASAN TEORI ===\n")
    f.write("\n".join(landasan_teori))
    f.write("\n\n=== PEMBAHASAN DAN KENDALA ===\n")
    f.write("\n".join(pembahasan))

print("Extraction complete.")
