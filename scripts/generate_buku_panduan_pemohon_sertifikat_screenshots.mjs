import { chromium } from 'playwright';
import fs from 'node:fs/promises';
import path from 'node:path';

const rootDir = path.resolve('c:/laragon/www/ult-fkip-unila');
const screenshotDir = path.join(rootDir, 'docs', 'buku-panduan', 'assets', 'screenshots');
const baseUrl = 'http://ult-fkip-unila.test';
const email = 'mahasiswa@demo.test';
const password = 'Password!2345';
const certificateCreatePath = '/mahasiswa/permohonan/buat/sertifikat-kegiatan-program-studi-7Un3eM';
const requestShowPath = '/mahasiswa/permohonan/17';
const requestCompletePath = '/mahasiswa/permohonan/14';
const sourcePptxPath = path.join(
  rootDir,
  'storage',
  'app',
  'private',
  'requests',
  '14',
  'input',
  'certificate_source_pptx_20260220_0001.pptx',
);
const signaturePath = path.join(
  rootDir,
  'storage',
  'app',
  'private',
  'requests',
  '14',
  'signatures',
  'signature_certificate_custom_1_20260220_0001.png',
);

async function ensureDir(dir) {
  await fs.mkdir(dir, { recursive: true });
}

async function login(page) {
  await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await Promise.all([
    page.waitForURL(/\/mahasiswa\//, { timeout: 30000 }),
    page.click('button[type="submit"]'),
  ]);
}

async function screenshotLocator(page, selector, outputName) {
  const locator = page.locator(selector).first();
  await locator.waitFor({ state: 'visible', timeout: 30000 });
  await locator.scrollIntoViewIfNeeded();
  await page.waitForTimeout(250);
  await locator.screenshot({ path: path.join(screenshotDir, outputName) });
}

async function screenshotBetween(page, topSelector, bottomSelector, outputName, padding = 20) {
  const top = await page.locator(topSelector).first().boundingBox();
  const bottom = await page.locator(bottomSelector).first().boundingBox();
  if (!top || !bottom) {
    throw new Error(`Cannot resolve clip for ${outputName}`);
  }

  const x = Math.max(0, Math.min(top.x, bottom.x) - padding);
  const y = Math.max(0, top.y - padding);
  const right = Math.max(top.x + top.width, bottom.x + bottom.width) + padding;
  const bottomY = bottom.y + bottom.height + padding;
  const viewport = page.viewportSize() ?? { width: 1440, height: 2200 };
  const width = Math.min(viewport.width - x, right - x);
  const height = Math.max(200, bottomY - y);

  await page.screenshot({
    path: path.join(screenshotDir, outputName),
    clip: {
      x: Math.floor(x),
      y: Math.floor(y),
      width: Math.ceil(width),
      height: Math.ceil(height),
    },
    captureBeyondViewport: true,
  });
}

async function prepareCreateForm(page) {
  await page.goto(`${baseUrl}${certificateCreatePath}`, { waitUntil: 'networkidle' });
  await page.locator('select[name="certificate_signers[0][type]"]').selectOption('custom', { force: true });
  await page.fill('input[name="certificate_signers[0][name]"]', 'Ketua Pelaksana');
  await page.fill('input[name="certificate_signers[0][id_number]"]', '198704122020121001');
  await page.fill('input[name="certificate_signers[0][jabatan]"]', 'Ketua Pelaksana');
  await page.setInputFiles('input[name="certificate_source_pptx"]', sourcePptxPath);
  await page.setInputFiles('input[name="certificate_signatures[0]"]', signaturePath);

  await page.getByRole('button', { name: 'Tambah signer' }).click();
  await page.locator('select[name="certificate_signers[1][type]"]').selectOption('internal', { force: true });
  await page.locator('select[name="certificate_signers[1][internal_user_id]"]').selectOption({ index: 1 }, { force: true });
  await page.waitForTimeout(500);
}

async function main() {
  await ensureDir(screenshotDir);

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({
    viewport: { width: 1440, height: 2200 },
    deviceScaleFactor: 1,
  });

  page.setDefaultTimeout(30000);

  try {
    await login(page);
    await prepareCreateForm(page);

    await screenshotBetween(
      page,
      '.student-create-form',
      '.cert-editor__source',
      '12-form-layanan-sertifikat-piagam.png',
      24,
    );
    await screenshotBetween(
      page,
      '.cert-editor__source',
      '.cert-editor__signer:nth-of-type(2)',
      '13-upload-pptx-dan-penyusunan-signer-sertifikat.png',
      24,
    );
    await screenshotLocator(
      page,
      '.cert-editor__guide',
      '14-panel-pedoman-dan-contoh-layout-sertifikat.png',
    );

    await page.goto(`${baseUrl}${requestShowPath}`, { waitUntil: 'networkidle' });
    await screenshotLocator(
      page,
      '.student-show-layout',
      '15-detail-permohonan-sertifikat-piagam.png',
    );

    await page.goto(`${baseUrl}${requestCompletePath}`, { waitUntil: 'networkidle' });
    await screenshotLocator(
      page,
      '.student-show-card--overview',
      '16-area-preview-dan-output-sertifikat-piagam.png',
    );
  } finally {
    await browser.close();
  }
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
