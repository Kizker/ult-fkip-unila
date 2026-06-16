import docx

def check_images(doc_path):
    doc = docx.Document(doc_path)
    for i, p in enumerate(doc.paragraphs):
        text = p.text.strip()
        if "Gambar" in text:
            print(f"[{i}] {text}")
        xml = p._p.xml
        if 'w:drawing' in xml or 'pic:pic' in xml or 'v:imagedata' in xml or 'v:shape' in xml:
            print(f"[{i}] HAS IMAGE. Text length: {len(text)}. Text snippet: {text[:30]}")

if __name__ == '__main__':
    print("=== Original ===")
    check_images(r'c:\laragon\www\ult-fkip-unila\docs\skripsi\update skripsi terakhir-seminar proposal\001_Skripsi_Andricha Dea Mitra.docx')
