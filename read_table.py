import docx

doc = docx.Document(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx")

for i, table in enumerate(doc.tables):
    # Find the table that has "Komentar / Saran"
    if table.rows and len(table.columns) >= 4:
        header = [cell.text.strip() for cell in table.rows[0].cells]
        if "Nama Validator" in header or "Komentar / Saran" in header:
            print(f"--- Table {i} ---")
            for row in table.rows:
                cells = [c.text.strip() for c in row.cells]
                print(cells)
