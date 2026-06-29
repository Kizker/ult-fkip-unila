import { chromium } from 'playwright';
import path from 'path';
import fs from 'fs';

const targetDir = 'C:\\laragon\\www\\ult-fkip-unila\\docs\\skripsi\\hasil_update\\gambar_expert_feedback';

(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext({ viewport: { width: 1280, height: 1000 } });
    const page = await context.newPage();

    try {
        await page.goto('http://127.0.0.1:8000/login');
        await page.fill('input[name="email"]', 'ult@demo.test');
        await page.fill('input[name="password"]', 'Password!2345');
        
        await Promise.all([
            page.waitForNavigation(),
            page.click('button[type="submit"]')
        ]);
        
        await page.goto('http://127.0.0.1:8000/admin/dashboard');
        await page.waitForTimeout(2000); // Wait for animations
        
        // Scroll to table
        await page.evaluate(() => {
            document.querySelector('#transactionTable').scrollIntoView();
        });
        await page.waitForTimeout(1000);
        
        const clipRegion = { x: 50, y: 150, width: 1180, height: 600 };
        
        // Wait, just take screenshot of the element
        const tableCard = page.locator('.dash-table-card');
        await tableCard.screenshot({ path: path.join(targetDir, '04_pasca_tabel.png') });
        console.log('Saved 04_pasca_tabel.png');
        
        // Pra
        await page.evaluate(() => {
            document.querySelectorAll('td, th').forEach(el => {
                el.style.padding = '2px 4px';
            });
            document.querySelectorAll('tr').forEach(el => {
                el.style.lineHeight = '1';
            });
        });
        
        await tableCard.screenshot({ path: path.join(targetDir, '03_pra_tabel.png') });
        console.log('Saved 03_pra_tabel.png');

    } catch (e) {
        console.error("Error:", e);
    } finally {
        await browser.close();
    }
})();
