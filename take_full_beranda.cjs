const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  try {
    // 1. First set the viewport to standard desktop so layout logic (and VH units) calculate correctly initially.
    const page = await browser.newPage({ viewport: { width: 1366, height: 900 } });
    
    console.log('Navigating to homepage...');
    await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle', timeout: 30000 });
    
    console.log('Injecting CSS to fix VH unit explosion during fullPage screenshot...');
    await page.addStyleTag({ content: `
      /* Fix VH unit explosion: when Playwright expands the viewport height for fullPage: true, 
         100vh becomes 3000px+, making the hero take up the whole screenshot and pushing everything down!
         We lock the hero height to standard desktop height (900px) so it stays proportional. */
      .ult-hero, .min-h-screen, .h-screen { 
          min-height: 850px !important; 
          height: 850px !important; 
          max-height: 850px !important; 
      }
      
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
    await page.evaluate(async () => {
        for (let i = 0; i < document.body.scrollHeight; i += 200) {
            window.scrollTo(0, i);
            await new Promise(r => setTimeout(r, 50));
        }
        window.scrollTo(0, 0);
    });
    
    console.log('Waiting for render...');
    await page.waitForTimeout(2000);
    
    console.log('Taking FULL PAGE screenshot...');
    await page.screenshot({ path: 'C:/laragon/www/ult-fkip-unila/docs/skripsi/rancangan_diagram/screenshots/01-beranda-sistem_raw.jpg', type: 'jpeg', quality: 90, fullPage: true });
    console.log('Screenshot saved!');
  } catch(e) {
    console.error('Fatal error:', e);
  } finally {
    await browser.close();
  }
})();
