import docx
from docx.oxml.table import CT_Tbl
from docx.oxml.text.paragraph import CT_P
from docx.table import Table
from docx.text.paragraph import Paragraph

doc = docx.Document(r'docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx')
with open('doc_structure.txt', 'w', encoding='utf-8') as f:
    for i, child in enumerate(doc._body._body):
        if isinstance(child, CT_P):
            p = Paragraph(child, doc)
            text = p.text.strip()
            if text:
                f.write(f'P: {text[:60]}...\n')
            else:
                f.write(f'P: [EMPTY]\n')
        elif isinstance(child, CT_Tbl):
            t = Table(child, doc)
            row_text = ' | '.join(cell.text.strip().replace('\n', ' ')[:20] for cell in t.rows[0].cells)
            f.write(f'*** TABLE: {row_text} ***\n')
