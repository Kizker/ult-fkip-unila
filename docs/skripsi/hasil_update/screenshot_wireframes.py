import asyncio
from playwright.async_api import async_playwright
import os

tabs = [
    ('Beranda Utama', '11_wireframe_beranda_balsamiq.png'),
    ('Katalog Layanan', '12_wireframe_katalog_balsamiq.png'),
    ('Berita & Info', '13_wireframe_berita_balsamiq.png'),
    ('Login & Register', '14_wireframe_login_balsamiq.png'),
    ('Dasbor Mahasiswa', '15_wireframe_student_balsamiq.png'),
    ('Form Pengajuan', '16_wireframe_form_balsamiq.png'),
    ('Riwayat & Timeline', '17_wireframe_timeline_balsamiq.png'),
    ('Dasbor Staff', '18_wireframe_admin_balsamiq.png'),
    ('Detail Review', '19_wireframe_review_balsamiq.png'),
    ('Manajemen Template', '20_wireframe_template_balsamiq.png'),
    ('Verifikasi Pejabat', '21_wireframe_signer_balsamiq.png')
]

async def main():
    async with async_playwright() as p:
        browser = await p.chromium.launch(channel="msedge")
        page = await browser.new_page(viewport={"width": 1400, "height": 900})
        html_path = r"c:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram\preview_11_wireframes_balsamiq.html"
        await page.goto(f"file:///{html_path.replace(chr(92), '/')}")
        
        # Wait for Alpine to init
        await page.wait_for_timeout(1000)

        for btn_text, img_name in tabs:
            print(f"Capturing {img_name}...")
            await page.locator(f"button:has-text('{btn_text}')").click()
            await page.wait_for_timeout(500)
            
            # screenshot browser window
            locator = page.locator(".browser-window")
            out_path = os.path.join(r"c:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram", img_name)
            await locator.screenshot(path=out_path)

        await browser.close()
        print("Done capturing 11 screenshots!")

asyncio.run(main())
