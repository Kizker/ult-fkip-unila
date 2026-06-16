import { chromium } from 'playwright';
import { execFile } from 'node:child_process';
import fs from 'node:fs/promises';
import path from 'node:path';
import { promisify } from 'node:util';

const execFileAsync = promisify(execFile);

const rootDir = path.resolve('c:/laragon/www/ult-fkip-unila');
const screenshotDir = path.join(rootDir, 'docs', 'buku-panduan', 'assets', 'screenshots');
const baseUrl = 'http://ult-fkip-unila.test';
const email = 'mahasiswa@demo.test';
const password = 'Password!2345';

const routes = {
  register: '/register',
  login: '/login',
  dashboard: '/mahasiswa/dashboard',
  requests: '/mahasiswa/permohonan',
  requestsFiltered: '/mahasiswa/permohonan?status=DIAJUKAN&service_id=19',
  serviceDetail: '/layanan/surat-persetujuan-pra-penelitian-sFQuYM',
  createAttachment: '/mahasiswa/permohonan/buat/surat-persetujuan-pra-penelitian-sFQuYM',
  createSigner: '/mahasiswa/permohonan/buat/surat-persyaratan-wisuda-ToYJDI',
  createCertificate: '/mahasiswa/permohonan/buat/sertifikat-kegiatan-program-studi-7Un3eM',
  requestDraft: '/mahasiswa/permohonan/13',
  requestRevision: '/mahasiswa/permohonan/18',
  requestComplete: '/mahasiswa/permohonan/14',
};

const assets = {
  sourcePptx: path.join(
    rootDir,
    'storage',
    'app',
    'private',
    'requests',
    '14',
    'input',
    'certificate_source_pptx_20260220_0001.pptx',
  ),
  signature: path.join(
    rootDir,
    'storage',
    'app',
    'private',
    'requests',
    '14',
    'signatures',
    'signature_certificate_custom_1_20260220_0001.png',
  ),
};

async function ensureDir(dir) {
  await fs.mkdir(dir, { recursive: true });
}

async function runPhpScript(script, action) {
  await execFileAsync('php', [path.join(rootDir, script), action], {
    cwd: rootDir,
  });
}

async function getCaptureRequestIds() {
  const { stdout } = await execFileAsync('php', [path.join(rootDir, 'scripts', 'seed_buku_panduan_pemohon_capture_requests.php')], {
    cwd: rootDir,
  });

  return JSON.parse(String(stdout).trim());
}

async function waitForStable(page, selector) {
  await page.locator(selector).first().waitFor({ state: 'visible', timeout: 30000 });
  await page.waitForTimeout(400);
}

async function gotoAndWait(page, target, selector) {
  await page.goto(`${baseUrl}${target}`, { waitUntil: 'domcontentloaded', timeout: 60000 });
  await waitForStable(page, selector);
}

async function screenshotLocator(page, selector, outputName) {
  const locator = page.locator(selector).first();
  await locator.scrollIntoViewIfNeeded();
  await page.waitForTimeout(250);
  await locator.screenshot({ path: path.join(screenshotDir, outputName) });
}

async function screenshotUnion(page, locators, outputName, padding = 20) {
  const boxes = [];

  for (const locator of locators) {
    await locator.scrollIntoViewIfNeeded();
    await page.waitForTimeout(150);
    const box = await locator.boundingBox();
    if (box) {
      boxes.push(box);
    }
  }

  if (boxes.length === 0) {
    throw new Error(`Gagal menentukan union clip untuk ${outputName}`);
  }

  const viewport = page.viewportSize() ?? { width: 1440, height: 2200 };
  const x = Math.max(0, Math.min(...boxes.map((box) => box.x)) - padding);
  const y = Math.max(0, Math.min(...boxes.map((box) => box.y)) - padding);
  const right = Math.max(...boxes.map((box) => box.x + box.width)) + padding;
  const bottom = Math.max(...boxes.map((box) => box.y + box.height)) + padding;
  const width = Math.min(viewport.width - x, right - x);
  const height = Math.max(240, bottom - y);

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

async function screenshotBetween(page, topLocator, bottomLocator, outputName, padding = 20) {
  const top = await topLocator.boundingBox();
  const bottom = await bottomLocator.boundingBox();

  if (!top || !bottom) {
    throw new Error(`Gagal menentukan clip untuk ${outputName}`);
  }

  const x = Math.max(0, Math.min(top.x, bottom.x) - padding);
  const y = Math.max(0, top.y - padding);
  const right = Math.max(top.x + top.width, bottom.x + bottom.width) + padding;
  const bottomY = bottom.y + bottom.height + padding;
  const viewport = page.viewportSize() ?? { width: 1440, height: 2200 };
  const width = Math.min(viewport.width - x, right - x);
  const height = Math.max(240, bottomY - y);

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

async function loginAsStudent(page) {
  await gotoAndWait(page, routes.login, '.auth-card');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await Promise.all([
    page.waitForURL(/\/mahasiswa\//, { timeout: 30000 }),
    page.click('button[type="submit"]'),
  ]);
  await page.waitForTimeout(500);
}

async function prepareSignerCreateForm(page) {
  await gotoAndWait(page, routes.createSigner, '.student-create-form');
  await page.locator('select[name="dosen_signers[2]"]').selectOption({ index: 1 }, { force: true });
  await page.waitForTimeout(400);
}

async function prepareCertificateCreateForm(page) {
  await gotoAndWait(page, routes.createCertificate, '.student-create-form');
  await page.locator('input[name="certificate_source_pptx"]').setInputFiles(assets.sourcePptx);
  await page.locator('select[name="certificate_signers[0][type]"]').selectOption('custom', { force: true });
  await page.fill('input[name="certificate_signers[0][name]"]', 'Ketua Pelaksana');
  await page.fill('input[name="certificate_signers[0][id_number]"]', '198704122020121001');
  await page.fill('input[name="certificate_signers[0][jabatan]"]', 'Ketua Pelaksana');
  await page.locator('input[name="certificate_signatures[0]"]').setInputFiles(assets.signature);
  await page.getByRole('button', { name: 'Tambah signer' }).click();
  await page.locator('select[name="certificate_signers[1][type]"]').selectOption('internal', { force: true });
  await page.locator('select[name="certificate_signers[1][internal_user_id]"]').selectOption({ index: 1 }, { force: true });
  await page.waitForTimeout(600);
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
    const captureRequests = await getCaptureRequestIds();

    await gotoAndWait(page, routes.register, '.auth-card');
    await screenshotLocator(page, '.auth-card', '01-register-akun-pemohon.png');

    await gotoAndWait(page, routes.login, '.auth-card');
    await screenshotLocator(page, '.auth-card', '02-halaman-login-pemohon.png');

    await loginAsStudent(page);

    await gotoAndWait(page, routes.dashboard, '.page-student-dashboard');
    await screenshotLocator(page, '.page-student-dashboard', '03-dashboard-pemohon.png');
    await screenshotLocator(page, '.student-dashboard-side', '04-ringkasan-dashboard-pemohon.png');

    await gotoAndWait(page, routes.requests, '.page-student-requests-index');
    await screenshotLocator(page, '.student-requests-grid', '05-daftar-permohonan-pemohon.png');

    await page.locator('.student-requests-filter-menu summary').click();
    await page.waitForTimeout(350);
    await screenshotUnion(
      page,
      [
        page.locator('.student-filter-card').first(),
        page.locator('.student-resultbar').first(),
      ],
      '06-filter-dan-pencarian-permohonan.png',
      24,
    );

    await gotoAndWait(page, routes.serviceDetail, '.service-show-shell');
    await screenshotLocator(page, '.service-show-shell', '07-detail-layanan-publik.png');
    await screenshotLocator(page, '.content-grid', '08-persyaratan-dan-sop-layanan.png');

    await gotoAndWait(page, routes.createAttachment, '.student-create-form');
    await screenshotLocator(page, '.student-create-form', '09-form-pengajuan-layanan-biasa.png');
    await screenshotUnion(
      page,
      [
        page.locator('.student-create-subsection').filter({ hasText: 'Lampiran umum' }).first(),
        page.locator('.student-form-actions').first(),
      ],
      '11-form-pengajuan-dengan-lampiran-umum.png',
      24,
    );

    await prepareSignerCreateForm(page);
    const signerTop = page.locator('.student-create-subsection').filter({ hasText: 'Tanda tangan pemohon' }).first();
    const signerBottom = page.locator('.student-create-subsection').filter({ hasText: 'Pemilihan dosen penandatangan' }).first();
    await screenshotBetween(page, signerTop, signerBottom, '10-form-pengajuan-dengan-signer.png', 24);

    await prepareCertificateCreateForm(page);
    await screenshotBetween(
      page,
      page.locator('.student-create-form').first(),
      page.locator('.cert-editor__source').first(),
      '12-form-sertifikat-piagam.png',
      24,
    );
    await screenshotBetween(
      page,
      page.locator('.cert-editor__source').first(),
      page.locator('.cert-editor__signer').nth(1),
      '13-upload-pptx-dan-signer-sertifikat.png',
      24,
    );
    await screenshotLocator(page, '.cert-editor__guide', '14-panel-pedoman-sertifikat-piagam.png');

    await gotoAndWait(page, `/mahasiswa/permohonan/${captureRequests.detail_request_id}`, '.page-student-requests-show');
    await screenshotLocator(page, '.student-show-layout', '15-detail-permohonan-setelah-submit.png');

    await gotoAndWait(page, routes.requestComplete, '.page-student-requests-show');
    await screenshotLocator(page, '.student-show-card--overview', '16-status-permohonan.png');
    await screenshotLocator(page, '.student-show-side', '20-riwayat-status-permohonan.png');

    await gotoAndWait(page, `/mahasiswa/permohonan/${captureRequests.revision_request_id}`, '.page-student-requests-show');
    await screenshotLocator(page, '.student-show-card--overview', '17-form-perbaikan-permohonan.png');
    await screenshotBetween(
      page,
      page.locator('div.rounded-2xl').filter({ hasText: 'Tanda tangan pemohon' }).first(),
      page.locator('div.rounded-2xl').filter({ hasText: 'Pemilihan dosen penandatangan' }).first(),
      '18-revisi-signer-dan-tandatangan.png',
      24,
    );

    await gotoAndWait(page, routes.requestComplete, '.page-student-requests-show');
    await screenshotUnion(
      page,
      [
        page.locator('.student-card-header').filter({ hasText: 'Data permohonan' }).first(),
        page.locator('.student-data-actions').first(),
      ],
      '19-preview-dokumen-permohonan.png',
      24,
    );
    await screenshotUnion(
      page,
      [
        page.locator('.student-show-card').filter({ hasText: 'Data permohonan' }).first(),
        page.locator('.student-data-actions').first(),
      ],
      '20-output-layanan-unduh-berkas.png',
      24,
    );
  } finally {
    await browser.close();
  }
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
