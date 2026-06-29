import docx

def find_table_details(filename):
    doc = docx.Document(filename)
    for i, t in enumerate(doc.tables):
        for r_idx, r in enumerate(t.rows):
            for c_idx, c in enumerate(r.cells):
                if "Kedalaman Materi" in c.text:
                    print(f"Table {i}, Row {r_idx}, Col {c_idx}: {repr(c.text)}")

find_table_details("001_Skripsi_Andricha Dea Mitra_Clean.docx")
