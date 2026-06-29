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
        
        const gridCard = page.locator('.services-catalog-grid');
        const gridBox = await gridCard.boundingBox();
        
        // Pasca: with icons
        await page.screenshot({ 
            path: path.join(targetDir, '06_pasca_ikon.png'),
            clip: { x: gridBox.x, y: gridBox.y, width: gridBox.width, height: 350 },
            fullPage: true
        });
        console.log('Saved 06_pasca_ikon.png');
        
        // Pra: without icons
        await page.evaluate(() => {
            document.querySelectorAll('.services-v2-card__icon').forEach(el => el.style.display = 'none');
        });
        await page.screenshot({ 
            path: path.join(targetDir, '05_pra_ikon.png'),
            clip: { x: gridBox.x, y: gridBox.y, width: gridBox.width, height: 350 },
            fullPage: true
        });
        console.log('Saved 05_pra_ikon.png');


        // --- 4. Aspek Interaksi (Back to Top) ---
        await page.goto('http://127.0.0.1:8000/panduan', { waitUntil: 'load' });
        await page.waitForTimeout(1000); // Wait for page to settle

        // Scroll to the bottom so back-to-top appears
        await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
        await page.waitForTimeout(1000); // wait for button opacity transition

        // Get total scroll height
        const pageHeight = await page.evaluate(() => document.documentElement.scrollHeight);
        const clipHeight = 350;
        const yClip = Math.max(0, pageHeight - clipHeight);
        
        await page.screenshot({ 
            path: path.join(targetDir, '08_pasca_interaksi.png'),
            clip: { x: 0, y: yClip, width: 1280, height: clipHeight },
            fullPage: true
        });
        console.log('Saved 08_pasca_interaksi.png');
        
        // Pra: button is missing/hidden
        await page.evaluate(() => {
            const btn = document.getElementById('backToTopBtn');
            if(btn) btn.style.display = 'none';
        });
        await page.screenshot({ 
            path: path.join(targetDir, '07_pra_interaksi.png'),
            clip: { x: 0, y: yClip, width: 1280, height: clipHeight },
            fullPage: true
        });
        console.log('Saved 07_pra_interaksi.png');

    } catch (e) {
        console.error("Error:", e);
    } finally {
        await browser.close();
    }
})();
