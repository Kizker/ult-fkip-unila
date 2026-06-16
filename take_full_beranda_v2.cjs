const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  try {
    // Huge viewport so we don't need to scroll. Everything renders instantly.
    const page = await browser.newPage({ viewport: { width: 1366, height: 4000 } });
    
    console.log('Navigating to homepage...');
    await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle', timeout: 30000 });
    
    console.log('Injecting CSS to lock Hero height and reveal elements...');
    await page.addStyleTag({ content: `
      /* Lock hero height so it doesn't take up the full 4000px viewport */
      .ult-hero, .h-screen, .min-h-screen { 
          min-height: 800px !important; 
          height: 800px !important; 
          max-height: 800px !important; 
      }
      
      /* Make sure EVERYTHING is visible */
      * {
          opacity: 1 !important;
          visibility: visible !important;
          transform: none !important;
          animation: none !important;
          transition: none !important;
      }
      
      /* Hide the footer as requested by user */
      footer, .page-public-footer, .public-footer {
          display: none !important;
      }
    ` });
    
    console.log('Waiting for render...');
    await page.waitForTimeout(3000);
    
    console.log('Taking screenshot of the document element bounding box...');
    const handle = await page.$('.ult-home'); // or body
    const box = await handle.boundingBox();
    
    // We take a screenshot of the exact height of the content (minus the footer which is now display:none)
    await page.screenshot({ 
        path: 'C:/laragon/www/ult-fkip-unila/docs/skripsi/rancangan_diagram/screenshots/01-beranda-sistem.jpg', 
        type: 'jpeg', 
        quality: 90, 
        clip: { x: 0, y: 0, width: 1366, height: box.height }
    });
    console.log('Screenshot saved with height:', box.height);
  } catch(e) {
    console.error('Fatal error:', e);
  } finally {
    await browser.close();
  }
})();
