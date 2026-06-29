import docx
import re

def inspect_images(filename):
    doc = docx.Document(filename)
    highest_img_num = 0
    for p in doc.paragraphs:
        text = p.text.strip()
        match = re.search(r'Gambar\s+(\d+)\.', text)
        if match and p.style.name == 'Caption':
            num = int(match.group(1))
            if num > highest_img_num:
                highest_img_num = num
    print(f"Highest image number found: {highest_img_num}")

inspect_images("001_Skripsi_Andricha Dea Mitra_Clean.docx")
