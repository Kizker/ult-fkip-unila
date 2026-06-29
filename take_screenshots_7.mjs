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
        await page.goto('http://127.0.0.1:8000/layanan', { waitUntil: 'networkidle' });
        await page.waitForSelector('.services-catalog-grid', { timeout: 10000 });
        
        // Remove all cards except the first 3 (so only 1 row is visible)
        await page.evaluate(() => {
            const grid = document.querySelector('.services-catalog-grid');
            if (grid) {
                const children = Array.from(grid.children);
                for (let i = 3; i < children.length; i++) {
                    grid.removeChild(children[i]);
                }
            }
        });
        
        // Wait a moment for layout to settle
        await page.waitForTimeout(500);

        const gridCard = page.locator('.services-catalog-grid');
        
        // Pasca: with icons
        await gridCard.screenshot({ path: path.join(targetDir, '06_pasca_ikon.png') });
        console.log('Saved 06_pasca_ikon.png');
        
        // Pra: without icons
        await page.evaluate(() => {
            document.querySelectorAll('.services-v2-card__icon').forEach(el => el.style.display = 'none');
        });
        await gridCard.screenshot({ path: path.join(targetDir, '05_pra_ikon.png') });
        console.log('Saved 05_pra_ikon.png');


        // --- 4. Aspek Interaksi (Back to Top) ---
        // Let's use /layanan which we know is long and works
        await page.goto('http://127.0.0.1:8000/layanan', { waitUntil: 'networkidle' });
        
        // Scroll to the bottom so back-to-top appears
        await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
        await page.waitForTimeout(1500); // wait for scroll and button opacity transition

        await page.screenshot({ 
            path: path.join(targetDir, '08_pasca_interaksi_full.png'),
            fullPage: true
        });
        console.log('Saved 08_pasca_interaksi_full.png');
        
        // Pra: button is missing/hidden
        await page.evaluate(() => {
            const btn = document.getElementById('backToTopBtn');
            if(btn) btn.style.display = 'none';
        });
        await page.screenshot({ 
            path: path.join(targetDir, '07_pra_interaksi_full.png'),
            fullPage: true
        });
        console.log('Saved 07_pra_interaksi_full.png');

    } catch (e) {
        console.error("Error:", e);
    } finally {
        await browser.close();
    }
})();
