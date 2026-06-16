import playwright from '../../../../node_modules/playwright/index.js';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const { chromium } = playwright;

const baseUrl = process.env.CAPTURE_BASE_URL || 'http://127.0.0.1:8035';
const chromePath = process.env.CHROME_PATH || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const outputDir = fileURLToPath(new URL('../screenshots/', import.meta.url));
const sourceDir = fileURLToPath(new URL('./', import.meta.url));
const avatarPath = path.join(sourceDir, 'tutorial-avatar.png');

fs.mkdirSync(outputDir, { recursive: true });

async function setScroll(page, y) {
  await page.evaluate((scrollY) => {
    window.scrollTo(0, scrollY);
    document.documentElement.scrollTop = scrollY;
    document.body.scrollTop = scrollY;
  }, y);
  await page.waitForTimeout(350);
}

async function selectNative(page, selector, value, label) {
  await page.evaluate(({ selector, value, label }) => {
    const select = document.querySelector(selector);
    if (!select) return;
    if (!Array.from(select.options).some((option) => option.value === value)) {
      const option = document.createElement('option');
      option.value = value;
      option.textContent = label;
      select.appendChild(option);
    }
    select.value = value;
    select.dispatchEvent(new Event('input', { bubbles: true }));
    select.dispatchEvent(new Event('change', { bubbles: true }));

    const wrapper = select.closest('[data-scrollable-select], .space-y-1, div')?.parentElement ?? select.parentElement;
    const textCandidates = Array.from(document.querySelectorAll('button, [role="button"], .scrollable-select__trigger, .scrollable-select-trigger'));
    for (const node of textCandidates) {
      const box = node.getBoundingClientRect();
      const selectBox = select.getBoundingClientRect();
      const near = Math.abs(box.top - selectBox.top) < 90 && Math.abs(box.left - selectBox.left) < 140;
      if (near && node.textContent && node.textContent.trim() !== label) {
        node.childNodes.forEach((child) => {
          if (child.nodeType === Node.TEXT_NODE) child.textContent = label;
        });
      }
    }
  }, { selector, value, label });
}

async function screenshot(page, name) {
  await page.screenshot({
    path: path.join(outputDir, name),
    fullPage: false,
  });
  console.log(name);
}

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

await page.goto(`${baseUrl}/register`, { waitUntil: 'networkidle', timeout: 60000 });
await page.fill('input[name="name"]', 'Mahasiswa Tutorial');
await page.fill('input[name="email"]', 'mahasiswa.tutorial@example.test');
await selectNative(page, 'select[name="account_role"]', 'Mahasiswa', 'Mahasiswa');
await page.fill('input[name="student_number"]', '2313050001');
await selectNative(page, 'select[name="jurusan_id"]', '11', 'Ilmu Pendidikan');
await page.waitForTimeout(500);
await selectNative(page, 'select[name="prodi_id"]', '20', 'Bimbingan dan Konseling');
await page.setInputFiles('input[name="profile_photo"]', avatarPath);
await page.fill('input[name="password"]', 'Password!2345');
await page.fill('input[name="password_confirmation"]', 'Password!2345');

await setScroll(page, 0);
await screenshot(page, '12-register-filled-identity.png');
await setScroll(page, 440);
await screenshot(page, '13-register-filled-academic.png');
await setScroll(page, 760);
await screenshot(page, '14-register-filled-photo.png');
await setScroll(page, 1120);
await screenshot(page, '15-register-filled-password.png');

await page.goto(`${baseUrl}/login`, { waitUntil: 'networkidle', timeout: 60000 });
await page.fill('input[name="email"]', 'mahasiswa.tutorial@example.test');
await page.fill('input[name="password"]', 'Password!2345');
await setScroll(page, 0);
await screenshot(page, '16-login-filled.png');

await browser.close();
