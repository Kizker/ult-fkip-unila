import sys
import docx
from pathlib import Path

# Set stdout to UTF-8
sys.stdout.reconfigure(encoding='utf-8')

def main():
    path = Path(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx")
    doc = docx.Document(path)
    
    in_kesimpulan = False
    for i, p in enumerate(doc.paragraphs):
        txt = p.text.strip()
        if "Kesimpulan" in txt and p.style.name.startswith("Heading"):
            in_kesimpulan = True
        elif in_kesimpulan and "B. Saran" in txt:
            in_kesimpulan = False
            
        if in_kesimpulan:
            print(f"P {i} ({p.style.name}): '{txt}'")

if __name__ == "__main__":
    main()
