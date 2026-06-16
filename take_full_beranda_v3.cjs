const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage({ viewport: { width: 1366, height: 900 } });
    
    console.log('Injecting IntersectionObserver Mock...');
    // This script runs before the page loads. It intercepts IntersectionObserver
    // and immediately tells the page that ALL elements are intersecting!
    // This perfectly triggers all reveal animations and lazy loading instantly.
    await page.addInitScript(() => {
      window.IntersectionObserver = class IntersectionObserver {
        constructor(callback, options) {
          this.callback = callback;
        }
        observe(element) {
          // Use setTimeout to allow the element to be inserted into the DOM first
          setTimeout(() => {
            this.callback([{
              isIntersecting: true,
              target: element,
              intersectionRatio: 1,
              boundingClientRect: element.getBoundingClientRect(),
              intersectionRect: element.getBoundingClientRect(),
              rootBounds: document.body.getBoundingClientRect()
            }]);
          }, 50);
        }
        unobserve() {}
        disconnect() {}
      };
    });
    
    console.log('Navigating to homepage...');
    await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle', timeout: 30000 });
    
    console.log('Injecting CSS to lock Hero height and remove footer...');
    await page.addStyleTag({ content: `
      /* Lock hero height so it doesn't take up the full screenshot when fullPage is true */
      .ult-hero, .h-screen, .min-h-screen { 
          min-height: 800px !important; 
          height: 800px !important; 
          max-height: 800px !important; 
      }
      
      /* Hide the footer as requested by user */
      footer, .page-public-footer, .public-footer {
          display: none !important;
      }
    ` });
    
    console.log('Waiting for render and mocked animations to finish...');
    await page.waitForTimeout(2000);
    
    console.log('Taking FULL PAGE screenshot...');
    await page.screenshot({ 
        path: 'C:/laragon/www/ult-fkip-unila/docs/skripsi/rancangan_diagram/screenshots/01-beranda-sistem.jpg', 
        type: 'jpeg', 
        quality: 90,
        fullPage: true
    });
    console.log('Screenshot saved!');
  } catch(e) {
    console.error('Fatal error:', e);
  } finally {
    await browser.close();
  }
})();
