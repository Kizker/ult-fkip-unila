import docx
from pathlib import Path

def main():
    path = Path(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx")
    if not path.exists():
        print("Not found.")
        return
        
    doc = docx.Document(path)
    print("=== Document paragraphs with Lampiran ===")
    for idx, p in enumerate(doc.paragraphs):
        if "lampiran" in p.text.lower():
            print(f"P {idx} ({p.style.name}): '{p.text}'")

if __name__ == "__main__":
    main()
