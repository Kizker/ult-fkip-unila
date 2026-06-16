import docx

def find_lost_text(doc_path):
    doc = docx.Document(doc_path)
    for i, p in enumerate(doc.paragraphs):
        text = p.text.strip()
        if "Gambar 4" in text or "Gambar  4" in text:
            print(f"[{i}] {text}")
        if "Gambar 5" in text or "Gambar  5" in text:
            print(f"[{i}] {text}")
        if "Gambar 6" in text or "Gambar  6" in text:
            print(f"[{i}] {text}")

if __name__ == '__main__':
    find_lost_text('001_Skripsi_Andricha Dea Mitra_Clean.docx')
