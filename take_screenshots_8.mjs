import { chromium } from 'playwright';
import path from 'path';

const targetDir = 'C:\\laragon\\www\\ult-fkip-unila\\docs\\skripsi\\hasil_update\\gambar_expert_feedback';

(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext({ viewport: { width: 1280, height: 1000 } });
    const page = await context.newPage();

    try {
        // --- 3. Aspek Ikon (Katalog Layanan) ---
        await page.goto('http://127.0.0.1:8000/layanan', { waitUntil: 'networkidle' });
        await page.waitForSelector('.services-catalog-grid', { timeout: 10000 });
        
        // Scroll to grid so Alpine x-intersect triggers animations
        await page.evaluate(() => {
            document.querySelector('.services-catalog-grid').scrollIntoView();
        });
        
        // Remove all cards except the first 3
        await page.evaluate(() => {
            const grid = document.querySelector('.services-catalog-grid');
            if (grid) {
                const children = Array.from(grid.children);
                for (let i = 3; i < children.length; i++) {
                    grid.removeChild(children[i]);
                }
            }
        });
        
        // Wait for Alpine animations to finish (usually 300-500ms)
        await page.waitForTimeout(1000);

        const gridCard = page.locator('.services-catalog-grid');
        
        // Pasca: with icons
        await gridCard.screenshot({ path: path.join(targetDir, '06_pasca_ikon.png') });
        console.log('Saved 06_pasca_ikon.png');
        
        // Pra: without icons
        await page.evaluate(() => {
            document.querySelectorAll('.services-v2-card__icon').forEach(el => el.style.display = 'none');
        });
        // wait for layout
        await page.waitForTimeout(500);
        await gridCard.screenshot({ path: path.join(targetDir, '05_pra_ikon.png') });
        console.log('Saved 05_pra_ikon.png');

    } catch (e) {
        console.error("Error:", e);
    } finally {
        await browser.close();
    }
})();
