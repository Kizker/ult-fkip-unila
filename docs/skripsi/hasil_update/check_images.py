import docx

def check_images(doc_path):
    doc = docx.Document(doc_path)
    for i, p in enumerate(doc.paragraphs):
        text = p.text.strip()
        if "Gambar" in text:
            print(f"[{i}] {text}")
        # Check if paragraph has drawing/image
        xml = p._p.xml
        if 'w:drawing' in xml or 'pic:pic' in xml or 'v:imagedata' in xml or 'v:shape' in xml:
            print(f"[{i}] HAS IMAGE. Text length: {len(text)}. Text snippet: {text[:30]}")

if __name__ == '__main__':
    print("=== Clean ===")
    check_images('001_Skripsi_Andricha Dea Mitra_Clean.docx')
