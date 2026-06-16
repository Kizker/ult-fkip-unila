import pandas as pd
import json

file_path = r'c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil uji ahli validator\Rekap_Uji_Validitas_Ahli.xlsx'
xls = pd.ExcelFile(file_path)

print("Sheets:", xls.sheet_names)

for sheet in xls.sheet_names:
    print(f"\n--- {sheet} ---")
    df = pd.read_excel(file_path, sheet_name=sheet)
    print(df.head(10).to_string())
