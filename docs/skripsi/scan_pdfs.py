import os
import time
from datetime import datetime

folder = r'C:\Users\Andri\Documents\001. SKRIPSI !!!\01. Jurnal'
files_info = []

if os.path.exists(folder):
    for root, dirs, files in os.walk(folder):
        for f in files:
            if f.lower().endswith('.pdf'):
                p = os.path.join(root, f)
                mtime = os.path.getmtime(p)
                dt = datetime.fromtimestamp(mtime)
                months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
                fmt_date = f"{dt.day} {months[dt.month-1]} {dt.year}"
                files_info.append({'name': f, 'date': fmt_date, 'raw_mtime': mtime})
                
    with open(r'c:\laragon\www\ult-fkip-unila\docs\skripsi\all_pdf_dates.txt', 'w', encoding='utf-8') as out:
        for info in files_info:
            out.write(f"{info['name']} | {info['date']}\n")
    print(f'Found {len(files_info)} PDF files recursively.')
else:
    print('Folder not found')
