const { chromium } = require('playwright');
const path = require('path');

const OUT_DIR = path.join(__dirname, 'docs', 'skripsi', 'hasil_update', 'screenshots_baru');

async function run() {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1366, height: 768 } });
    const page = await context.newPage();
    
    // Login as admin
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[name="email"]', 'superadmin@demo.test');
    await page.fill('input[name="password"]', 'Password!2345');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle' }),
        page.click('button[type="submit"]')
    ]);
    
    // Go to the layanan edit page
    await page.goto('http://127.0.0.1:8000/admin/layanan/15/edit');
    await page.waitForTimeout(2000);
    
    // Hide the sidebar and topnav if needed, or just capture the main form area
    try {
        const formArea = page.locator('.fi-main'); 
        await formArea.screenshot({ path: path.join(OUT_DIR, 'Gambar 39.png'), timeout: 5000 });
        console.log("Successfully captured Gambar 39 (cropped to .fi-main)");
    } catch(e) {
        console.log("Failed to capture .fi-main, capturing full page...");
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 39 (Full).png') });
    }

    await browser.close();
}

run().catch(console.error);
