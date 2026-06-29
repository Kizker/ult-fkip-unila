from PIL import Image
import os

folder = r"c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\screenshots_baru"

def crop_image(filename, out_filename, box):
    path = os.path.join(folder, filename)
    if os.path.exists(path):
        img = Image.open(path)
        cropped = img.crop(box)
        cropped.save(os.path.join(folder, out_filename))
        print(f"Cropped {filename} to {out_filename}")
        os.remove(path)

# Gambar 38: RBAC Table (usually center/left content, avoiding top nav and left sidebar)
crop_image("Gambar 38 (Full).png", "Gambar 38.png", (250, 100, 1200, 700))

# Gambar 39: Perakitan Dokumen (WYSIWYG editor)
crop_image("Gambar 39 (Full).png", "Gambar 39.png", (250, 150, 1300, 700))

# Gambar 41: Log Admin
crop_image("Gambar 41 (Full).png", "Gambar 41.png", (300, 150, 1300, 700))

# Remove the old debug files
for f in ["debug_after_student_login.png", "Gambar 37 (Full).png"]:
    p = os.path.join(folder, f)
    if os.path.exists(p):
        os.remove(p)

print("All cropping done.")
