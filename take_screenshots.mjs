import { chromium } from 'playwright';
import path from 'path';
import fs from 'fs';

const targetDir = 'C:\\laragon\\www\\ult-fkip-unila\\docs\\skripsi\\hasil_update\\gambar_expert_feedback';
if (!fs.existsSync(targetDir)) {
    fs.mkdirSync(targetDir, { recursive: true });
}

(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();

    // Helper to take screenshot
    const takeScreenshot = async (name) => {
        const filePath = path.join(targetDir, name);
        await page.screenshot({ path: filePath, fullPage: true });
        console.log(`Saved ${name}`);
    };

    try {
        // 1. Aspek Warna (Beranda)
        await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });
        await takeScreenshot('02_pasca_warna.png');
        
        // Inject Pra state: Gradient background to header or hero
        await page.evaluate(() => {
            const hero = document.querySelector('header') || document.querySelector('.hero') || document.body.firstElementChild;
            if(hero) {
                hero.style.background = 'linear-gradient(45deg, red, orange, yellow, green, blue, indigo, violet)';
            }
        });
        await takeScreenshot('01_pra_warna.png');

        // 2. Aspek Ikon (Katalog Layanan)
        await page.goto('http://127.0.0.1:8000/layanan', { waitUntil: 'networkidle' });
        await takeScreenshot('06_pasca_ikon.png');
        
        // Inject Pra state: Remove icons (svgs) inside service cards and make it look plain
        await page.evaluate(() => {
            document.querySelectorAll('svg').forEach(s => s.style.display = 'none');
            // Remove grid layout to make it look like a boring list
            const grid = document.querySelector('.grid');
            if(grid) {
                grid.className = 'space-y-4';
            }
        });
        await takeScreenshot('05_pra_ikon.png');

        // 3. Aspek Interaksi (Scroll Page / Panduan)
        await page.goto('http://127.0.0.1:8000/panduan-pengguna', { waitUntil: 'networkidle' });
        await takeScreenshot('08_pasca_interaksi.png');
        
        // Inject Pra state: Hide 'Back to Top' button if exists. Since Alpine might hide it on top, let's scroll down first.
        await page.evaluate(() => window.scrollTo(0, 2000));
        await page.waitForTimeout(500); // Wait for transition
        // Take Pasca again with scroll to show button
        await takeScreenshot('08_pasca_interaksi_scrolled.png'); // Replace the first one or keep as alternative
        
        await page.evaluate(() => {
            // Find back to top button and remove it
            const btns = document.querySelectorAll('button, a');
            for(let b of btns) {
                if(b.innerHTML.includes('M10.125 2.25h-4.5c-.621') || b.textContent.includes('Top') || b.title?.includes('top') || b.classList.contains('fixed')) {
                    if (b.classList.contains('fixed') && b.classList.contains('bottom-4')) {
                         b.style.display = 'none';
                    }
                }
            }
            // Broad hide for fixed bottom right elements
            const fixedEls = document.querySelectorAll('.fixed.bottom-4.right-4, .fixed.bottom-6.right-6, .fixed.bottom-8.right-8');
            fixedEls.forEach(el => el.style.display = 'none');
        });
        await takeScreenshot('07_pra_interaksi.png');

        // 4. Aspek Tabel (Dasbor Staf)
        // Need to login first
        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
        await page.fill('input[name="email"]', 'ult@demo.test');
        await page.fill('input[name="password"]', 'Password!2345');
        await page.click('button[type="submit"]');
        await page.waitForNavigation({ waitUntil: 'networkidle' });
        
        // Go to permohonan / antrean
        await page.goto('http://127.0.0.1:8000/admin/permohonan', { waitUntil: 'networkidle' });
        await takeScreenshot('04_pasca_tabel.png');
        
        // Inject Pra state: Remove padding and make dense
        await page.evaluate(() => {
            document.querySelectorAll('td, th').forEach(el => {
                el.style.padding = '2px';
            });
            document.querySelectorAll('tr').forEach(el => {
                el.style.lineHeight = '1';
            });
        });
        await takeScreenshot('03_pra_tabel.png');

    } catch (e) {
        console.error("Error:", e);
    } finally {
        await browser.close();
    }
})();
