import playwright from '../../../../node_modules/playwright/index.js';
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const { chromium } = playwright;

const baseUrl = process.env.CAPTURE_BASE_URL || 'http://127.0.0.1:8033';
const chromePath = process.env.CHROME_PATH || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const mysqlPath = process.env.MYSQL_PATH || 'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysql.exe';
const outputDir = fileURLToPath(new URL('../screenshots/', import.meta.url));
const sourceDir = fileURLToPath(new URL('./', import.meta.url));

fs.mkdirSync(outputDir, { recursive: true });

const avatarPath = path.join(sourceDir, 'tutorial-avatar.png');
if (!fs.existsSync(avatarPath)) {
  throw new Error(`Aset foto profil tidak ditemukan: ${avatarPath}`);
}

const timestamp = Date.now();
const email = 'mahasiswa.tutorial@example.test';
const password = 'Password!2345';
execFileSync(mysqlPath, [
  '-uroot',
  'ult_fkip',
  '-e',
  `delete from users where email = '${email}' or student_number = '2313050001';`,
]);

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

async function waitForImageAssets(page) {
  await page.waitForLoadState('networkidle');
  await page.waitForFunction(() => (
    Array.from(document.querySelectorAll([
      '.public-brand__logo',
      '.public-footer__logoimg',
      '.app-topbar__logo',
      '.app-footer__logo',
    ].join(','))).filter((img) => {
      const rect = img.getBoundingClientRect();
      return rect.bottom > 0 && rect.top < window.innerHeight;
    }).every((img) => img.complete && img.naturalWidth > 0)
  ), null, { timeout: 15000 });
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
await page.fill('input[name="email"]', email);
await page.evaluate(() => {
  const select = document.querySelector('select[name="account_role"]');
  if (!select) return;
  select.value = 'Mahasiswa';
  select.dispatchEvent(new Event('change', { bubbles: true }));
});
await page.fill('input[name="student_number"]', '2313050001');
await page.evaluate(() => {
  const select = document.querySelector('select[name="jurusan_id"]');
  if (!select) return;
  select.value = '11';
  select.dispatchEvent(new Event('change', { bubbles: true }));
});
await page.waitForTimeout(500);
await page.evaluate(() => {
  const select = document.querySelector('select[name="prodi_id"]');
  if (!select) return;
  if (!Array.from(select.options).some((option) => option.value === '20')) {
    const option = document.createElement('option');
    option.value = '20';
    option.textContent = 'Bimbingan dan Konseling';
    select.appendChild(option);
  }
  select.value = '20';
  select.dispatchEvent(new Event('change', { bubbles: true }));
});
await page.setInputFiles('input[name="profile_photo"]', avatarPath);
await page.fill('input[name="password"]', password);
await page.fill('input[name="password_confirmation"]', password);
await page.click('button[type="submit"]');
await page.waitForURL(/email\/verify/, { timeout: 60000 });
await waitForImageAssets(page);
await page.screenshot({
  path: path.join(outputDir, '05-verify-email.png'),
  fullPage: false,
});
console.log(`05-verify-email.png <- ${page.url()}`);

execFileSync(mysqlPath, [
  '-uroot',
  'ult_fkip',
  '-e',
  `update users set email_verified_at = now() where email = '${email}';`,
]);

await page.goto(`${baseUrl}/mahasiswa/dashboard`, { waitUntil: 'networkidle', timeout: 60000 });
await waitForImageAssets(page);
await page.screenshot({
  path: path.join(outputDir, '07-dashboard-pemohon.png'),
  fullPage: false,
});
console.log(`07-dashboard-pemohon.png <- ${page.url()}`);

const profileTrigger = page.locator('.app-topbar__profile-trigger').first();
if (await profileTrigger.count()) {
  await profileTrigger.click();
  await page.waitForTimeout(500);
}
await waitForImageAssets(page);
await page.screenshot({
  path: path.join(outputDir, '08-menu-pengaturan.png'),
  fullPage: false,
});
console.log(`08-menu-pengaturan.png <- ${page.url()}`);

await page.goto(`${baseUrl}/profil`, { waitUntil: 'networkidle', timeout: 60000 });
await page.evaluate(() => {
  window.scrollTo(0, 0);
  document.documentElement.scrollTop = 0;
  document.body.scrollTop = 0;
});
await page.waitForTimeout(500);
await waitForImageAssets(page);
await page.screenshot({
  path: path.join(outputDir, '09-profil-top.png'),
  fullPage: false,
});
console.log(`09-profil-top.png <- ${page.url()} @ scrollY=0`);

await page.evaluate(() => {
  window.scrollTo(0, 420);
  document.documentElement.scrollTop = 420;
  document.body.scrollTop = 420;
});
await page.waitForTimeout(500);
await waitForImageAssets(page);
await page.screenshot({
  path: path.join(outputDir, '10-profil-photo.png'),
  fullPage: false,
});
console.log(`10-profil-photo.png <- ${page.url()} @ scrollY=520`);

await addMobileFilePickerOverlay(page);
await waitForImageAssets(page);
await page.screenshot({
  path: path.join(outputDir, '36-profil-photo-file-picker.png'),
  fullPage: false,
});
console.log(`36-profil-photo-file-picker.png <- ${page.url()} @ scrollY=520`);

await page.evaluate(() => document.querySelector('[data-codex-file-picker-overlay]')?.remove());
await page.setInputFiles('input[name="profile_photo"]', avatarPath);
await page.waitForTimeout(500);
await waitForImageAssets(page);
await page.screenshot({
  path: path.join(outputDir, '37-profil-photo-selected.png'),
  fullPage: false,
});
console.log(`37-profil-photo-selected.png <- ${page.url()} @ scrollY=520`);

await page.evaluate(() => {
  window.scrollTo(0, 840);
  document.documentElement.scrollTop = 840;
  document.body.scrollTop = 840;
});
await page.waitForTimeout(500);
await waitForImageAssets(page);
await page.screenshot({
  path: path.join(outputDir, '11-profil-save.png'),
  fullPage: false,
});
console.log(`11-profil-save.png <- ${page.url()} @ scrollY=1120`);

await browser.close();
