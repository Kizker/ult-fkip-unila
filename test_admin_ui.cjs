const { chromium } = require('playwright');
const fs = require('fs');

async function testScreenshots() {
    console.log('Launching browser...');
    const browser = await chromium.launch();
    const context = await browser.newContext({ viewport: { width: 1366, height: 768 } });
    const page = await context.newPage();
    
    // Login as admin
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[name="email"]', 'superadmin@demo.test');
    await page.fill('input[name="password"]', 'Password!2345');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle' }),
        page.click('button[type="submit"]')
    ]);

    await page.waitForTimeout(2000);
    await page.screenshot({ path: 'test_admin_dashboard.png' });

    // Go to requests
    await page.goto('http://127.0.0.1:8000/admin/permohonan/5');
    await page.waitForTimeout(2000);
    await page.screenshot({ path: 'test_admin_req5.png' });
    
    // Go to requests table
    await page.goto('http://127.0.0.1:8000/admin/permohonan');
    await page.waitForTimeout(2000);
    await page.screenshot({ path: 'test_admin_req_table.png' });
    
    await browser.close();
}

testScreenshots().catch(console.error);
