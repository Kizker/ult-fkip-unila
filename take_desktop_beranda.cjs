const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  try {
    // Standard desktop viewport. We only want one screenshot of this exact size.
    const page = await browser.newPage({ viewport: { width: 1366, height: 1000 } });
    
    // Intercept IntersectionObserver to instantly trigger all scroll-reveal animations
    await page.addInitScript(() => {
      window.IntersectionObserver = class IntersectionObserver {
        constructor(callback) { this.callback = callback; }
        observe(element) {
          setTimeout(() => {
            this.callback([{ isIntersecting: true, target: element, intersectionRatio: 1 }]);
          }, 50);
        }
        unobserve() {} disconnect() {}
      };
    });
    
    console.log('Navigating to Beranda...');
    await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle', timeout: 30000 });
    
    console.log('Injecting CSS to compress hero...');
    await page.addStyleTag({ content: `
      /* Compress hero height to 600px so the sections below it appear in the 1000px viewport */
      .ult-hero, .h-screen, .min-h-screen { 
          min-height: 600px !important; 
          height: 600px !important; 
          max-height: 600px !important; 
      }
    ` });
    
    console.log('Waiting for render...');
    await page.waitForTimeout(2000);
    
    // Take a single, normal viewport screenshot (NO fullPage, NO stitching)
    console.log('Taking viewport screenshot...');
    await page.screenshot({ 
        path: 'C:/laragon/www/ult-fkip-unila/docs/skripsi/rancangan_diagram/screenshots/01-beranda-sistem.jpg', 
        type: 'jpeg', 
        quality: 100,
        fullPage: false
    });
    
    console.log('Saved 01-beranda-sistem.jpg successfully!');
  } catch(e) {
    console.error(e);
  } finally {
    await browser.close();
  }
})();
