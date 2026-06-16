from PIL import Image

img_path = r'C:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\screenshots\01-beranda-sistem.jpg'
img = Image.open(img_path)

# Crop out Y=250 to Y=750
top = img.crop((0, 0, img.width, 250))
bottom = img.crop((0, 750, img.width, img.height))

new_img = Image.new('RGB', (img.width, top.height + bottom.height))
new_img.paste(top, (0, 0))
new_img.paste(bottom, (0, top.height))

new_img.save(img_path, "JPEG", quality=85)
print(f"Cropped 500px from the middle of {img_path}")
