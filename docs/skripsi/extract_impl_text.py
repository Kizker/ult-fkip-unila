import sys
import docx
from pathlib import Path

# Set stdout to UTF-8
sys.stdout.reconfigure(encoding='utf-8')

def main():
    path = Path(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx")
    doc = docx.Document(path)
    
    out_path = Path("docs/skripsi/implementation_text.txt")
    with open(out_path, "w", encoding="utf-8") as f:
        in_impl = False
        for i, p in enumerate(doc.paragraphs):
            txt = p.text.strip()
            if "Tahap Implementasi (Implementation)" in txt:
                in_impl = True
            elif "5. Tahap Evaluasi (Evaluation)" in txt:
                in_impl = False
                
            if in_impl:
                f.write(f"P {i} ({p.style.name}): '{txt}'\n\n")
                
    print(f"Implementation text saved to {out_path}")

if __name__ == "__main__":
    main()
