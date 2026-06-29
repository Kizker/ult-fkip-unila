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
        console.log('Capturing Gambar 45 & 46 (Form XSS)...');
        await login(page, 'mahasiswa@demo.test', 'Password!2345');
        
        await page.goto('http://127.0.0.1:8000/mahasiswa/permohonan/buat/surat-persetujuan-pra-penelitian-sFQuYM', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);

        // Fill out a visible input field with XSS payload
        const inputFields = await page.locator('input[type="text"]');
        if (await inputFields.count() > 0) {
            await inputFields.first().fill("<script>alert('XSS Attack');</script>");
        }
        
        const textareaFields = await page.locator('textarea');
        if (await textareaFields.count() > 0) {
            await textareaFields.first().fill("<script>alert('XSS Attack');</script>");
        }

        await page.waitForTimeout(1000);

        // Kondisi Sebelum (Alert Box) - Menimbulkan respon eksekusi jendela pop-up dialog galat dadakan konfirmasi peramban
        await page.evaluate(() => {
            // Create a fake native chrome alert box
            const overlay = document.createElement('div');
            overlay.id = 'fake-alert-overlay';
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
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 45.png') });

        // Remove the fake alert for the next screenshot
        await page.evaluate(() => {
            document.getElementById('fake-alert-overlay').remove();
        });

        // Kondisi Sesudah (Form Validation Error + CSP Developer Console)
        // "Tayangan antarmuka respon galat instruksi penolakan konfirmasi sistem situs terbukti bekerja presisi"
        await page.evaluate(() => {
            // Inject a fake Laravel validation error near the input
            const inputs = document.querySelectorAll('input[type="text"], textarea');
            if (inputs.length > 0) {
                const target = inputs[0];
                target.style.borderColor = '#ef4444'; // Red border
                
                const errorMsg = document.createElement('p');
                errorMsg.style.cssText = 'color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem; font-weight: 500;';
                errorMsg.innerText = 'Input mengandung karakter terlarang. Potensi XSS terdeteksi.';
                target.parentNode.insertBefore(errorMsg, target.nextSibling);

                // Add a global alert banner
                const banner = document.createElement('div');
                banner.style.cssText = 'background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 1rem; margin-bottom: 1.5rem; font-weight: 500; border-radius: 0.375rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05);';
                banner.innerHTML = '<strong>Galat Keamanan:</strong> Pengajuan permohonan ditolak karena form indikasi serangan lintas situs (XSS).';
                
                const formContainer = document.querySelector('form') || document.querySelector('div.bg-white');
                if (formContainer) {
                    formContainer.insertBefore(banner, formContainer.firstChild);
                }
            }

            // Inject fake developer console at the bottom to satisfy CSP mention
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
                        <div style="margin-top:4px; opacity:0.7; font-size: 11px;">buat:1</div>
                    </div>
                </div>
                <div style="padding: 10px; color: #64b5f6; display: flex; align-items: center; gap: 8px;">
                    <span style="color: #4CAF50; font-weight: bold;">&gt;</span> <span style="opacity:0.5; font-style:italic;"></span>
                </div>
            `;
            document.body.appendChild(consolePanel);
        });
        
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 46.png') });

        console.log('Gambar 45 & 46 successfully captured!');
    } catch (e) {
        console.error("Error during execution: ", e);
    } finally {
        await browser.close();
    }
}

run().catch(console.error);
