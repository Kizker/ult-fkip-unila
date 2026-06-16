const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage({ viewport: { width: 1366, height: 900 } });
    
    console.log('Navigating to login...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    
    console.log('Logging in as Mahasiswa...');
    await page.fill('input[name="email"]', 'mahasiswa@demo.test');
    await page.fill('input[name="password"]', 'Password!2345');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle' }),
      page.click('button[type="submit"]')
    ]);
    
    console.log('Navigating to Form Pengajuan...');
    await page.goto('http://127.0.0.1:8000/mahasiswa/permohonan/buat/surat-persetujuan-pra-penelitian-sFQuYM', { waitUntil: 'networkidle' });
    
    console.log('On Form Pengajuan page!');
    
    // Wait for Alpine/Tailwind rendering
    await page.waitForTimeout(2000);
    
    // Hide scrollbar
    await page.addStyleTag({ content: '::-webkit-scrollbar { display: none; } body { -ms-overflow-style: none; scrollbar-width: none; }' });
    
    // Capture desktop viewport
    console.log('Taking viewport screenshot...');
    await page.screenshot({ 
        path: 'C:/laragon/www/ult-fkip-unila/docs/skripsi/rancangan_diagram/screenshots/08-form-pengajuan.jpg', 
        type: 'jpeg', 
        quality: 100,
        fullPage: false 
    });
    console.log('Saved 08-form-pengajuan.jpg successfully!');
    
  } catch(e) {
    console.error(e);
  } finally {
    await browser.close();
  }
})();
