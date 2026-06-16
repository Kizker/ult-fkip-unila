import re
fixes = {
    'Ahmad': {'journal': 'Network and Distributed System Security (NDSS)', 'pages': '1-8'},
    'Aiken': {'journal': 'Educational and Psychological Measurement', 'vol': '45(1)', 'pages': '131-142'},
    'Arikunto|2009': {'publisher': 'Bumi Aksara, Jakarta', 'pages': '310 hlm'},
    'Arikunto|2013': {'publisher': 'Rineka Cipta, Jakarta', 'pages': '413 hlm'},
    'Branch': {'publisher': 'Springer, Boston', 'pages': '175 hlm'}
}
ref_text = 'Arikunto, S. (2009). Dasar-dasar Evaluasi Pendidikan.'
match = re.match(r'^([^\(]+?)\s+\((\d{4})\)\.\s+(.+?)\.(?:\s+(.+))?$', ref_text)
author_part = match.group(1).strip()
year = match.group(2).strip()
title = match.group(3).strip()
rest = match.group(4).strip() if match.group(4) else ''
if author_part.endswith('.'): author_part = author_part[:-1].strip()

is_book = True
p = {'authors': author_part, 'year': year, 'title': title, 'rest': rest, 'is_book': is_book}

auth, year, title, rest, is_book = p['authors'], p['year'], p['title'], p['rest'], p['is_book']
for k, v in fixes.items():
    search_k = k.split('|')[0]
    req_year = k.split('|')[1] if '|' in k else None
    
    if search_k in auth:
        if req_year and req_year != year:
            continue
        
        if 'publisher' in v:
            rest = f"{v.get('publisher', '')}. {v.get('pages', '')}"
            is_book = True
        elif 'journal' in v:
            rest = f"{v['journal']} {v.get('vol', '')}, {v.get('pages', rest)}"
            is_book = False
        else:
            if 'pages' in v: rest += f", {v['pages']}"

p['rest'] = rest
print('REST IS:', p['rest'])
