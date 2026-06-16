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
  await page.waitForTimeout(250);
}

async function screenshot(page, name) {
  await page.screenshot({ path: path.join(outputDir, name), fullPage: false });
  console.log(name);
}

async function clickTriggerByText(page, text, index = 0) {
  const loc = page.locator('button.user-scroll-select__trigger', { hasText: text }).nth(index);
  await loc.scrollIntoViewIfNeeded();
  await loc.click();
  await page.waitForTimeout(350);
}

async function chooseOption(page, text) {
  const option = page.locator('button.user-scroll-select__option', { hasText: text }).first();
  await option.click();
  await page.waitForTimeout(350);
}

async function addMobileFilePickerOverlay(page) {
  await page.evaluate(() => {
    const old = document.querySelector('[data-codex-file-picker-overlay]');
    if (old) old.remove();
    const overlay = document.createElement('div');
    overlay.setAttribute('data-codex-file-picker-overlay', '1');
    overlay.innerHTML = `
      <div class="codex-file-backdrop"></div>
      <div class="codex-file-sheet">
        <div class="codex-file-handle"></div>
        <div class="codex-file-title">Pilih foto profil</div>
        <div class="codex-file-subtitle">Pilih file gambar persegi dari perangkat.</div>
        <div class="codex-file-item is-active">
          <div class="codex-file-thumb">JPG</div>
          <div class="codex-file-copy">
            <div class="codex-file-name">tutorial-avatar.jpg</div>
            <div class="codex-file-meta">Gambar profil - 1:1</div>
          </div>
          <div class="codex-file-check">✓</div>
        </div>
        <div class="codex-file-item">
          <div class="codex-file-thumb muted">PNG</div>
          <div class="codex-file-copy">
            <div class="codex-file-name">foto-ktm.png</div>
            <div class="codex-file-meta">Contoh file lain</div>
          </div>
        </div>
        <div class="codex-file-actions">
          <button type="button">Batal</button>
          <button type="button" class="primary">Pilih</button>
        </div>
      </div>`;
    const style = document.createElement('style');
    style.textContent = `
      [data-codex-file-picker-overlay] { position: fixed; inset: 0; z-index: 99999; font-family: inherit; }
      .codex-file-backdrop { position: absolute; inset: 0; background: rgba(15, 23, 42, .38); }
      .codex-file-sheet { position: absolute; left: 24px; right: 24px; bottom: 28px; border-radius: 30px; background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 28px 70px rgba(15, 23, 42, .25); padding: 18px 18px 20px; }
      .codex-file-handle { width: 54px; height: 6px; border-radius: 999px; background: #d1d5db; margin: 2px auto 18px; }
      .codex-file-title { font-weight: 800; font-size: 22px; color: #111827; text-align: center; }
      .codex-file-subtitle { font-size: 14px; color: #64748b; text-align: center; margin: 6px 0 18px; }
      .codex-file-item { display: flex; align-items: center; gap: 14px; min-height: 72px; border: 1px solid #e5e7eb; border-radius: 20px; padding: 10px 12px; margin-bottom: 10px; background: #fff; }
      .codex-file-item.is-active { border-color: #8b5cf6; background: #f5f3ff; }
      .codex-file-thumb { width: 50px; height: 50px; border-radius: 16px; display: grid; place-items: center; background: #7c3aed; color: #fff; font-weight: 900; font-size: 13px; }
      .codex-file-thumb.muted { background: #94a3b8; }
      .codex-file-copy { min-width: 0; flex: 1; }
      .codex-file-name { color: #111827; font-size: 17px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
      .codex-file-meta { color: #64748b; font-size: 13px; margin-top: 2px; }
      .codex-file-check { width: 30px; height: 30px; border-radius: 999px; display: grid; place-items: center; color: #fff; background: #7c3aed; font-weight: 900; }
      .codex-file-actions { display: flex; gap: 12px; margin-top: 12px; }
      .codex-file-actions button { flex: 1; border: 1px solid #e5e7eb; border-radius: 18px; min-height: 48px; background: #fff; font-weight: 800; color: #334155; }
      .codex-file-actions .primary { border-color: #7c3aed; background: #7c3aed; color: #fff; }
    `;
    overlay.appendChild(style);
    document.body.appendChild(overlay);
  });
  await page.waitForTimeout(250);
}

const browser = await chromium.launch({ headless: true, executablePath: chromePath });
const page = await browser.newPage({
  viewport: { width: 477, height: 960 },
  deviceScaleFactor: 2,
  isMobile: true,
  hasTouch: true,
});

await page.goto(`${baseUrl}/register`, { waitUntil: 'networkidle', timeout: 60000 });

await setScroll(page, 0);
await screenshot(page, '17-register-step-empty.png');
await page.fill('input[name="name"]', 'Mahasiswa Tutorial');
await screenshot(page, '18-register-step-name.png');
await page.fill('input[name="email"]', 'mahasiswa.tutorial@example.test');
await screenshot(page, '19-register-step-email.png');

await clickTriggerByText(page, 'Mahasiswa');
await screenshot(page, '20-register-dropdown-jenis-akun-open.png');
await chooseOption(page, 'Mahasiswa');
await screenshot(page, '21-register-step-jenis-akun-selected.png');

await page.fill('input[name="student_number"]', '2313050001');
await screenshot(page, '22-register-step-npm.png');

await clickTriggerByText(page, 'Pilih jurusan');
await screenshot(page, '23-register-dropdown-jurusan-open.png');
await chooseOption(page, 'Ilmu Pendidikan');
await screenshot(page, '24-register-step-jurusan-selected.png');

await clickTriggerByText(page, 'Pilih program studi');
await screenshot(page, '25-register-dropdown-prodi-open.png');
await chooseOption(page, 'Bimbingan dan Konseling');
await screenshot(page, '26-register-step-prodi-selected.png');

await setScroll(page, 530);
await screenshot(page, '27-register-photo-before-pick.png');
await addMobileFilePickerOverlay(page);
await screenshot(page, '28-register-photo-file-picker.png');
await page.evaluate(() => document.querySelector('[data-codex-file-picker-overlay]')?.remove());
await page.setInputFiles('input[name="profile_photo"]', avatarPath);
await page.waitForFunction(() => {
  const img = document.querySelector('.auth-register-photo__preview img');
  return Boolean(img?.getAttribute('src')) && img.complete;
}, { timeout: 5000 }).catch(() => null);
await page.waitForTimeout(700);
await screenshot(page, '29-register-photo-selected.png');

await setScroll(page, 930);
await screenshot(page, '30-register-password-empty.png');
await page.fill('input[name="password"]', 'Password!2345');
await screenshot(page, '31-register-password-filled.png');
await page.fill('input[name="password_confirmation"]', 'Password!2345');
await screenshot(page, '32-register-confirm-password-filled.png');

await page.goto(`${baseUrl}/login`, { waitUntil: 'networkidle', timeout: 60000 });
await screenshot(page, '33-login-empty.png');
await page.fill('input[name="email"]', 'mahasiswa.tutorial@example.test');
await screenshot(page, '34-login-email-filled.png');
await page.fill('input[name="password"]', 'Password!2345');
await screenshot(page, '35-login-password-filled.png');

await browser.close();
