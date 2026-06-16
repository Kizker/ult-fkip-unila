import playwright from '../../../../node_modules/playwright/index.js';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const { chromium } = playwright;

const baseUrl = process.env.CAPTURE_BASE_URL || 'http://127.0.0.1:8037';
const chromePath = process.env.CHROME_PATH || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const outputDir = fileURLToPath(new URL('../screenshots/', import.meta.url));
const sourceDir = fileURLToPath(new URL('./', import.meta.url));
const projectRoot = fileURLToPath(new URL('../../../../', import.meta.url));
const sampleAttachment = path.join(sourceDir, 'sample-lampiran-pra-penelitian.pdf');
const serviceSlug = 'surat-persetujuan-pra-penelitian-sFQuYM';
const logoDataUris = {
  unila: `data:image/png;base64,${fs.readFileSync(path.join(projectRoot, 'public/icons/unila.png')).toString('base64')}`,
  fkip: `data:image/png;base64,${fs.readFileSync(path.join(projectRoot, 'public/icons/logo.png')).toString('base64')}`,
};

fs.mkdirSync(outputDir, { recursive: true });

if (!fs.existsSync(sampleAttachment)) {
  throw new Error(`File lampiran contoh tidak ditemukan: ${sampleAttachment}`);
}

async function screenshot(page, name) {
  await stabilizeBrandLogos(page);
  await page.screenshot({ path: path.join(outputDir, name), fullPage: false });
  console.log(name);
}

async function setScroll(page, y) {
  await page.evaluate((scrollY) => {
    window.scrollTo(0, scrollY);
    document.documentElement.scrollTop = scrollY;
    document.body.scrollTop = scrollY;
  }, y);
  await page.waitForTimeout(350);
}

async function waitForImages(page) {
  await page.waitForLoadState('domcontentloaded').catch(() => null);
  await page.waitForTimeout(500);
  await page.waitForFunction(() => (
    Array.from(document.images)
      .filter((img) => {
        const rect = img.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0 && rect.bottom >= 0 && rect.top <= window.innerHeight;
      })
      .every((img) => img.complete && img.naturalWidth > 0)
  ), null, { timeout: 15000 }).catch(() => null);
}

async function stabilizeBrandLogos(page) {
  await page.evaluate((logos) => {
    document.querySelectorAll([
      '.public-brand',
      '.public-brand__logos',
      '.public-brand__mark',
      '.public-footer__brand',
      '.public-footer__logo',
      '.public-footer__logos',
      '.app-topbar__brand',
      '.app-topbar__logos',
      '.app-topbar__mark',
    ].join(',')).forEach((node) => {
      node.style.opacity = '1';
      node.style.visibility = 'visible';
      node.style.filter = 'none';
      node.style.transform = 'none';
    });
    const logoSelectors = [
      '.public-brand__logo',
      '.public-footer__logoimg',
      '.app-topbar__logo',
    ];
    document.querySelectorAll(logoSelectors.join(',')).forEach((img) => {
      if (img.className.includes('--unila')) img.src = logos.unila;
      if (img.className.includes('--fkip')) img.src = logos.fkip;
      img.loading = 'eager';
      img.decoding = 'sync';
      img.style.display = 'block';
      img.style.opacity = '1';
      img.style.visibility = 'visible';
      img.style.filter = 'none';
      img.style.transform = 'none';
      const size = img.className.includes('app-topbar') ? '28px' : '32px';
      img.style.width = size;
      img.style.height = size;
      img.style.objectFit = 'contain';
    });
  }, logoDataUris);
  await page.waitForFunction(() => (
    Array.from(document.querySelectorAll('.public-brand__logo, .public-footer__logoimg, .app-topbar__logo'))
      .filter((img) => {
        const rect = img.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0 && rect.bottom >= 0 && rect.top <= window.innerHeight;
      })
      .every((img) => img.complete && img.naturalWidth > 0)
  ), null, { timeout: 8000 }).catch(() => null);
}

async function addMobileFilePickerOverlay(page) {
  await page.evaluate(() => {
    document.querySelector('[data-codex-file-picker-overlay]')?.remove();
    const overlay = document.createElement('div');
    overlay.setAttribute('data-codex-file-picker-overlay', '1');
    overlay.innerHTML = `
      <div class="codex-file-backdrop"></div>
      <div class="codex-file-sheet">
        <div class="codex-file-handle"></div>
        <div class="codex-file-title">Pilih lampiran</div>
        <div class="codex-file-subtitle">Pilih dokumen pendukung sesuai persyaratan layanan.</div>
        <div class="codex-file-item is-active">
          <div class="codex-file-thumb">PDF</div>
          <div class="codex-file-copy">
            <div class="codex-file-name">sample-lampiran-pra-penelitian.pdf</div>
            <div class="codex-file-meta">Dokumen pendukung - 1 file</div>
          </div>
          <div class="codex-file-check">OK</div>
        </div>
        <div class="codex-file-item">
          <div class="codex-file-thumb muted">DOC</div>
          <div class="codex-file-copy">
            <div class="codex-file-name">surat-pengantar.docx</div>
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
      .codex-file-check { width: 34px; height: 30px; border-radius: 999px; display: grid; place-items: center; color: #fff; background: #7c3aed; font-weight: 900; font-size: 12px; }
      .codex-file-actions { display: flex; gap: 12px; margin-top: 12px; }
      .codex-file-actions button { flex: 1; border: 1px solid #e5e7eb; border-radius: 18px; min-height: 48px; background: #fff; font-weight: 800; color: #334155; }
      .codex-file-actions .primary { border-color: #7c3aed; background: #7c3aed; color: #fff; }
    `;
    overlay.appendChild(style);
    document.body.appendChild(overlay);
  });
  await page.waitForTimeout(250);
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

await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded', timeout: 60000 });
await page.fill('input[name="email"]', 'mahasiswa@demo.test');
await page.fill('input[name="password"]', 'Password!2345');
await Promise.all([
  page.waitForURL(/mahasiswa\/dashboard/, { waitUntil: 'domcontentloaded', timeout: 60000 }),
  page.click('button[type="submit"]'),
]);
await waitForImages(page);
await screenshot(page, '00-dashboard.png');

await page.goto(`${baseUrl}/layanan`, { waitUntil: 'domcontentloaded', timeout: 60000 });
await waitForImages(page);
await setScroll(page, 0);
await screenshot(page, '01-layanan-index-top.png');
await page.fill('#services-q', 'Pra Penelitian');
await page.waitForTimeout(500);
await setScroll(page, 850);
await screenshot(page, '02-layanan-index-card.png');

await page.goto(`${baseUrl}/layanan/${serviceSlug}`, { waitUntil: 'domcontentloaded', timeout: 60000 });
await waitForImages(page);
await setScroll(page, 0);
await screenshot(page, '03-detail-layanan-top.png');
await setScroll(page, 980);
await screenshot(page, '04-detail-layanan-syarat.png');
await setScroll(page, 2600);
await screenshot(page, '04-detail-layanan-ajukan.png');

await page.goto(`${baseUrl}/mahasiswa/permohonan/buat/${serviceSlug}`, { waitUntil: 'domcontentloaded', timeout: 60000 });
await waitForImages(page);
await setScroll(page, 0);
await screenshot(page, '05-form-top-empty.png');

await page.fill('input[name="fields[42]"]', 'Kepala SMP Negeri 1 Bandar Lampung');
await screenshot(page, '06-form-field-instansi.png');
await page.fill('input[name="fields[43]"]', 'Kepala Sekolah');
await screenshot(page, '07-form-field-jabatan.png');
await page.fill('input[name="fields[44]"]', 'Bandar Lampung');
await screenshot(page, '08-form-field-kota.png');
await page.fill('input[name="fields[46]"]', '7');
await screenshot(page, '09-form-field-semester.png');

await setScroll(page, 620);
await screenshot(page, '10-form-attachment-empty.png');
await addMobileFilePickerOverlay(page);
await screenshot(page, '11-form-attachment-picker.png');
await page.evaluate(() => document.querySelector('[data-codex-file-picker-overlay]')?.remove());
await page.setInputFiles('input[name="attachments[]"]', sampleAttachment);
await page.waitForTimeout(700);
await screenshot(page, '12-form-attachment-selected.png');

await setScroll(page, 900);
await screenshot(page, '13-form-submit.png');
await Promise.all([
  page.waitForURL(/mahasiswa\/permohonan/, { waitUntil: 'domcontentloaded', timeout: 60000 }),
  page.click('form.student-form button[type="submit"]'),
]);
await waitForImages(page);
await screenshot(page, '14-requests-success.png');

await browser.close();
