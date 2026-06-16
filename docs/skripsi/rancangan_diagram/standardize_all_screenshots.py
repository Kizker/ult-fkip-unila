import os
from PIL import Image
import glob

# Destination folder
out_dir = r'C:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\screenshots'

# Map the 11 original artifacts to their desired output names
# EXCEPT for 01-beranda-sistem which we already generated correctly in the screenshots folder!
artifacts = {
    '004-blog-index.png': '004-blog-index.jpg',
    '02-daftar-layanan.png': '02-daftar-layanan.jpg',
    '05-login.png': '05-login.jpg',
    '06-dashboard-pemohon.png': '06-dashboard-pemohon.jpg',
    '07-detail-penandatanganan.png': '07-detail-penandatanganan.jpg',
    '08-form-pengajuan.png': '08-form-pengajuan.jpg',
    '09-riwayat-permohonan.png': '09-riwayat-permohonan.jpg',
    'admin_dashboard__desktop-1366.png': 'admin_dashboard__desktop-1366.jpg',
    'admin_doc_formats_show__desktop-1366.png': 'admin_doc_formats_show__desktop-1366.jpg',
    'admin_requests_show__desktop-1366.png': 'admin_requests_show__desktop-1366.jpg'
}

artifact_dir = r'C:\Users\Andri\.gemini\antigravity-ide\brain\8a482603-602c-4a1e-b39a-b55525be5686'

max_width = 1366
viewport_height = 900  # Standard desktop viewport height

print("Standardizing screenshots to desktop viewport size...")

for orig, dest in artifacts.items():
    orig_path = os.path.join(artifact_dir, orig)
    dest_path = os.path.join(out_dir, dest)
    
    if os.path.exists(orig_path):
        img = Image.open(orig_path).convert('RGB')
        
        # 1. Resize to 1366 width
        ratio = max_width / float(img.width)
        new_height = int((float(img.height) * float(ratio)))
        img = img.resize((max_width, new_height), Image.Resampling.LANCZOS)
        
        # 2. Crop to viewport height (900px) from top
        if img.height > viewport_height:
            img = img.crop((0, 0, max_width, viewport_height))
            
        img.save(dest_path, "JPEG", quality=90)
        print(f"Processed: {dest} -> {img.size}")
    else:
        print(f"Missing: {orig_path}")

# Now, process 01-beranda-sistem.jpg which we generated with Playwright
beranda_path = os.path.join(out_dir, '01-beranda-sistem.jpg')
if os.path.exists(beranda_path):
    img = Image.open(beranda_path).convert('RGB')
    if img.height > viewport_height:
        # For Beranda, we want to capture the hero AND the Katalog Layanan cards.
        # Let's crop it to 1000px height which is slightly taller to ensure cards fit nicely.
        img = img.crop((0, 0, max_width, 1000))
        img.save(beranda_path, "JPEG", quality=90)
        print(f"Processed Beranda: 01-beranda-sistem.jpg -> {img.size}")

print("All screenshots standardized!")
