const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  try {
    const page = await browser.newPage({ viewport: { width: 1366, height: 900 } });
    
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
    
    console.log('Navigating...');
    await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle', timeout: 30000 });
    
    console.log('Injecting CSS...');
    await page.addStyleTag({ content: `
      .ult-hero, .h-screen, .min-h-screen { 
          min-height: 800px !important; 
          height: 800px !important; 
          max-height: 800px !important; 
      }
      footer, .page-public-footer, .public-footer { display: none !important; }
      ::-webkit-scrollbar { display: none; }
      body { -ms-overflow-style: none; scrollbar-width: none; }
    ` });
    
    console.log('Waiting for render...');
    await page.waitForTimeout(2000);
    
    const dir = 'C:/laragon/www/ult-fkip-unila/docs/skripsi/rancangan_diagram/screenshots/';
    
    console.log('Taking part 1...');
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(500);
    await page.screenshot({ path: dir + 'part1.jpg', type: 'jpeg', quality: 100 });
    
    console.log('Hiding sticky header for subsequent parts...');
    await page.evaluate(() => {
        // Find the sticky header/navbar and hide it so it doesn't appear in the middle of the stitched image
        const headers = document.querySelectorAll('header, .navbar, .ult-navbar');
        headers.forEach(h => {
            const style = window.getComputedStyle(h);
            if (style.position === 'sticky' || style.position === 'fixed') {
                h.style.display = 'none';
            }
        });
    });
    await page.waitForTimeout(100);
    
    console.log('Taking part 2...');
    await page.evaluate(() => window.scrollTo(0, 900));
    await page.waitForTimeout(500);
    await page.screenshot({ path: dir + 'part2.jpg', type: 'jpeg', quality: 100 });
    
    console.log('Taking part 3...');
    await page.evaluate(() => window.scrollTo(0, 1800));
    await page.waitForTimeout(500);
    await page.screenshot({ path: dir + 'part3.jpg', type: 'jpeg', quality: 100 });
    
    const scrollHeight = await page.evaluate(() => document.body.scrollHeight);
    console.log('Total Scroll Height:', scrollHeight);
    
  } catch(e) {
    console.error(e);
  } finally {
    await browser.close();
  }
})();
