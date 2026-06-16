import { chromium } from 'playwright';
import fs from 'node:fs/promises';
import path from 'node:path';
import { spawnSync } from 'node:child_process';
import { fileURLToPath, pathToFileURL } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const rootDir = path.resolve(__dirname, '..');
const adminDir = path.join(rootDir, 'docs', 'figma', 'admin');
const screenshotDir = path.join(rootDir, 'docs', 'buku-panduan', 'assets', 'screenshots');
const unilaLogoUrl = pathToFileURL(path.join(rootDir, 'public', 'icons', 'unila.png')).href;
const fkipLogoUrl = pathToFileURL(path.join(rootDir, 'public', 'icons', 'logo.png')).href;
const liveBaseUrl = 'http://ult-fkip-unila.test';
const demoAdminEmail = 'superadmin@demo.test';
const demoAdminPassword = 'Password!2345';

const captures = [
  { input: 'users-index-mirror.html', output: '41-daftar-pengguna-admin.png' },
  { input: 'roles-index-mirror.html', output: '42-daftar-role-admin.png' },
  { input: 'layanan-index-mirror.html', output: '43-daftar-layanan-admin.png' },
  { input: 'user-guides-index-mirror.html', output: '44-daftar-panduan-pengguna-admin.png' },
  { input: 'letter-formats-index-mirror.html', output: '45-daftar-format-nomor-surat-admin.png' },
  { input: 'prodi-index-mirror.html', output: '46-daftar-program-studi-admin.png' },
  { input: 'cms-index-mirror.html', output: '47-manajemen-konten-admin.png' },
  { input: 'audit-index-mirror.html', output: '48-audit-aktivitas-admin.png' },
  { input: 'users-create-mirror.html', output: '49-form-tambah-pengguna-admin.png' },
  { input: 'roles-create-mirror.html', output: '50-form-tambah-role-admin.png' },
  { input: 'layanan-create-mirror.html', output: '51-form-tambah-layanan-admin.png' },
  { input: 'layanan-placeholder-guide-mirror.html', output: '52-pedoman-placeholder-layanan-dokumen.png' },
  { input: 'user-guides-create-mirror.html', output: '53-form-tambah-panduan-pengguna-admin.png' },
  { input: 'jurusan-create-mirror.html', output: '54-form-tambah-jurusan-admin.png' },
  { input: 'prodi-create-mirror.html', output: '55-form-tambah-program-studi-admin.png' },
  { input: path.join('cms', 'categories-create-mirror.html'), output: '56-form-tambah-kategori-cms-admin.png' },
  { input: path.join('cms', 'blogs-create-mirror.html'), output: '57-form-tambah-blog-admin.png' },
  { input: path.join('cms', 'announcements-create-mirror.html'), output: '58-form-tambah-pengumuman-admin.png' },
  { input: path.join('cms', 'hero-edit-mirror.html'), output: '59-pengaturan-hero-banner-admin.png' },
  { input: 'letter-formats-show-mirror.html', output: '60-detail-format-nomor-surat-admin.png' },
  { input: 'feedback-index-mirror.html', output: '61-daftar-kritik-dan-saran-admin.png' },
  { input: 'legalization-index-mirror.html', output: '62-modul-legalisasi-admin.png' },
  { input: 'layanan-show-mirror.html', output: '63-detail-layanan-admin.png' },
  { input: 'layanan-edit-mirror.html', output: '64-form-edit-layanan-admin.png' },
  { input: 'doc-formats-create-mirror.html', output: '65-form-tambah-format-dokumen-admin.png' },
  { input: 'doc-formats-edit-mirror.html', output: '66-form-edit-format-dokumen-admin.png' },
  { input: 'doc-formats-index-mirror.html', output: '67-daftar-setup-layanan-dokumen-admin.png' },
  { output: '68-ringkasan-setup-layanan-dokumen-admin.png', selector: '#doc-overview', livePath: '/admin/layanan/15' },
  { output: '69-upload-template-layanan-dokumen-admin.png', selector: '#doc-template', livePath: '/admin/layanan/15' },
  { output: '70-mapping-placeholder-layanan-dokumen-admin.png', selector: '#doc-placeholders', livePath: '/admin/layanan/15' },
  { output: '71-gate-layanan-dokumen-admin.png', selector: '#doc-gate', livePath: '/admin/layanan/15' },
  { output: '72-signer-layanan-dokumen-admin.png', selector: '#doc-signers', livePath: '/admin/layanan/15' },
  { output: '73-publish-readiness-layanan-dokumen-admin.png', selector: '#doc-readiness', livePath: '/admin/layanan/15' },
];

async function ensureDir(dir) {
  await fs.mkdir(dir, { recursive: true });
}

async function cropBottomWhitespace(page, outputPath) {
  const clip = await page.evaluate(() => {
    const root = document.scrollingElement || document.documentElement;
    const topbar = document.querySelector('.app-topbar');
    const content = document.querySelector('.app-shell__content');

    if (topbar && content) {
      const topbarRect = topbar.getBoundingClientRect();
      const contentRect = content.getBoundingClientRect();
      const meaningfulBottom = Array.from(content.querySelectorAll('*'))
        .map((el) => {
          const style = window.getComputedStyle(el);
          if (style.display === 'none' || style.visibility === 'hidden') {
            return null;
          }

          const rect = el.getBoundingClientRect();
          if (rect.width < 16 || rect.height < 16) {
            return null;
          }

          const hasText = (el.textContent || '').trim().length > 0;
          const isInteractive = ['A', 'BUTTON', 'INPUT', 'SELECT', 'TEXTAREA'].includes(el.tagName);
          const isMedia = ['IMG', 'SVG', 'TABLE'].includes(el.tagName);

          if (!hasText && !isInteractive && !isMedia) {
            return null;
          }

          return rect.bottom;
        })
        .filter(Boolean)
        .reduce((max, value) => Math.max(max, value), 0);

      const x = Math.min(topbarRect.left, contentRect.left);
      const y = Math.min(topbarRect.top, contentRect.top);
      const right = Math.max(topbarRect.right, contentRect.right);
      const bottom = Math.max(topbarRect.bottom, meaningfulBottom || contentRect.bottom);

      return {
        x: Math.max(0, Math.floor(x)),
        y: Math.max(0, Math.floor(y)),
        width: Math.ceil(right - x),
        height: Math.ceil(bottom - y + 28),
      };
    }

    const width = Math.min(root.scrollWidth, root.clientWidth || root.scrollWidth);
    const nonEmpty = Array.from(document.querySelectorAll('body *'))
      .map((el) => {
        const style = window.getComputedStyle(el);
        if (style.display === 'none' || style.visibility === 'hidden') {
          return null;
        }
        const rect = el.getBoundingClientRect();
        if (rect.width < 8 || rect.height < 8) {
          return null;
        }
        return rect.bottom + window.scrollY;
      })
      .filter(Boolean);
    const contentBottom = nonEmpty.reduce((max, item) => Math.max(max, item), 0);
    const height = Math.min(root.scrollHeight, Math.ceil(contentBottom + 24));

    return {
      x: 0,
      y: 0,
      width: Math.ceil(width),
      height: Math.max(200, height),
    };
  });

  await page.screenshot({
    path: outputPath,
    clip,
    captureBeyondViewport: true,
  });
}

async function screenshotSelector(page, selector, outputPath) {
  const locator = page.locator(selector).first();
  await locator.waitFor({ state: 'visible', timeout: 5000 });
  await locator.scrollIntoViewIfNeeded();
  await page.waitForTimeout(150);
  await locator.screenshot({
    path: outputPath,
  });
}

async function preparePage(page) {
  await page.setViewportSize({ width: 1102, height: 1600 });
}

async function normalizeAdminLayout(page) {
  await page.addStyleTag({
    content: `
      footer.public-footer,
      .public-footer,
      .page-public-footer,
      .site-footer {
        display: none !important;
      }

      .app-shell-main {
        min-height: auto !important;
        height: auto !important;
      }

      .app-shell__content {
        padding-bottom: 0 !important;
      }
    `,
  }).catch(() => {});
}

async function restoreBrandLogos(page) {
  await page.evaluate(
    ({ unilaSrc, fkipSrc }) => {
      const mappings = [
        {
          selector: 'img.app-topbar__logo--unila, img.public-footer__logoimg--unila',
          src: unilaSrc,
        },
        {
          selector: 'img.app-topbar__logo--fkip, img.public-footer__logoimg--fkip',
          src: fkipSrc,
        },
      ];

      for (const { selector, src } of mappings) {
        for (const img of document.querySelectorAll(selector)) {
          img.removeAttribute('loading');
          img.loading = 'eager';
          img.decoding = 'sync';
          img.setAttribute('src', src);
          img.src = src;
        }
      }
    },
    { unilaSrc: unilaLogoUrl, fkipSrc: fkipLogoUrl },
  );

  await page.evaluate(async () => {
    const logos = Array.from(
      document.querySelectorAll('img.app-topbar__logo, img.public-footer__logoimg'),
    );

    await Promise.all(
      logos.map(async (img) => {
        if (img.complete && img.naturalWidth > 0) {
          return;
        }

        if (typeof img.decode === 'function') {
          try {
            await img.decode();
            return;
          } catch {}
        }

        await new Promise((resolve) => {
          const done = () => resolve();
          img.addEventListener('load', done, { once: true });
          img.addEventListener('error', done, { once: true });
          setTimeout(done, 1200);
        });
      }),
    );
  });
}

async function loginAsDemoAdmin(page) {
  await page.goto(`${liveBaseUrl}/login`, { waitUntil: 'networkidle' });
  await page.fill('input[name="email"]', demoAdminEmail);
  await page.fill('input[name="password"]', demoAdminPassword);
  await Promise.all([
    page.waitForURL(/\/admin\//),
    page.click('button[type="submit"]'),
  ]);
}

async function main() {
  await ensureDir(screenshotDir);

  const browser = await chromium.launch({ headless: true });
  const livePage = await browser.newPage({
    viewport: { width: 1440, height: 1800 },
    deviceScaleFactor: 1,
  });
  await loginAsDemoAdmin(livePage);

  for (const capture of captures) {
    const target = path.join(screenshotDir, capture.output);
    if (capture.livePath) {
      await livePage.goto(`${liveBaseUrl}${capture.livePath}`, { waitUntil: 'networkidle' });
      await livePage.evaluate(() => window.scrollTo(0, 0));
      await screenshotSelector(livePage, capture.selector, target);
    } else {
      const page = await browser.newPage({
        viewport: { width: 1102, height: 1600 },
        deviceScaleFactor: 1,
      });
      const htmlPath = path.join(adminDir, capture.input);
      const url = pathToFileURL(htmlPath).href;

      await preparePage(page);
      await page.goto(url, { waitUntil: 'load' });
      await normalizeAdminLayout(page);
      await restoreBrandLogos(page);
      await page.evaluate(() => window.scrollTo(0, 0));
      if (capture.selector) {
        await screenshotSelector(page, capture.selector, target);
      } else {
        await cropBottomWhitespace(page, target);
      }
      await page.close();
    }
    console.log(`saved ${capture.output}`);
  }

  const cropScript = path.join(rootDir, 'scripts', 'crop_buku_panduan_admin_screenshots.py');
  const cropResult = spawnSync('python', [cropScript], {
    cwd: rootDir,
    encoding: 'utf-8',
  });

  if (cropResult.stdout) {
    process.stdout.write(cropResult.stdout);
  }

  if (cropResult.status !== 0) {
    throw new Error(cropResult.stderr || 'Gagal menjalankan crop screenshot admin.');
  }

  await livePage.close();
  await browser.close();
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
