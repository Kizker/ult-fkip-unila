import playwright from '../../../../node_modules/playwright/index.js';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const { chromium } = playwright;

const baseUrl = process.env.CAPTURE_BASE_URL || 'http://127.0.0.1:8032';
const chromePath = process.env.CHROME_PATH || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const outputDir = fileURLToPath(new URL('../screenshots/', import.meta.url));

const shots = [
  { name: '01-register-top.png', path: '/register', scrollY: 0 },
  { name: '02-register-academic.png', path: '/register', scrollY: 440 },
  { name: '03-register-photo.png', path: '/register', scrollY: 760 },
  { name: '04-register-password.png', path: '/register', scrollY: 1120 },
  { name: '06-login.png', path: '/login', scrollY: 0 },
];

const browser = await chromium.launch({
  headless: true,
  executablePath: chromePath,
});

const page = await browser.newPage({
  viewport: { width: 477, height: 960 },
  deviceScaleFactor: 2,
  isMobile: true,
  hasTouch: true,
});

for (const shot of shots) {
  const url = `${baseUrl}${shot.path}`;
  await page.goto(url, { waitUntil: 'networkidle', timeout: 60000 });
  await page.evaluate((y) => {
    window.scrollTo(0, y);
    document.documentElement.scrollTop = y;
    document.body.scrollTop = y;
  }, shot.scrollY);
  await page.waitForTimeout(500);
  await page.screenshot({
    path: path.join(outputDir, shot.name),
    fullPage: false,
  });
  console.log(`${shot.name} <- ${url} @ scrollY=${shot.scrollY}`);
}

await browser.close();
