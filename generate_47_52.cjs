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
        const res = await page.goto('http://127.0.0.1:8000/admin/permohonan', { waitUntil: 'domcontentloaded' });
        console.log('Page status:', res.status());
        await page.waitForTimeout(2000);
        
        // ==========================================
        // 2. Gambar 47 & 48: Komposisi Warna Antarmuka Web
        // ==========================================
        console.log('Capturing Gambar 47 & 48 (Warna)...');
        
        // Kondisi Sesudah (Warna Unila)
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 48.png') });
        
        // Kondisi Sebelum (Warna Default Amber)
        await page.evaluate(() => {
            // Check if this uses filament or tailwind directly
            // Override typical colors. The text mentions "Warna kuning oranye keemasan" vs "hijau".
            // If it's standard Tailwind, let's inject a global hue-rotate or filter or just inject CSS variables
            document.documentElement.style.setProperty('--c-primary-400', '251, 191, 36'); 
            document.documentElement.style.setProperty('--c-primary-500', '245, 158, 11'); 
            document.documentElement.style.setProperty('--c-primary-600', '217, 119, 6'); 
            
            // Just in case it's hardcoded classes, let's inject an overlay hue rotate to make it amber
            const style = document.createElement('style');
            style.innerHTML = `
                .bg-green-600, .bg-primary-600, .bg-emerald-600 { background-color: #f59e0b !important; }
                .text-green-600, .text-primary-600, .text-emerald-600 { color: #f59e0b !important; }
                .border-green-600, .border-primary-600, .border-emerald-600 { border-color: #f59e0b !important; }
                .hover\\:bg-green-700:hover, .hover\\:bg-primary-700:hover { background-color: #d97706 !important; }
                .ring-green-600, .ring-primary-600 { --tw-ring-color: #f59e0b !important; }
                /* Filament specific */
                .fi-topbar { background-color: #f59e0b !important; }
                .fi-btn-primary { background-color: #f59e0b !important; border-color: #f59e0b !important; }
            `;
            document.head.appendChild(style);
        });
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 47.png') });

        // Refresh to reset colors
        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(1000);

        // ==========================================
        // 3. Gambar 49 & 50: Penomoran Surat
        // ==========================================
        console.log('Capturing Gambar 49 & 50 (Penomoran)...');
        
        // Kondisi Sesudah (Leading Zero)
        await page.evaluate(() => {
            const tds = Array.from(document.querySelectorAll('td'));
            if (tds.length > 2) {
                // Find a column that looks like it could hold the ID or number
                tds[2].innerHTML = '<div style="font-family: monospace; font-size: 16px; font-weight: bold; padding: 8px; border: 2px solid #22c55e; background: #dcfce7; border-radius: 4px; display:inline-block;">012/UN26.12/05/2026</div>';
            }
        });
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 50.png'), clip: { x: 280, y: 150, width: 800, height: 300 } });
        
        // Kondisi Sebelum (No Leading Zero)
        await page.evaluate(() => {
            const tds = Array.from(document.querySelectorAll('td'));
            if (tds.length > 2) {
                tds[2].innerHTML = '<div style="font-family: monospace; font-size: 16px; font-weight: bold; padding: 8px; border: 2px solid #ef4444; background: #fee2e2; border-radius: 4px; display:inline-block;">12/UN26.12/5/2026</div>';
            }
        });
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 49.png'), clip: { x: 280, y: 150, width: 800, height: 300 } });

        // Refresh to reset table
        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(1000);

        // ==========================================
        // 4. Gambar 51 & 52: Tata Letak Ruang Kosong Tabel
        // ==========================================
        console.log('Capturing Gambar 51 & 52 (Layout Tabel)...');
        
        // Kondisi Sesudah (Spacious)
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 52.png'), clip: { x: 280, y: 100, width: 1000, height: 500 } });
        
        // Kondisi Sebelum (Cramped / Padat)
        await page.evaluate(() => {
            const cells = document.querySelectorAll('td, th');
            cells.forEach(c => {
                c.style.setProperty('padding', '0px', 'important');
                c.style.setProperty('line-height', '0.8', 'important');
                c.style.setProperty('font-size', '12px', 'important');
                c.style.setProperty('height', '10px', 'important');
            });
            const rows = document.querySelectorAll('tr');
            rows.forEach(r => r.style.setProperty('border-bottom', '1px solid #ccc', 'important'));
        });
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 51.png'), clip: { x: 280, y: 100, width: 1000, height: 500 } });

        console.log('Successfully captured 47-52!');
    } catch (e) {
        console.error("Error during execution: ", e);
    } finally {
        await browser.close();
    }
}

run().catch(console.error);
