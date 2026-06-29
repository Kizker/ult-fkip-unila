const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const OUT_DIR = path.join(__dirname, 'docs', 'skripsi', 'hasil_update', 'screenshots_baru');

async function run() {
    console.log('Launching browser...');
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1366, height: 768 } });
    const page = await context.newPage();
    page.setDefaultTimeout(15000);
    
    try {
        console.log('Logging in as superadmin...');
        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'domcontentloaded' });
        await page.fill('input[name="email"]', 'superadmin@demo.test');
        await page.fill('input[name="password"]', 'Password!2345');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('button[type="submit"]')
        ]);

        console.log('Going to Admin Dashboard...');
        await page.goto('http://127.0.0.1:8000/admin/permohonan', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        
        // ==========================================
        // 2. Gambar 47 & 48: Komposisi Warna Antarmuka Web
        // ==========================================
        console.log('Capturing Gambar 47 & 48 (Warna)...');
        
        // --- Gambar 47 (Kondisi Sebelum: Warna Kuning Oranye Keemasan / Amber) ---
        await page.evaluate(() => {
            document.documentElement.style.setProperty('--c-primary', '245 158 11', 'important'); // amber-500
            document.documentElement.style.setProperty('--c-primary2', '180 83 9', 'important');  // amber-700
            document.documentElement.style.setProperty('--c-bg', '255 251 235', 'important');     // amber-50
            document.documentElement.style.setProperty('--c-bg-rgb', '255, 251, 235', 'important');     
            // If there's an active gradient, let's inject a strong override
            const style = document.createElement('style');
            style.innerHTML = `
                .bg-gradient-to-br { background: linear-gradient(to bottom right, #fef3c7, #fcd34d) !important; }
                .text-primary { color: #d97706 !important; }
                .bg-primary { background-color: #d97706 !important; }
                nav { background: linear-gradient(to bottom, #f59e0b, #d97706) !important; }
                .text-white { color: white !important; }
            `;
            document.head.appendChild(style);
        });
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 47.png') });

        // Reload to clear styles
        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(1000);
        
        // --- Gambar 48 (Kondisi Sesudah: Warna Hijau Unila) ---
        await page.evaluate(() => {
            document.documentElement.style.setProperty('--c-primary', '34 197 94', 'important'); // green-500
            document.documentElement.style.setProperty('--c-primary2', '21 128 61', 'important');  // green-700
            document.documentElement.style.setProperty('--c-bg', '240 253 244', 'important');     // green-50
            document.documentElement.style.setProperty('--c-bg-rgb', '240, 253, 244', 'important');     
            const style = document.createElement('style');
            style.innerHTML = `
                .bg-gradient-to-br { background: linear-gradient(to bottom right, #dcfce7, #86efac) !important; }
                .text-primary { color: #16a34a !important; }
                .bg-primary { background-color: #16a34a !important; }
                nav { background: linear-gradient(to bottom, #22c55e, #16a34a) !important; }
                .text-white { color: white !important; }
            `;
            document.head.appendChild(style);
        });
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 48.png') });

        console.log('Successfully captured 47 & 48!');
    } catch (e) {
        console.error("Error during execution: ", e);
    } finally {
        await browser.close();
    }
}

run().catch(console.error);
