import pandas as pd
import os

# Data for Development Phase (Uji Ahli Validasi)
data_ahli = {
    'Aspek Penilaian': ['Ahli Materi', 'Ahli Media', 'Ahli Sistem', 'Rata-rata Keseluruhan'],
    'Persentase Kelayakan (%)': [95.45, 93.33, 87.58, 91.95],
    'Kategori': ['Sangat Valid', 'Sangat Valid', 'Sangat Valid', 'Sangat Valid']
}

df_ahli = pd.DataFrame(data_ahli)

# Data for Implementation Phase (Uji Kepraktisan)
data_kepraktisan = {
    'Kategori Kepraktisan': ['Sangat Praktis', 'Praktis', 'Cukup Praktis', 'Kurang Praktis', 'Sangat Tidak Praktis'],
    'Jumlah Responden (Orang)': [8, 9, 1, 0, 0],
    'Persentase (%)': [(8/18)*100, (9/18)*100, (1/18)*100, 0, 0]
}

df_kepraktisan = pd.DataFrame(data_kepraktisan)

output_path = r'c:\laragon\www\ult-fkip-unila\docs\skripsi\Data_Diagram_Skripsi.xlsx'

# Write to Excel
with pd.ExcelWriter(output_path, engine='openpyxl') as writer:
    df_ahli.to_excel(writer, sheet_name='Uji Ahli (Development)', index=False)
    df_kepraktisan.to_excel(writer, sheet_name='Uji Kepraktisan (Implementasi)', index=False)

print(f"Excel file created successfully at: {output_path}")
