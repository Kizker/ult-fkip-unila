import docx

doc = docx.Document(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx")

for i, table in enumerate(doc.tables):
    if table.rows:
        header = [cell.text.strip() for cell in table.rows[0].cells]
        print(f"Table {i}: {header}")
