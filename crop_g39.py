from PIL import Image
import os

src_file = r"c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\screenshots_baru\Gambar 39 (Full).png"
dst_file = r"c:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\screenshots_baru\Gambar 39.png"

if os.path.exists(src_file):
    img = Image.open(src_file)
    # The user's image shows the content area, which is usually right of the 250px sidebar and below the 64px header.
    # We want it to look exactly like the screenshot they provided.
    cropped = img.crop((260, 60, 1360, 750))
    cropped.save(dst_file)
    print("Cropped Gambar 39 (Full).png to Gambar 39.png")
    os.remove(src_file)
else:
    print("Source file not found")
