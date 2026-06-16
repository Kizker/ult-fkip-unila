import pdfplumber

pdf_path = r"C:\laragon\www\ult-fkip-unila\docs\skripsi\contoh skripsi-ajeng.pdf"

with pdfplumber.open(pdf_path) as pdf:
    # Just print the first 500 characters of the document to see if text is extractable
    text = ""
    for page in pdf.pages:
        t = page.extract_text()
        if t:
            text += t + "\n"
    print("Extracted text length:", len(text))
    
    if "Masukan" in text:
        print("Found 'Masukan'")
    else:
        print("Did not find 'Masukan'")
        
    print(text[:500])
