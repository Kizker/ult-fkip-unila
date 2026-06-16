import os
from PIL import Image

out_dir = r'C:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\screenshots'

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

print("Restoring all screenshots to FULL height (width = 1366)...")

for orig, dest in artifacts.items():
    orig_path = os.path.join(artifact_dir, orig)
    dest_path = os.path.join(out_dir, dest)
    
    if os.path.exists(orig_path):
        img = Image.open(orig_path).convert('RGB')
        ratio = max_width / float(img.width)
        new_height = int((float(img.height) * float(ratio)))
        img = img.resize((max_width, new_height), Image.Resampling.LANCZOS)
        
        img.save(dest_path, "JPEG", quality=90)
        print(f"Restored: {dest} -> {img.size}")
