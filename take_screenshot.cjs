const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage({ viewport: { width: 1366, height: 1000 } });
    
    console.log('Navigating...');
    await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle', timeout: 30000 });
    
    console.log('Injecting CSS to fix hero and disable animations...');
    await page.addStyleTag({ content: `
      .min-h-screen { min-height: 60vh !important; } 
      .py-24, .py-32, .pt-32, .pb-32 { padding-top: 2rem !important; padding-bottom: 2rem !important; }
      .mt-20 { margin-top: 2rem !important; }
      
      /* Force all lazy loaded / animated elements to be visible! */
      .ult-reveal, [class*="opacity-0"], .invisible {
          opacity: 1 !important;
          transform: none !important;
          visibility: visible !important;
          animation: none !important;
          transition: none !important;
      }
    ` });
    
    console.log('Scrolling down to trigger any remaining lazy loads...');
    // Scroll down slowly
    await page.evaluate(async () => {
        for (let i = 0; i < document.body.scrollHeight; i += 200) {
            window.scrollTo(0, i);
            await new Promise(r => setTimeout(r, 50));
        }
        window.scrollTo(0, 0);
    });
    
    console.log('Waiting for render...');
    await page.waitForTimeout(2000);
    
    console.log('Taking screenshot...');
    await page.screenshot({ path: 'C:/laragon/www/ult-fkip-unila/docs/skripsi/rancangan_diagram/screenshots/01-beranda-sistem.jpg', type: 'jpeg', quality: 90, fullPage: true });
    console.log('Screenshot saved!');
  } catch(e) {
    console.error('Fatal error:', e);
  } finally {
    await browser.close();
  }
})();
