const { chromium } = require('playwright');
async function test() {
    const browser = await chromium.launch();
    const page = await browser.newPage();
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[name="email"]', 'superadmin@demo.test');
    await page.fill('input[name="password"]', 'password');
    await Promise.all([
        page.waitForNavigation(),
        page.click('button[type="submit"]')
    ]);
    console.log(await page.url());
    await page.screenshot({ path: 'test_login.png' });
    await browser.close();
}
test();
