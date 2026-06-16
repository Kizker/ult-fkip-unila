import os
import re
import datetime
import pypdf
import json

def parse_reference(ref_text):
    # Remove URLs for clean parsing
    url_match = re.search(r'(https?://[^\s]+)', ref_text)
    www_match = re.search(r'(www\.[^\s]+)', ref_text)
    if url_match:
        ref_text = ref_text.replace(url_match.group(1), '').strip()
    elif www_match:
        ref_text = ref_text.replace(www_match.group(1), '').strip()
        
    match = re.match(r'^([^\(]+?)\s+\((\d{4})\)\.\s+(.+?)\.(?:\s+(.+))?$', ref_text)
    if not match: return None
    author = match.group(1).strip()
    year = match.group(2).strip()
    title = match.group(3).strip()
    return author, year, title

def clean_text(text):
    text = text.lower()
    text = re.sub(r'[^a-z0-9\s]', '', text)
    return set(text.split())

# 1. Load references
refs = []
with open(r'c:\laragon\www\ult-fkip-unila\docs\skripsi\daftar_pustaka_raw.txt', 'r', encoding='utf-8') as f:
    for line in f:
        line = line.strip()
        if not line or line == 'DAFTAR PUSTAKA' or line.startswith('The above content') or line.startswith('http'):
            continue
        parsed = parse_reference(line)
        if parsed:
            author, year, title = parsed
            refs.append({
                'original': line,
                'author': author,
                'title': title,
                'title_words': clean_text(title),
                'author_words': clean_text(author)
            })

# 2. Scan PDFs and extract text
folder = r'C:\Users\Andri\Documents\001. SKRIPSI !!!\01. Jurnal'
pdf_data = []

if os.path.exists(folder):
    for root, dirs, files in os.walk(folder):
        for f in files:
            if f.lower().endswith('.pdf'):
                p = os.path.join(root, f)
                mtime = os.path.getmtime(p)
                dt = datetime.datetime.fromtimestamp(mtime)
                months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
                fmt_date = f"{dt.day} {months[dt.month-1]} {dt.year}"
                
                # Extract text from first page
                text = ""
                try:
                    with open(p, 'rb') as pdf_file:
                        reader = pypdf.PdfReader(pdf_file)
                        if len(reader.pages) > 0:
                            text = reader.pages[0].extract_text()
                except Exception:
                    pass
                
                text_words = clean_text(text)
                name_words = clean_text(f)
                
                pdf_data.append({
                    'path': p,
                    'name': f,
                    'date': fmt_date,
                    'text_words': text_words,
                    'name_words': name_words
                })

# 3. Match
results = []
for ref in refs:
    best_match = None
    highest_score = 0
    
    for pdf in pdf_data:
        # Score based on filename
        title_name_intersect = len(ref['title_words'].intersection(pdf['name_words']))
        author_name_intersect = len(ref['author_words'].intersection(pdf['name_words']))
        
        # Score based on PDF content (first page)
        title_text_intersect = len(ref['title_words'].intersection(pdf['text_words']))
        author_text_intersect = len(ref['author_words'].intersection(pdf['text_words']))
        
        score = (title_name_intersect * 2) + (author_name_intersect * 3) + title_text_intersect + (author_text_intersect * 2)
        
        if score > highest_score:
            highest_score = score
            best_match = pdf
            
    if best_match and highest_score > 3: # threshold
        results.append({
            'author': ref['author'],
            'title': ref['title'],
            'pdf_name': best_match['name'],
            'pdf_path': best_match['path'],
            'date': best_match['date'],
            'score': highest_score
        })
    else:
        results.append({
            'author': ref['author'],
            'title': ref['title'],
            'pdf_name': 'TIDAK DITEMUKAN',
            'pdf_path': '-',
            'date': '-',
            'score': 0
        })

# Write markdown report
with open(r'c:\laragon\www\ult-fkip-unila\docs\skripsi\matching_report.md', 'w', encoding='utf-8') as f:
    f.write("# Hasil Pemetaan Daftar Pustaka dengan File PDF\n\n")
    f.write("Berikut adalah hasil pencocokan setiap referensi jurnal dengan file fisik PDF di folder Anda beserta tanggal modifikasinya.\n\n")
    
    for r in results:
        f.write(f"**Referensi:** {r['author']} - *{r['title']}*\n")
        if r['pdf_name'] != 'TIDAK DITEMUKAN':
            f.write(f"- **File Ditemukan:** `{r['pdf_name']}`\n")
            f.write(f"- **Tanggal Akses (Modified Date):** **{r['date']}**\n")
            f.write(f"- **Lokasi File:** `{r['pdf_path']}`\n")
            f.write(f"- *(Skor Kecocokan: {r['score']})*\n\n")
        else:
            f.write(f"- **File Ditemukan:** ❌ TIDAK DITEMUKAN DALAM FOLDER JURNAL\n")
            f.write(f"- **Tanggal Akses:** -\n\n")
