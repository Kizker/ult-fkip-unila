import docx

doc = docx.Document(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx")

for i in range(955, 960):
    p = doc.paragraphs[i]
    print(f"Line {i}: {repr(p.text)}")
    print(f"  Runs: {len(p.runs)}")
    for j, run in enumerate(p.runs):
        has_drawing = any('drawing' in r.tag for r in run._element.iter())
        has_pic = any('pic' in r.tag for r in run._element.iter())
        print(f"    Run {j}: len={len(run.text)}, pic={has_pic}, drawing={has_drawing}")
