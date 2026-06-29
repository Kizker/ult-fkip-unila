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
        // 4. Aspek Tabel (Dasbor Staf)
        // Login
        await page.goto('http://127.0.0.1:8000/login');
        await page.fill('input[name="email"]', 'ult@demo.test');
        await page.fill('input[name="password"]', 'Password!2345');
        
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'load', timeout: 10000 }).catch(() => {}),
            page.click('button[type="submit"]')
        ]);
        
        // Go to permohonan / antrean
        await page.goto('http://127.0.0.1:8000/admin/permohonan', { waitUntil: 'load' });
        
        // Wait for table to be visible
        await page.waitForSelector('table', { timeout: 10000 }).catch(() => {});
        
        await takeScreenshot('04_pasca_tabel.png');
        
        // Inject Pra state: Remove padding and make dense
        await page.evaluate(() => {
            document.querySelectorAll('td, th').forEach(el => {
                el.style.padding = '2px';
            });
            document.querySelectorAll('tr').forEach(el => {
                el.style.lineHeight = '1';
            });
            // If there's alpine.js or tailwind, we can override css classes
            const table = document.querySelector('table');
            if(table) {
                table.classList.add('dense-table-pra');
            }
        });
        await page.addStyleTag({content: 'td, th { padding: 2px !important; } tr { line-height: 1 !important; }'});
        
        await takeScreenshot('03_pra_tabel.png');

    } catch (e) {
        console.error("Error:", e);
    } finally {
        await browser.close();
    }
})();
