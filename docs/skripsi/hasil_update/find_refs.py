import docx
import re

def find_image_references(filename):
    doc = docx.Document(filename)
    references = []
    
    # We will search for occurrences of "Gambar" in the text, except for Captions
    for p in doc.paragraphs:
        if p.style.name != 'Caption' and 'Gambar' in p.text:
            text = p.text.strip()
            # Find the sentence containing "Gambar \d+"
            sentences = re.split(r'(?<=\.)\s+', text)
            for s in sentences:
                if re.search(r'Gambar\s+\d+', s, re.IGNORECASE):
                    references.append(s)
                    if len(references) >= 10:
                        break
        if len(references) >= 10:
            break
            
    for i, ref in enumerate(references):
        print(f"[{i+1}] {ref}")

find_image_references("001_Skripsi_Andricha Dea Mitra_Clean.docx")
