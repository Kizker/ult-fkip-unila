import os
from PIL import Image

artifact_dir = r'C:\Users\Andri\.gemini\antigravity-ide\brain\8a482603-602c-4a1e-b39a-b55525be5686'
out_dir = r'C:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\screenshots'

orig_path = os.path.join(artifact_dir, '004-blog-index.png')
dest_path = os.path.join(out_dir, '004-blog-index.jpg')

max_width = 1366

if os.path.exists(orig_path):
    img = Image.open(orig_path).convert('RGB')
    
    # 1. Resize to 1366 width
    ratio = max_width / float(img.width)
    new_height = int((float(img.height) * float(ratio)))
    img = img.resize((max_width, new_height), Image.Resampling.LANCZOS)
    
    # We want it "full" as the user requested, so let's not crop it,
    # or crop it to 1600 just to keep it from being absurdly long, but 
    # let's try keeping the full resized height first (which is ~2482).
    # Wait, the user said "buat seperti sebelumnya yang full" (make it like before which was full)
    # Before, it was 2400x4362, which when put in HTML preview, scaled to fit the width.
    # So let's just save the full resized image.
    
    img.save(dest_path, "JPEG", quality=90)
    print(f"Restored Blog index to full height: {dest_path} -> {img.size}")
else:
    print(f"Missing: {orig_path}")
