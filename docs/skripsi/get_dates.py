import os
import time
from datetime import datetime

folder = r'C:\Users\Andri\Documents\001. SKRIPSI !!!\01. Jurnal'
if os.path.exists(folder):
    files = []
    for f in os.listdir(folder):
        p = os.path.join(folder, f)
        if os.path.isfile(p):
            mtime = os.path.getmtime(p)
            dt = datetime.fromtimestamp(mtime)
            months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
            fmt_date = f"{dt.day} {months[dt.month-1]} {dt.year}"
            files.append({'name': f, 'date': fmt_date})
    
    with open(r'c:\laragon\www\ult-fkip-unila\docs\skripsi\file_dates.txt', 'w', encoding='utf-8') as out:
        for f in files:
            out.write(f"{f['name']} | {f['date']}\n")
    print('Dates written to file_dates.txt')
else:
    print('Folder not found')
