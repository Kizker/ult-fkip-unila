import os
from PIL import Image, ImageFilter

img_path = r'C:\Users\Andri\.gemini\antigravity-ide\brain\8a482603-602c-4a1e-b39a-b55525be5686\01-beranda-sistem.png'
out_path = r'C:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\screenshots\01-beranda-sistem.jpg'
img = Image.open(img_path).convert('RGB')

# Let's inspect the original image and guess locations
# Navbar: ~0 to 100
# Huge empty space: ~100 to 450
# Text "Selamat Datang...": ~450 to 650
# Empty space: ~650 to 800
# Cards: ~800 to 1000
# Bottom content: 1000 to end

# We will cut 250px from the top empty space (Y=150 to Y=400)
# We will cut 100px from the bottom empty space (Y=650 to Y=750)

# Cut 1: 0 to 150
part1 = img.crop((0, 0, img.width, 150))
# Cut 2: 400 to 650
part2 = img.crop((0, 400, img.width, 650))
# Cut 3: 750 to end
part3 = img.crop((0, 750, img.width, img.height))

# Create a smooth transition for part2 top and bottom
# Actually, since it's a solid gradient, a simple paste is usually fine, but let's try to blur the seams if necessary. 
# We'll just do a sharp cut first.

new_height = part1.height + part2.height + part3.height
new_img = Image.new('RGB', (img.width, new_height))
new_img.paste(part1, (0, 0))
new_img.paste(part2, (0, part1.height))
new_img.paste(part3, (0, part1.height + part2.height))

new_img.save(out_path, "JPEG", quality=90)
print(f"Aggressive crop done. Original height: {img.height}, New height: {new_height}")
