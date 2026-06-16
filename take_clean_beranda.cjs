const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage({ viewport: { width: 1366, height: 900 } });
    
    console.log('Navigating...');
    await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle', timeout: 30000 });
    
    console.log('Injecting CSS to lock hero height and hide footer...');
    await page.addStyleTag({ content: `
      /* Lock hero height so it doesn't stretch during fullPage screenshot */
      .ult-hero, .h-screen, .min-h-screen { 
          min-height: 800px !important; 
          height: 800px !important; 
          max-height: 800px !important; 
      }
      /* Hide footer */
      footer, .page-public-footer, .public-footer { display: none !important; }
      
      /* Force reveal elements to be visible */
      .ult-reveal {
          opacity: 1 !important;
          visibility: visible !important;
          transform: none !important;
          transition: none !important;
          animation: none !important;
      }
    ` });
    
    // Naturally scroll down to trigger any lazy loading or IntersectionObservers
    console.log('Scrolling down...');
    await page.evaluate(async () => {
        for (let i = 0; i < document.body.scrollHeight; i += 200) {
            window.scrollTo(0, i);
            await new Promise(r => setTimeout(r, 50));
        }
        window.scrollTo(0, 0); // Scroll back to top
    });
    
    console.log('Waiting for render...');
    await page.waitForTimeout(2000);
    
    console.log('Taking fullpage screenshot...');
    await page.screenshot({ 
        path: 'C:/laragon/www/ult-fkip-unila/docs/skripsi/rancangan_diagram/screenshots/01-beranda-sistem.jpg', 
        type: 'jpeg', 
        quality: 90,
        fullPage: true
    });
    console.log('Saved!');
    
  } catch(e) {
    console.error(e);
  } finally {
    await browser.close();
  }
})();
