import pdfplumber
import sys

pdf_path = r"C:\laragon\www\ult-fkip-unila\docs\skripsi\contoh skripsi-ajeng.pdf"
try:
    with pdfplumber.open(pdf_path) as pdf:
        for i, page in enumerate(pdf.pages):
            text = page.extract_text()
            if text and ("saran" in text.lower() or "masukan" in text.lower() or "revisi" in text.lower()):
                print(f"--- Page {i+1} ---")
                tables = page.extract_tables()
                if tables:
                    for j, table in enumerate(tables):
                        print(f"Table {j+1}:")
                        for row in table:
                            print(row)
                else:
                    print(text[:500]) # Print first 500 chars to get an idea
except Exception as e:
    print(f"Error: {e}")
