import sys
import docx
from pathlib import Path

# Set stdout to UTF-8
sys.stdout.reconfigure(encoding='utf-8')

def main():
    path = Path(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx")
    if not path.exists():
        print("Not found.")
        return
        
    doc = docx.Document(path)
    print(f"Loaded document. Search for SSO paragraphs...")
    
    for i, p in enumerate(doc.paragraphs):
        txt = p.text.strip()
        if "SSO" in txt or "Single Sign-On" in txt or "single sign-on" in txt:
            print(f"P {i} ({p.style.name}): '{txt[:150]}...'")

if __name__ == "__main__":
    main()
