import matplotlib.pyplot as plt
import os
import numpy as np

labels = ['Sangat Praktis\n(8 orang)', 'Praktis\n(9 orang)', 'Cukup Praktis\n(1 orang)']
sizes = [8, 9, 1]
colors = ['#1f77b4', '#2ca02c', '#ff7f0e'] # Blue, Green, Orange
explode = (0.05, 0.05, 0.1)  # explode 3rd slice slightly

fig, ax = plt.subplots(figsize=(8, 6), subplot_kw=dict(aspect="equal"))
wedges, texts, autotexts = ax.pie(sizes, explode=explode, labels=labels, colors=colors,
        autopct='%1.2f%%', shadow=False, startangle=140, textprops=dict(color="black", fontsize=12))

plt.setp(autotexts, size=12, weight="bold", color="white")
ax.set_title("Distribusi Kategori Uji Kepraktisan Pengguna", fontsize=14, weight='bold', pad=20)

plt.tight_layout()
output_path = r'c:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\22_diagram_kepraktisan.png'
plt.savefig(output_path, dpi=300, bbox_inches='tight')
print(f"Chart saved to {output_path}")
