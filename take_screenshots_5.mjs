import { chromium } from 'playwright';
import path from 'path';
import fs from 'fs';

const targetDir = 'C:\\laragon\\www\\ult-fkip-unila\\docs\\skripsi\\hasil_update\\gambar_expert_feedback';

(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext({ viewport: { width: 1280, height: 1000 } });
    const page = await context.newPage();

    try {
        // --- 3. Aspek Ikon (Katalog Layanan) ---
        await page.goto('http://127.0.0.1:8000/layanan', { waitUntil: 'load' });
        await page.waitForSelector('.services-catalog-grid', { timeout: 10000 });
        
        // Limit grid height for screenshot
        await page.evaluate(() => {
            const grid = document.querySelector('.services-catalog-grid');
            if (grid) {
                grid.style.maxHeight = '400px';
                grid.style.overflow = 'hidden';
            }
        });
        
        const gridCard = page.locator('.services-catalog-grid');
        await gridCard.screenshot({ path: path.join(targetDir, '06_pasca_ikon.png') });
        console.log('Saved 06_pasca_ikon.png');
        
        // Pra
        await page.evaluate(() => {
            document.querySelectorAll('.services-v2-card__icon').forEach(el => el.style.display = 'none');
        });
        await gridCard.screenshot({ path: path.join(targetDir, '05_pra_ikon.png') });
        console.log('Saved 05_pra_ikon.png');


        // --- 4. Aspek Interaksi (Back to Top) ---
        await page.goto('http://127.0.0.1:8000/panduan', { waitUntil: 'load' });
        await page.waitForTimeout(1000); // Wait for page to settle

        // Scroll to the bottom so back-to-top appears
        await page.evaluate(() => window.scrollBy(0, 1500));
        await page.waitForTimeout(1000); // wait for button opacity transition

        // We want to capture the bottom right corner of the viewport!
        // We can do this by taking a screenshot and clipping the bottom right corner of the VIEWPORT.
        // Wait, clip is absolute to document. So we clip at x: width-350, y: scrollY + height - 350
        const yClip = await page.evaluate(() => window.scrollY + window.innerHeight - 400);
        
        await page.screenshot({ 
            path: path.join(targetDir, '08_pasca_interaksi.png'),
            clip: { x: 1280 - 400, y: yClip, width: 350, height: 350 }
        });
        console.log('Saved 08_pasca_interaksi.png');
        
        // Pra: button is missing/hidden
        await page.evaluate(() => {
            const btn = document.getElementById('backToTopBtn');
            if(btn) btn.style.display = 'none';
        });
        await page.screenshot({ 
            path: path.join(targetDir, '07_pra_interaksi.png'),
            clip: { x: 1280 - 400, y: yClip, width: 350, height: 350 }
        });
        console.log('Saved 07_pra_interaksi.png');

    } catch (e) {
        console.error("Error:", e);
    } finally {
        await browser.close();
    }
})();
