const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage({ viewport: { width: 1366, height: 900 } });
    
    console.log('Navigating to Katalog Layanan...');
    await page.goto('http://127.0.0.1:8000/layanan', { waitUntil: 'networkidle' });
    
    console.log('On Katalog Layanan page!');
    
    // Intercept IntersectionObserver to instantly trigger all scroll-reveal animations just in case
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
    
    // Wait for Alpine/Tailwind rendering
    await page.waitForTimeout(2000);
    
    // Hide the hero section and the header to ONLY show the catalog
    await page.addStyleTag({ content: `
      .services-v2-hero, header, .navbar, .ult-navbar, [class*="sticky"] {
          display: none !important;
      }
      ::-webkit-scrollbar { display: none; }
      body { -ms-overflow-style: none; scrollbar-width: none; background-color: #f8fafc; }
      
      /* Give a bit of margin at the top so it doesn't touch the window edge */
      .page-services-index {
          padding-top: 40px !important;
      }
    ` });
    
    console.log('Waiting for render after CSS injection...');
    await page.waitForTimeout(1000);
    
    // Capture desktop viewport
    console.log('Taking viewport screenshot...');
    await page.screenshot({ 
        path: 'C:/laragon/www/ult-fkip-unila/docs/skripsi/rancangan_diagram/screenshots/02-daftar-layanan.jpg', 
        type: 'jpeg', 
        quality: 100,
        fullPage: false 
    });
    console.log('Saved 02-daftar-layanan.jpg successfully!');
    
  } catch(e) {
    console.error(e);
  } finally {
    await browser.close();
  }
})();
