import os
from PIL import Image
import glob

screenshots_dir = r"C:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\screenshots"
html_file = r"C:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\preview_11_screenshots.html"

# Convert PNGs to compressed JPGs
png_files = glob.glob(os.path.join(screenshots_dir, "*.png"))
for png_path in png_files:
    try:
        with Image.open(png_path) as img:
            # Convert to RGB if it has alpha channel
            if img.mode in ('RGBA', 'LA') or (img.mode == 'P' and 'transparency' in img.info):
                bg = Image.new("RGB", img.size, (255, 255, 255))
                bg.paste(img, mask=img.split()[3]) # 3 is the alpha channel
                img = bg
            else:
                img = img.convert("RGB")
            
            # Optionally resize if too large
            max_width = 1366
            if img.width > max_width:
                ratio = max_width / float(img.width)
                new_height = int((float(img.height) * float(ratio)))
                img = img.resize((max_width, new_height), Image.Resampling.LANCZOS)
            
            # Save as jpg
            jpg_path = png_path[:-4] + ".jpg"
            img.save(jpg_path, "JPEG", quality=75, optimize=True)
            print(f"Compressed and saved: {os.path.basename(jpg_path)}")
        
        # Remove the old png to save space
        os.remove(png_path)
    except Exception as e:
        print(f"Error compressing {png_path}: {e}")

# Update the HTML file to replace .png with .jpg
with open(html_file, 'r', encoding='utf-8') as f:
    html_content = f.read()

html_content = html_content.replace(".png", ".jpg")

with open(html_file, 'w', encoding='utf-8') as f:
    f.write(html_content)

print("Compression complete and HTML updated.")
