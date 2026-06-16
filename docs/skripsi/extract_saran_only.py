import sys
import docx
from pathlib import Path

# Set stdout to UTF-8
sys.stdout.reconfigure(encoding='utf-8')

def main():
    path = Path(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx")
    doc = docx.Document(path)
    
    in_saran = False
    for i, p in enumerate(doc.paragraphs):
        txt = p.text.strip()
        if "B. Saran" in txt:
            in_saran = True
        elif in_saran and "DAFTAR PUSTAKA" in txt.upper() and (p.style.name.startswith("Heading") or p.style.name == "Normal"):
            in_saran = False
            
        if in_saran:
            print(f"P {i} ({p.style.name}): '{txt}'")

if __name__ == "__main__":
    main()
