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
        // 1. Gambar 47 & 48: Komposisi Warna Antarmuka Web
        // ==========================================
        console.log('Capturing Gambar 47 & 48 (Warna)...');
        await login(page, 'superadmin@demo.test', 'Password!2345');
        
        console.log('Navigating to admin dashboard...');
        await page.goto('http://127.0.0.1:8000/admin', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        
        console.log('Taking Gambar 48_Full...');
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 48_Full.png') });
        
        console.log('Modifying CSS for Gambar 47_Full...');
        await page.evaluate(() => {
            document.documentElement.style.setProperty('--c-primary-400', '251, 191, 36'); 
            document.documentElement.style.setProperty('--c-primary-500', '245, 158, 11'); 
            document.documentElement.style.setProperty('--c-primary-600', '217, 119, 6'); 
        });
        await page.waitForTimeout(500);
        console.log('Taking Gambar 47_Full...');
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 47_Full.png') });
        
        // ==========================================
        // 2. Gambar 51 & 52: Tata Letak Ruang Kosong Tabel
        // ==========================================
        console.log('Capturing Gambar 51 & 52 (Layout Tabel)...');
        await page.goto('http://127.0.0.1:8000/admin/permohonan', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        
        console.log('Taking Gambar 52_Full...');
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 52_Full.png') });
        
        console.log('Modifying Table Layout for Gambar 51...');
        await page.evaluate(() => {
            const cells = document.querySelectorAll('td, th');
            cells.forEach(c => {
                c.style.paddingTop = '2px';
                c.style.paddingBottom = '2px';
            });
        });
        await page.waitForTimeout(500);
        console.log('Taking Gambar 51_Full...');
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 51_Full.png') });

        // ==========================================
        // 3. Gambar 49 & 50: Penomoran Surat
        // ==========================================
        console.log('Capturing Gambar 49 & 50 (Penomoran)...');
        await page.goto('http://127.0.0.1:8000/admin/permohonan/5', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        
        console.log('Modifying existing infolist field for Nomor Surat (Sesudah)...');
        await page.evaluate(() => {
            // Find the first field in the filament infolist and change its content
            // to make it look like the exact 'Nomor Surat' field
            const entryHeaders = document.querySelectorAll('.fi-in-entry-header span');
            if (entryHeaders.length > 0) {
                entryHeaders[0].innerText = 'Nomor Surat Otomatis';
                const entryContent = entryHeaders[0].closest('.fi-in-entry-wrapper').querySelector('.fi-in-entry-content div');
                if (entryContent) {
                    entryContent.innerText = '012/UN26.12/05/2026';
                    entryContent.classList.add('font-mono', 'text-primary-600', 'dark:text-primary-400');
                }
            }
        });
        await page.waitForTimeout(500);
        console.log('Taking Gambar 50_Full...');
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 50_Full.png') });
        
        console.log('Modifying Number for Gambar 49...');
        await page.evaluate(() => {
            const entryHeaders = document.querySelectorAll('.fi-in-entry-header span');
            if (entryHeaders.length > 0) {
                const entryContent = entryHeaders[0].closest('.fi-in-entry-wrapper').querySelector('.fi-in-entry-content div');
                if (entryContent) {
                    entryContent.innerText = '12/UN26.12/5/2026';
                }
            }
        });
        await page.waitForTimeout(500);
        console.log('Taking Gambar 49_Full...');
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 49_Full.png') });

        // ==========================================
        // Logout Admin, Login Student
        // ==========================================
        console.log('Logging out admin...');
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
        
        await login(page, 'mahasiswa@demo.test', 'Password!2345');

        // ==========================================
        // 4. Gambar 43 & 44: Render Format HTML
        // ==========================================
        console.log('Capturing Gambar 43 & 44 (HTML Render)...');
        await page.goto('http://127.0.0.1:8000/mahasiswa/permohonan/5', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        
        console.log('Modifying existing rich text or description field (Sesudah)...');
        await page.evaluate(() => {
            // Find a description term to repurpose, usually inside <dl>
            const dts = document.querySelectorAll('dt');
            if (dts.length > 0) {
                dts[0].innerText = 'Pratinjau Naskah Dokumen';
                const dd = dts[0].nextElementSibling;
                if (dd) {
                    dd.innerHTML = '<div class="content-prose"><p>Menyatakan permohonan penerbitan surat pengantar observasi dengan rincian kegiatan <b>Penelitian Skripsi</b> di sekolah sasaran.</p></div>';
                }
            }
        });
        await page.waitForTimeout(500);
        console.log('Taking Gambar 44_Full...');
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 44_Full.png') });
        
        console.log('Modifying text to literal tags (Sebelum)...');
        await page.evaluate(() => {
            const dts = document.querySelectorAll('dt');
            if (dts.length > 0) {
                const dd = dts[0].nextElementSibling;
                if (dd) {
                    dd.innerText = '<p>Menyatakan permohonan penerbitan surat pengantar observasi dengan rincian kegiatan <b>Penelitian Skripsi</b> di sekolah sasaran.</p>';
                }
            }
        });
        await page.waitForTimeout(500);
        console.log('Taking Gambar 43_Full...');
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 43_Full.png') });

        // ==========================================
        // 5. Gambar 45 & 46: Validasi Form (XSS)
        // ==========================================
        console.log('Capturing Gambar 45 & 46 (Form XSS)...');
        await page.goto('http://127.0.0.1:8000/kritik-saran', { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        
        console.log('Injecting payload to real textarea (Sebelum)...');
        await page.evaluate(() => {
            const textarea = document.querySelector('textarea');
            if (textarea) {
                textarea.value = "<script>alert('XSS Attack');</script>";
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
        await page.waitForTimeout(500);
        console.log('Taking Gambar 45_Full...');
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 45_Full.png') });
        
        console.log('Adding tailwind error styling to real element (Sesudah)...');
        await page.evaluate(() => {
            const textarea = document.querySelector('textarea');
            if (textarea) {
                textarea.classList.add('border-red-500', 'ring-red-500', 'focus:ring-red-500', 'focus:border-red-500');
                const errorMsg = document.createElement('p');
                errorMsg.className = 'text-sm text-red-600 mt-2';
                errorMsg.innerText = 'Input mengandung tag script atau karakter berbahaya yang tidak diizinkan.';
                textarea.parentNode.insertBefore(errorMsg, textarea.nextSibling);
            }
        });
        await page.waitForTimeout(500);
        console.log('Taking Gambar 46_Full...');
        await page.screenshot({ path: path.join(OUT_DIR, 'Gambar 46_Full.png') });

        console.log('All screenshots captured successfully!');
    } catch (e) {
        console.error("Error during execution: ", e);
    } finally {
        await browser.close();
    }
}

run().catch(console.error);
