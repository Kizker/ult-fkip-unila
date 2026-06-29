from PIL import Image
import os
import shutil

src_file = r"c:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\screenshots\admin_doc_formats_show__desktop-1366.jpg"
dst_file = r"c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\screenshots_baru\Gambar 39.png"

# Copy and crop
img = Image.open(src_file)
# Crop the main content area assuming a left sidebar of ~250px and a top nav of ~100px.
# We'll take a large chunk to make sure the editor is visible.
cropped = img.crop((250, 100, 1300, 900))
cropped.save(dst_file)
print("Replaced Gambar 39 with cropped original screenshot.")
