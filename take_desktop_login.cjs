const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage({ viewport: { width: 1366, height: 900 } });
    
    console.log('Navigating to login...');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    
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
    
    console.log('Waiting for render...');
    await page.waitForTimeout(2000);
    
    // Force header/navbar to be visible (if it's using sticky or has animation issues)
    await page.addStyleTag({ content: `
      header, .navbar, .ult-navbar, [class*="sticky"] {
          opacity: 1 !important;
          visibility: visible !important;
          transform: none !important;
      }
      ::-webkit-scrollbar { display: none; }
      body { -ms-overflow-style: none; scrollbar-width: none; }
    ` });
    
    console.log('Taking viewport screenshot...');
    // We use fullPage: false so it acts like a true desktop monitor window (1366x900)
    await page.screenshot({ 
        path: 'C:/laragon/www/ult-fkip-unila/docs/skripsi/rancangan_diagram/screenshots/05-login.jpg', 
        type: 'jpeg', 
        quality: 100,
        fullPage: false 
    });
    
    console.log('Saved 05-login.jpg successfully!');
    
  } catch(e) {
    console.error(e);
  } finally {
    await browser.close();
  }
})();
