import { chromium } from 'playwright';
import { execFile } from 'node:child_process';
import fs from 'node:fs/promises';
import path from 'node:path';
import { promisify } from 'node:util';

const execFileAsync = promisify(execFile);

const rootDir = path.resolve('c:/laragon/www/ult-fkip-unila');
const outputDir = path.join(
  rootDir,
  'docs',
  'video tutorial',
  'assets',
  'video-01-pengenalan-sistem-layanan',
);
const baseUrl = process.env.BASE_URL || 'http://ult-fkip-unila.test';
const email = 'mahasiswa@demo.test';
const password = 'Password!2345';

const routes = {
  home: '/',
  services: '/layanan',
  serviceDetail: '/layanan/surat-persyaratan-wisuda-ToYJDI',
  register: '/register',
  login: '/login',
  dashboard: '/mahasiswa/dashboard',
  profile: '/profil',
  requestCreate: '/mahasiswa/permohonan/buat/surat-persyaratan-wisuda-ToYJDI',
  requests: '/mahasiswa/permohonan',
};

async function ensureDir(dir) {
  await fs.mkdir(dir, { recursive: true });
}

async function getCaptureRequestIds() {
  const { stdout } = await execFileAsync(
    'php',
    [path.join(rootDir, 'scripts', 'seed_buku_panduan_pemohon_capture_requests.php')],
    { cwd: rootDir },
  );

  return JSON.parse(String(stdout).trim());
}

async function waitForStable(page, selector) {
  await page.locator(selector).first().waitFor({ state: 'visible', timeout: 30000 });
  await page.waitForLoadState('networkidle', { timeout: 30000 }).catch(() => {});
  await page.waitForTimeout(500);
}

async function gotoAndWait(page, target, selector) {
  await page.goto(`${baseUrl}${target}`, { waitUntil: 'domcontentloaded', timeout: 60000 });
  await waitForStable(page, selector);
}

async function screenshotLocator(page, selector, outputName) {
  const locator = page.locator(selector).first();
  await locator.scrollIntoViewIfNeeded();
  await page.waitForTimeout(200);
  await locator.screenshot({
    path: path.join(outputDir, outputName),
    animations: 'disabled',
  });
}

async function screenshotFullPage(page, outputName) {
  await page.screenshot({
    path: path.join(outputDir, outputName),
    fullPage: true,
    animations: 'disabled',
  });
}

async function screenshotUnion(page, locators, outputName, padding = 24) {
  const boxes = [];

  for (const locator of locators) {
    await locator.scrollIntoViewIfNeeded();
    await page.waitForTimeout(150);
    const box = await locator.boundingBox();
    if (box) boxes.push(box);
  }

  if (boxes.length === 0) {
    throw new Error(`Clip tidak ditemukan untuk ${outputName}`);
  }

  const viewport = page.viewportSize() ?? { width: 1600, height: 2400 };
  const x = Math.max(0, Math.min(...boxes.map((box) => box.x)) - padding);
  const y = Math.max(0, Math.min(...boxes.map((box) => box.y)) - padding);
  const right = Math.max(...boxes.map((box) => box.x + box.width)) + padding;
  const bottom = Math.max(...boxes.map((box) => box.y + box.height)) + padding;
  const width = Math.min(viewport.width - x, right - x);
  const height = Math.max(240, bottom - y);

  await page.screenshot({
    path: path.join(outputDir, outputName),
    clip: {
      x: Math.floor(x),
      y: Math.floor(y),
      width: Math.ceil(width),
      height: Math.ceil(height),
    },
    captureBeyondViewport: true,
    animations: 'disabled',
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
  await page.waitForLoadState('networkidle', { timeout: 30000 }).catch(() => {});
  await page.waitForTimeout(700);
}

async function writeManifest(entries) {
  const lines = [
    '# Screenshot Video 01',
    '',
    'Daftar screenshot wajib untuk narasi `01-pengenalan-sistem-layanan-natural.md`.',
    '',
    '| No | Narasi / Tampilan | File |',
    '| --- | --- | --- |',
  ];

  for (const [index, entry] of entries.entries()) {
    lines.push(`| ${index + 1} | ${entry.label} | ${entry.file} |`);
  }

  lines.push('');
  lines.push(`Base URL: ${baseUrl}`);
  lines.push(`Dibuat: ${new Date().toISOString()}`);
  lines.push('');

  await fs.writeFile(path.join(outputDir, 'README.md'), lines.join('\n'), 'utf8');
}

async function main() {
  await ensureDir(outputDir);
  const captureRequests = await getCaptureRequestIds();
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({
    viewport: { width: 1600, height: 2400 },
    deviceScaleFactor: 2,
  });

  page.setDefaultTimeout(30000);

  const manifest = [
    {
      label: 'Halaman utama sistem layanan',
      file: '01-beranda-sistem.png',
      capture: async () => {
        await gotoAndWait(page, routes.home, '.ult-hero');
        await screenshotLocator(page, '.ult-hero', '01-beranda-sistem.png');
      },
    },
    {
      label: 'Daftar layanan yang tersedia',
      file: '02-daftar-layanan.png',
      capture: async () => {
        await gotoAndWait(page, routes.services, '#services-catalog-section');
        await screenshotUnion(
          page,
          [
            page.locator('.services-v2-resultbar').first(),
            page.locator('.services-catalog-grid').first(),
          ],
          '02-daftar-layanan.png',
          24,
        );
      },
    },
    {
      label: 'Detail layanan dengan deskripsi, persyaratan, dan petunjuk',
      file: '03-detail-layanan.png',
      capture: async () => {
        await gotoAndWait(page, routes.serviceDetail, '.service-show-shell');
        await screenshotLocator(page, '.service-show-shell', '03-detail-layanan.png');
      },
    },
    {
      label: 'Halaman daftar akun',
      file: '04-daftar-akun.png',
      capture: async () => {
        await gotoAndWait(page, routes.register, '.auth-card');
        await screenshotLocator(page, '.auth-card', '04-daftar-akun.png');
      },
    },
    {
      label: 'Halaman login',
      file: '05-login.png',
      capture: async () => {
        await gotoAndWait(page, routes.login, '.auth-card');
        await screenshotLocator(page, '.auth-card', '05-login.png');
      },
    },
    {
      label: 'Tampilan setelah login berhasil',
      file: '06-dashboard-pemohon.png',
      capture: async () => {
        await loginAsStudent(page);
        await gotoAndWait(page, routes.dashboard, '.page-student-dashboard');
        await screenshotLocator(page, '.page-student-dashboard', '06-dashboard-pemohon.png');
      },
    },
    {
      label: 'Halaman profil untuk melengkapi data pengguna',
      file: '07-profil-pengguna.png',
      capture: async () => {
        await gotoAndWait(page, routes.profile, '.page-profile-edit');
        await screenshotLocator(page, '.page-profile-edit', '07-profil-pengguna.png');
      },
    },
    {
      label: 'Formulir pengajuan layanan',
      file: '08-form-pengajuan.png',
      capture: async () => {
        await gotoAndWait(page, routes.requestCreate, '.student-create-form');
        await screenshotLocator(page, '.student-create-form', '08-form-pengajuan.png');
      },
    },
    {
      label: 'Menu daftar atau riwayat permohonan',
      file: '09-riwayat-permohonan.png',
      capture: async () => {
        await gotoAndWait(page, routes.requests, '.page-student-requests-index');
        await screenshotLocator(page, '.page-student-requests-index', '09-riwayat-permohonan.png');
      },
    },
    {
      label: 'Detail permohonan untuk memantau status',
      file: '10-status-permohonan.png',
      capture: async () => {
        await gotoAndWait(
          page,
          `/mahasiswa/permohonan/${captureRequests.detail_request_id}`,
          '.page-student-requests-show',
        );
        await screenshotUnion(
          page,
          [
            page.locator('.student-show-card--overview').first(),
            page.locator('.student-show-side').first(),
          ],
          '10-status-permohonan.png',
          24,
        );
      },
    },
    {
      label: 'Detail permohonan dengan catatan revisi atau perbaikan',
      file: '11-revisi-permohonan.png',
      capture: async () => {
        await gotoAndWait(
          page,
          `/mahasiswa/permohonan/${captureRequests.revision_request_id}`,
          '.page-student-requests-show',
        );
        await screenshotUnion(
          page,
          [
            page.locator('.student-show-card--overview').first(),
            page.locator('.student-show-side').first(),
          ],
          '11-revisi-permohonan.png',
          24,
        );
      },
    },
    {
      label: 'Halaman detail permohonan selesai dengan tombol unduh hasil',
      file: '12-unduh-hasil.png',
      capture: async () => {
        await gotoAndWait(page, '/mahasiswa/permohonan/14', '.page-student-requests-show');
        await screenshotUnion(
          page,
          [
            page.locator('.student-show-card').filter({ hasText: 'Data permohonan' }).first(),
            page.locator('.student-data-actions').first(),
          ],
          '12-unduh-hasil.png',
          24,
        );
      },
    },
  ];

  try {
    for (const item of manifest) {
      await item.capture();
    }
    await writeManifest(manifest);
  } finally {
    await browser.close();
  }
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
