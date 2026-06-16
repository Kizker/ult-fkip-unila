import pandas as pd

# Data untuk Diagram 1 (Clustered Column Chart) - Uji Ahli
# Format khusus agar saat di-copy paste ke Word Chart langsung jadi 1 Series.
data_ahli = {
    '': ['Ahli Materi', 'Ahli Media', 'Ahli Sistem', 'Rata-rata'],
    'Persentase Kelayakan (%)': [95.45, 93.33, 87.58, 91.95]
}
df_ahli = pd.DataFrame(data_ahli)

# Data untuk Diagram 2 (Pie Chart) - Uji Kepraktisan
# Format khusus 2 kolom agar Pie Chart di Word tidak bingung, dan slice dengan nilai 0 dihapus.
data_kepraktisan = {
    '': ['Sangat Praktis', 'Praktis', 'Cukup Praktis'],
    'Jumlah Responden': [8, 9, 1]
}
df_kepraktisan = pd.DataFrame(data_kepraktisan)

output_path = r'c:\laragon\www\ult-fkip-unila\docs\skripsi\Data_Diagram_Skripsi_v2.xlsx'

# Tulis ke Excel
with pd.ExcelWriter(output_path, engine='openpyxl') as writer:
    df_ahli.to_excel(writer, sheet_name='Uji Ahli (Untuk Column Chart)', index=False)
    df_kepraktisan.to_excel(writer, sheet_name='Uji Kepraktisan (Untuk Pie Chart)', index=False)

print(f"Excel file updated successfully at: {output_path}")
