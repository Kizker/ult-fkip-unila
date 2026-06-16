import os
from PIL import Image

img_path = r'C:\Users\Andri\.gemini\antigravity-ide\brain\8a482603-602c-4a1e-b39a-b55525be5686\01-beranda-sistem.png'
out_path = r'C:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\screenshots\01-beranda-sistem.jpg'
img = Image.open(img_path).convert('RGB')

# First, resize to 1366 width to normalize coordinates
max_width = 1366
ratio = max_width / float(img.width)
new_height = int((float(img.height) * float(ratio)))
img = img.resize((max_width, new_height), Image.Resampling.LANCZOS)
# img size is now 1366 x 2049

# The text seems to be around Y=1100 to 1400 in the 2049 image.
# We will keep:
# 1. Navbar: 0 to 120
# 2. Text: 1100 to 1500
# 3. Cards & Below: 1600 to end

# Crop parts
part_navbar = img.crop((0, 0, img.width, 150))
part_text = img.crop((0, 1050, img.width, 1500))
part_content = img.crop((0, 1600, img.width, img.height))

# Stitch together
final_height = part_navbar.height + part_text.height + part_content.height
new_img = Image.new('RGB', (img.width, final_height))
new_img.paste(part_navbar, (0, 0))
new_img.paste(part_text, (0, part_navbar.height))
new_img.paste(part_content, (0, part_navbar.height + part_text.height))

# Save
new_img.save(out_path, "JPEG", quality=90)
print(f"Super crop done! New height is {final_height}px.")
