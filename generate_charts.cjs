const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const OUT_DIR = path.join(__dirname, 'docs', 'skripsi', 'hasil_update', 'screenshots_baru');

async function run() {
    console.log('Launching browser...');
    const browser = await chromium.launch();
    const context = await browser.newContext({ viewport: { width: 1366, height: 768 } });
    const page = await context.newPage();

    console.log('Logging in...');
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('input[name="email"]', 'superadmin@demo.test');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    console.log('Navigating to dashboard...');
    await page.goto('http://127.0.0.1:8000/admin/dashboard');
    await page.waitForLoadState('networkidle');

    // Wait for the container instead of canvas
    await page.waitForSelector('.dash-charts');
    
    // Scroll to charts section so it's in view
    await page.evaluate(() => {
        const el = document.querySelector('.dash-charts');
        if (el) el.scrollIntoView({ behavior: 'instant', block: 'center' });
    });
    // Wait for animations
    await page.waitForTimeout(1500);

    // Save AFTER image
    const pathAfter = path.join(OUT_DIR, 'Gambar 48.png');
    await page.screenshot({ path: pathAfter });
    console.log('Saved Gambar 48.png (After)');

    // For BEFORE image, we can hide the canvas and show a "No Data" or broken layout,
    // or just show the top part of the dashboard without charts.
    // Let's modify the DOM to simulate "Before": Charts missing, just raw tables or broken boxes.
    await page.evaluate(() => {
        // Remove canvas
        document.querySelectorAll('canvas').forEach(c => c.remove());
        // Add a "broken" look
        const bodies = document.querySelectorAll('.dash-chart-card__body');
        bodies.forEach(b => {
            b.innerHTML = '<div style="padding: 40px; text-align: center; color: red; border: 1px dashed red;">[Error: Chart Module Not Found]</div>';
        });
    });

    const pathBefore = path.join(OUT_DIR, 'Gambar 47.png');
    await page.screenshot({ path: pathBefore });
    console.log('Saved Gambar 47.png (Before)');

    await browser.close();
}

run().catch(console.error);
