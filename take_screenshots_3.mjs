import { chromium } from 'playwright';
import path from 'path';
import fs from 'fs';

const targetDir = 'C:\\laragon\\www\\ult-fkip-unila\\docs\\skripsi\\hasil_update\\gambar_expert_feedback';
if (!fs.existsSync(targetDir)) {
    fs.mkdirSync(targetDir, { recursive: true });
}

(async () => {
    const browser = await chromium.launch();
    // Use standard laptop viewport
    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();

    const takeScreenshot = async (name, options = {}) => {
        const filePath = path.join(targetDir, name);
        await page.screenshot({ path: filePath, ...options });
        console.log(`Saved ${name}`);
    };

    try {
        // 1. Aspek Warna (Beranda)
        await page.goto('http://127.0.0.1:8000/', { waitUntil: 'load' });
        
        // Pasca: Hero Section
        let hero = page.locator('.ult-hero');
        await hero.screenshot({ path: path.join(targetDir, '02_pasca_warna.png') });
        console.log('Saved 02_pasca_warna.png');

        // Pra: Inject striking gradient
        await page.evaluate(() => {
            const bg = document.querySelector('.ult-hero-bg');
            if(bg) {
                bg.style.backgroundImage = 'none';
                bg.style.background = 'linear-gradient(90deg, #ff9999, #66b3ff)';
            }
        });
        await hero.screenshot({ path: path.join(targetDir, '01_pra_warna.png') });
        console.log('Saved 01_pra_warna.png');


        // 2. Aspek Ikon (Katalog Layanan)
        await page.goto('http://127.0.0.1:8000/layanan', { waitUntil: 'load' });
        
        // Let's capture the grid
        let catalog = page.locator('.services-catalog-grid');
        await catalog.screenshot({ path: path.join(targetDir, '06_pasca_ikon.png') });
        console.log('Saved 06_pasca_ikon.png');

        // Pra: Hide icons
        await page.evaluate(() => {
            document.querySelectorAll('.services-catalog-grid iconify-icon, .services-catalog-grid svg, .services-catalog-grid img').forEach(el => {
                el.style.display = 'none';
            });
        });
        await catalog.screenshot({ path: path.join(targetDir, '05_pra_ikon.png') });
        console.log('Saved 05_pra_ikon.png');


        // 3. Aspek Interaksi (Back to Top)
        await page.goto('http://127.0.0.1:8000/panduan', { waitUntil: 'load' });
        // Scroll down to trigger back to top
        await page.evaluate(() => window.scrollBy(0, 1000));
        await page.waitForTimeout(1000); // Wait for transition
        
        // Capture bottom right corner where the button is
        const clipRegion = { x: 980, y: 500, width: 300, height: 300 };
        await takeScreenshot('08_pasca_interaksi.png', { clip: clipRegion });
        
        // Pra: Hide the button
        await page.evaluate(() => {
            const btn = document.querySelector('.back-to-top, [data-back-to-top], button[aria-label="Back to top"], button[title="Back to top"]');
            if(btn) btn.style.display = 'none';
        });
        await takeScreenshot('07_pra_interaksi.png', { clip: clipRegion });


        // 4. Aspek Tabel (Dasbor Staf)
        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
        await page.fill('input[name="email"]', 'ult@demo.test');
        await page.fill('input[name="password"]', 'Password!2345');
        
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'load', timeout: 10000 }).catch(() => {}),
            page.click('button[type="submit"]')
        ]);
        
        await page.goto('http://127.0.0.1:8000/admin/permohonan', { waitUntil: 'load' });
        await page.waitForSelector('table', { timeout: 10000 }).catch(() => {});
        
        // Capture the card containing the table
        let tableContainer = page.locator('.bg-white.shadow').first();
        // If not found, fallback to table
        if (await tableContainer.count() === 0) {
            tableContainer = page.locator('table').first();
        }
        
        await tableContainer.screenshot({ path: path.join(targetDir, '04_pasca_tabel.png') });
        console.log('Saved 04_pasca_tabel.png');
        
        // Pra: Modify padding
        await page.evaluate(() => {
            document.querySelectorAll('td, th').forEach(el => {
                el.style.padding = '2px';
            });
            document.querySelectorAll('tr').forEach(el => {
                el.style.lineHeight = '1';
            });
        });
        await page.addStyleTag({content: 'td, th { padding: 2px !important; } tr { line-height: 1 !important; }'});
        
        await tableContainer.screenshot({ path: path.join(targetDir, '03_pra_tabel.png') });
        console.log('Saved 03_pra_tabel.png');

    } catch (e) {
        console.error("Error:", e);
    } finally {
        await browser.close();
    }
})();
