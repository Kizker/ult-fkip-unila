import pdfplumber

pdf_path = r"C:\laragon\www\ult-fkip-unila\docs\skripsi\contoh skripsi-ajeng.pdf"

with pdfplumber.open(pdf_path) as pdf:
    for page in pdf.pages:
        text = page.extract_text()
        if text and "Masukan" in text:
            print("Found on page:", page.page_number)
            print("---------------------------------")
            print(text)
            print("=================================\n")
