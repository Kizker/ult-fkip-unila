import docx
import json

def dump_texts(doc_path):
    doc = docx.Document(doc_path)
    lines = []
    for p in doc.paragraphs:
        lines.append(p.text.strip())
        
    start_idx = -1
    for i, t in enumerate(lines):
        if "Diagram flowchart pada Gambar 6" in t:
            start_idx = i
            break
            
    dump_data = []
    if start_idx != -1:
        for i in range(start_idx, start_idx+35):
            if lines[i]:
                dump_data.append(lines[i])
                
    with open('dump_texts.json', 'w', encoding='utf-8') as f:
        json.dump(dump_data, f, ensure_ascii=False, indent=2)
    print("Dumped texts to dump_texts.json")

if __name__ == '__main__':
    dump_texts(r'001_Skripsi_Andricha Dea Mitra_Clean.docx')
