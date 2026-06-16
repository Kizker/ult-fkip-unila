import docx
from pathlib import Path

def main():
    path = Path(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx")
    doc = docx.Document(path)
    
    in_bab3 = False
    in_bab4 = False
    in_bab5 = False
    
    print("=== BAB III Paragraphs ===")
    for i, p in enumerate(doc.paragraphs):
        txt = p.text.strip()
        if "METODE PENELITIAN" in txt.upper() and p.style.name.startswith("Heading"):
            in_bab3 = True
            continue
        if "HASIL DAN PEMBAHASAN" in txt.upper() and p.style.name.startswith("Heading"):
            in_bab3 = False
            in_bab4 = True
            continue
        if "KESIMPULAN DAN SARAN" in txt.upper() and p.style.name.startswith("Heading"):
            in_bab4 = False
            in_bab5 = True
            continue
        if "DAFTAR PUSTAKA" in txt.upper() and (p.style.name.startswith("Heading") or p.style.name == "Normal"):
            in_bab5 = False
            continue
            
        if in_bab3:
            # Print if contains certain keywords
            keywords = ["purposive", "sampling", "aiken", "validator", "9 orang", "18 orang", "ahli", "kepraktisan", "validitas"]
            match = any(kw in txt.lower() for kw in keywords)
            if match or p.style.name.startswith("Heading"):
                print(f"P {i} ({p.style.name}): {txt[:120]}...")

if __name__ == "__main__":
    main()
