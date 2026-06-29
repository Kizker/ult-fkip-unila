import docx
import re
from collections import Counter

doc_path = r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx"
try:
    doc = docx.Document(doc_path)
    
    text = ""
    for p in doc.paragraphs:
        text += p.text + " "
            
    words = re.findall(r'\b[a-zA-Z-]+\b', text.lower())
    counter = Counter(words)

    with open("C:/laragon/www/ult-fkip-unila/word_freq.txt", "w", encoding="utf-8") as f:
        for word, count in counter.most_common():
            f.write(f"{word}: {count}\n")
    print("Word frequencies extracted successfully.")
except Exception as e:
    print(f"Error: {e}")
