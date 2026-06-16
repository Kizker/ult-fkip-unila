import zipfile
import xml.etree.ElementTree as ET

ns = {'w': 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'}

z = zipfile.ZipFile(r'c:\laragon\www\ult-fkip-unila\docs\buku-panduan\perbaikan_v2\Buku Panduan Pengelola Layanan - Versi Terbaru.docx')
doc = ET.fromstring(z.read('word/document.xml'))

for p in doc.findall('.//w:p', ns):
    text = ''.join(t.text for t in p.findall('.//w:t', ns) if t.text)
    pPr = p.find('w:pPr', ns)
    if pPr is not None:
        numPr = pPr.find('w:numPr', ns)
        if numPr is not None:
            numId = numPr.find('w:numId', ns)
            if numId is not None:
                val = numId.get('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}val')
                print(f'numId: {val} | Text: {text}')
