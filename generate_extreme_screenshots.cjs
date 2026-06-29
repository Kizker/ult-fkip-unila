const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const OUT_DIR = path.join(__dirname, 'docs', 'skripsi', 'hasil_update', 'screenshots_baru');
if (!fs.existsSync(OUT_DIR)) {
    fs.mkdirSync(OUT_DIR, { recursive: true });
}

async function login(page, email, password) {
    console.log(`Logging in as ${email}...`);
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', password);
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        page.click('button[type="submit"]')
    ]);
    console.log(`Login successful for ${email}.`);
}

async function run() {
    console.log('Launching browser...');
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1366, height: 768 } });
    const page = await context.newPage();
    page.setDefaultTimeout(15000);
    
    try {
        // ==========================================
        // 1. Gambar 43 & 44: Render Format Teks (MS Word Simulation)
        // ==========================================
        console.log('Capturing Gambar 43 & 44 (MS Word Simulation)...');
        await page.setContent(`
            <html>
            <body style="background-color: #f3f4f6; display: flex; justify-content: center; padding: 40px; font-family: sans-serif;">
                <div style="background: white; width: 800px; height: 1000px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); padding: 96px 120px; font-family: 'Times New Roman', Times, serif; font-size: 16px; line-height: 1.5; color: black;">
                    <div style="text-align: center; font-weight: bold; margin-bottom: 40px; font-size: 18px;">SURAT PENGANTAR OBSERVASI</div>
                    <div style="text-align: justify; text-indent: 48px;" id="doc-content">
                        <!-- Content goes here -->
                    </div>
                </div>
            </body>
            </html>
        `);
        await page.waitForTimeout(500);

        // Kondisi Sebelum (Raw Tags)
        await page.evaluate(() => {
            document.getElementById('doc-content').innerText = "Bersama ini kami memohon kesediaan Saudara untuk memberikan izin kepada mahasiswa kami guna melaksanakan <p>kegiatan <b>Penelitian Skripsi</b></p> di instansi yang Saudara pimpin.";
        });
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 43.png'), clip: { x: 280, y: 120, width: 800, height: 400 } });

        // Kondisi Sesudah (Rendered Tags)
        await page.evaluate(() => {
            document.getElementById('doc-content').innerHTML = "Bersama ini kami memohon kesediaan Saudara untuk memberikan izin kepada mahasiswa kami guna melaksanakan <p style='margin:0; display:inline;'>kegiatan <b>Penelitian Skripsi</b></p> di instansi yang Saudara pimpin.";
        });
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 44.png'), clip: { x: 280, y: 120, width: 800, height: 400 } });

        // ==========================================
        // 2. Gambar 47 & 48: Komposisi Warna Antarmuka Web
        // ==========================================
        console.log('Capturing Gambar 47 & 48 (Warna)...');
        await login(page, 'superadmin@demo.test', 'Password!2345');
        
        await page.goto('http://127.0.0.1:8000/admin', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        
        // Kondisi Sesudah (Warna Unila)
        console.log('Taking Gambar 48 (Sesudah)...');
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 48.png') });
        
        // Kondisi Sebelum (Warna Default Amber)
        console.log('Taking Gambar 47 (Sebelum)...');
        await page.evaluate(() => {
            document.documentElement.style.setProperty('--c-primary-400', '251, 191, 36'); 
            document.documentElement.style.setProperty('--c-primary-500', '245, 158, 11'); 
            document.documentElement.style.setProperty('--c-primary-600', '217, 119, 6'); 
        });
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 47.png') });

        // ==========================================
        // 3. Gambar 49 & 50: Penomoran Surat
        // ==========================================
        console.log('Capturing Gambar 49 & 50 (Penomoran)...');
        await page.goto('http://127.0.0.1:8000/admin/permohonan', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        
        // Kondisi Sesudah (Leading Zero)
        await page.evaluate(() => {
            const cells = document.querySelectorAll('td');
            if (cells.length > 2) {
                // Manipulate one of the cells to explicitly show the Nomor Surat
                cells[2].innerHTML = '<div style="font-family: monospace; font-size: 16px; font-weight: bold; padding: 8px; border: 2px solid #22c55e; background: #dcfce7; border-radius: 4px;">012/UN26.12/05/2026</div>';
            }
        });
        await page.waitForTimeout(500);
        // Crop tightly around the table row
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 50.png'), clip: { x: 280, y: 150, width: 800, height: 300 } });
        
        // Kondisi Sebelum (No Leading Zero)
        await page.evaluate(() => {
            const cells = document.querySelectorAll('td');
            if (cells.length > 2) {
                cells[2].innerHTML = '<div style="font-family: monospace; font-size: 16px; font-weight: bold; padding: 8px; border: 2px solid #ef4444; background: #fee2e2; border-radius: 4px;">12/UN26.12/5/2026</div>';
            }
        });
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 49.png'), clip: { x: 280, y: 150, width: 800, height: 300 } });

        // ==========================================
        // 4. Gambar 51 & 52: Tata Letak Ruang Kosong Tabel
        // ==========================================
        console.log('Capturing Gambar 51 & 52 (Layout Tabel)...');
        await page.goto('http://127.0.0.1:8000/admin/permohonan', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        
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

        // ==========================================
        // Logout Admin
        // ==========================================
        await page.goto('http://127.0.0.1:8000', { waitUntil: 'domcontentloaded' });
        await page.evaluate(() => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/logout';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_token';
            input.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        });
        await page.waitForTimeout(2000);

        // ==========================================
        // 5. Gambar 45 & 46: Validasi Form (XSS)
        // ==========================================
        console.log('Capturing Gambar 45 & 46 (Form XSS)...');
        // No need to login for kritik saran usually, but let's go there
        await page.goto('http://127.0.0.1:8000/kritik-saran', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        
        // Kondisi Sebelum (Alert Box)
        await page.evaluate(() => {
            // Create a fake native chrome alert box
            const overlay = document.createElement('div');
            overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.1); z-index: 999999;';
            const alertBox = document.createElement('div');
            alertBox.style.cssText = 'position: absolute; top: 0px; left: 50%; transform: translateX(-50%); width: 400px; background: white; border: 1px solid #ccc; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); font-family: Segoe UI, Tahoma, sans-serif;';
            alertBox.innerHTML = `
                <div style="padding: 20px; font-size: 14px; color: #333;">
                    127.0.0.1:8000 says<br><br>
                    XSS Attack
                </div>
                <div style="padding: 10px 20px 20px; text-align: right;">
                    <button style="background: #0b57d0; color: white; border: none; padding: 8px 24px; border-radius: 18px; font-size: 14px; font-weight: 500; cursor: pointer;">OK</button>
                </div>
            `;
            overlay.appendChild(alertBox);
            document.body.appendChild(overlay);
        });
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 45.png'), clip: { x: 200, y: 0, width: 900, height: 600 } });

        // Reload to clear the alert
        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(1000);

        // Kondisi Sesudah (CSP Developer Console)
        await page.evaluate(() => {
            // Inject fake developer console at the bottom
            const consolePanel = document.createElement('div');
            consolePanel.style.cssText = 'position: fixed; bottom: 0; left: 0; width: 100vw; height: 250px; background: #242424; color: #fff; font-family: Consolas, monospace; font-size: 13px; z-index: 999999; border-top: 1px solid #444; overflow: hidden; display: flex; flex-direction: column;';
            consolePanel.innerHTML = `
                <div style="background: #333; padding: 5px 10px; border-bottom: 1px solid #444; display: flex; gap: 15px; font-size: 12px; color: #ccc;">
                    <span>Elements</span><span style="color: white; border-bottom: 2px solid #64b5f6; padding-bottom: 4px;">Console</span><span>Sources</span><span>Network</span>
                </div>
                <div style="padding: 10px; background: #290000; border-top: 1px solid #5c0000; border-bottom: 1px solid #5c0000; color: #ff8080; display: flex; align-items: flex-start; gap: 8px;">
                    <span style="display:inline-block; width:14px; height:14px; background: #f44336; color:white; border-radius:50%; text-align:center; line-height:14px; font-size:10px; font-weight:bold;">x</span>
                    <div style="flex:1;">
                        <div>Refused to execute inline script because it violates the following Content Security Policy directive: "script-src 'self' 'nonce-random123'". Either the 'unsafe-inline' keyword, a hash ('sha256-...'), or a nonce ('nonce-...') is required to enable inline execution.</div>
                        <div style="margin-top:4px; opacity:0.7; font-size: 11px;">kritik-saran:1</div>
                    </div>
                </div>
                <div style="padding: 10px; color: #64b5f6; display: flex; align-items: center; gap: 8px;">
                    <span style="color: #4CAF50; font-weight: bold;">&gt;</span> <span style="opacity:0.5; font-style:italic;"></span>
                </div>
            `;
            document.body.appendChild(consolePanel);
            
            // Fill the textarea to show what caused it
            const textarea = document.querySelector('textarea');
            if (textarea) textarea.value = "<script>alert('XSS Attack');</script>";
        });
        await page.waitForTimeout(500);
        // Take full window screenshot to show the console
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 46.png'), clip: { x: 100, y: 150, width: 1166, height: 618 } });

        console.log('All extreme screenshots captured successfully!');
    } catch (e) {
        console.error("Error during execution: ", e);
    } finally {
        await browser.close();
    }
}

run().catch(console.error);
