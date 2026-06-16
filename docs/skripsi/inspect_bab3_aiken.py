import docx
from pathlib import Path

def main():
    path = Path(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx")
    if not path.exists():
        print("Clean doc not found.")
        return
        
    doc = docx.Document(path)
    print("Scanning for 'Aiken' or 'validitas' in paragraphs...")
    for idx, p in enumerate(doc.paragraphs):
        txt = p.text.strip()
        if "Aiken" in txt or "validitas" in txt.lower():
            if idx < 712: # only look in Bab 3 or before
                print(f"P {idx}: '{txt[:100]}...'")

if __name__ == "__main__":
    main()
