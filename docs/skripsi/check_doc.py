import docx
import sys

def inspect_doc(doc_path):
    print(f"Inspecting: {doc_path}")
    try:
        doc = docx.Document(doc_path)
        for i, p in enumerate(doc.paragraphs):
            if "Heading 3" in p.style.name:
                print(f"Paragraph {i}: {p.text.strip()} (Style: {p.style.name})")
    except Exception as e:
        print(f"Error: {e}")

inspect_doc(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Highlighted.docx")
