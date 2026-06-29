import sys
try:
    from docx import Document
except ImportError:
    import subprocess
    subprocess.check_call([sys.executable, "-m", "pip", "install", "python-docx"])
    from docx import Document

doc_path = r"c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx"
doc = Document(doc_path)

with open(r"c:\laragon\www\ult-fkip-unila\docs\skripsi\extracted_skripsi.txt", "w", encoding="utf-8") as f:
    for i, p in enumerate(doc.paragraphs):
        text = p.text.strip()
        if text:
            f.write(f"[{i}] {text}\n")
