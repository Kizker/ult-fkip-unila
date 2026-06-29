import os
from PIL import Image

OUT_DIR = r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\screenshots_baru"

crops = {
    "Gambar 43_Full.png": ("Gambar 43.png", (200, 200, 1100, 600)), 
    "Gambar 44_Full.png": ("Gambar 44.png", (200, 200, 1100, 600)),
    "Gambar 45_Full.png": ("Gambar 45.png", (100, 150, 1100, 700)), 
    "Gambar 46_Full.png": ("Gambar 46.png", (100, 150, 1100, 700)),
    "Gambar 47_Full.png": ("Gambar 47.png", (0, 0, 350, 768)), 
    "Gambar 48_Full.png": ("Gambar 48.png", (0, 0, 350, 768)),
    "Gambar 49_Full.png": ("Gambar 49.png", (280, 80, 1100, 500)), 
    "Gambar 50_Full.png": ("Gambar 50.png", (280, 80, 1100, 500)),
    "Gambar 51_Full.png": ("Gambar 51.png", (280, 150, 1300, 650)), 
    "Gambar 52_Full.png": ("Gambar 52.png", (280, 150, 1300, 650)),
}

for src_name, (dest_name, box) in crops.items():
    src_path = os.path.join(OUT_DIR, src_name)
    dest_path = os.path.join(OUT_DIR, dest_name)
    
    if os.path.exists(src_path):
        try:
            img = Image.open(src_path)
            cropped = img.crop(box)
            cropped.save(dest_path)
            print(f"Berhasil crop: {src_name} -> {dest_name}")
            os.remove(src_path)
        except Exception as e:
            print(f"Gagal crop {src_name}: {e}")
    else:
        print(f"File {src_name} tidak ditemukan.")

print("Selesai cropping.")
