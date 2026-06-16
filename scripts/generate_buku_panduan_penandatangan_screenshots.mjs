import { chromium } from 'playwright';
import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const rootDir = path.resolve(__dirname, '..');
const screenshotDir = path.join(rootDir, 'docs', 'buku-panduan', 'assets', 'screenshots');
const signerDir = path.join(rootDir, 'docs', 'figma', 'signer');
const loginSource = path.join(screenshotDir, '02-halaman-login-pemohon.png');

async function ensureDir(dir) {
  await fs.mkdir(dir, { recursive: true });
}

async function normalizeMirror(page) {
  await page.addStyleTag({
    content: `
      footer,
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

async function screenshotSelector(page, selector, outputName) {
  const locator = page.locator(selector).first();
  await locator.waitFor({ state: 'visible', timeout: 10000 });
  await locator.scrollIntoViewIfNeeded();
  await page.waitForTimeout(150);
  await locator.screenshot({ path: path.join(screenshotDir, outputName) });
}

async function screenshotUnion(page, locators, outputName, padding = 20) {
  const boxes = [];

  for (const locator of locators) {
    await locator.scrollIntoViewIfNeeded();
    await page.waitForTimeout(120);
    const box = await locator.boundingBox();
    if (box) {
      boxes.push(box);
    }
  }

  if (boxes.length === 0) {
    throw new Error(`Gagal menentukan clip untuk ${outputName}`);
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

async function openMirror(browser, name) {
  const page = await browser.newPage({
    viewport: { width: 1102, height: 1600 },
    deviceScaleFactor: 1,
  });

  const url = pathToFileURL(path.join(signerDir, name)).href;
  await page.goto(url, { waitUntil: 'load' });
  await normalizeMirror(page);
  await page.evaluate(() => window.scrollTo(0, 0));
  return page;
}

async function patchInboxText(page) {
  await page.evaluate(() => {
    const title = document.querySelector('.sgi-hero__title');
    if (title) {
      title.textContent = 'Signer Inbox';
    }
    const subtitle = document.querySelector('.sgi-hero__subtitle');
    if (subtitle) {
      subtitle.textContent = 'Permohonan yang menunggu keputusan Anda pada tahap proses saat ini.';
    }
  });
}

async function main() {
  await ensureDir(screenshotDir);
  await fs.copyFile(loginSource, path.join(screenshotDir, '01-halaman-login-penandatangan.png'));

  const browser = await chromium.launch({ headless: true });

  try {
    const inboxPage = await openMirror(browser, 'requests-inbox-mirror.html');
    await patchInboxText(inboxPage);
    const heroWrap = inboxPage.locator('.sgi-hero-wrap').first();
    const listWrap = inboxPage.locator('.sgi-list-wrap').first();
    await screenshotUnion(inboxPage, [heroWrap, listWrap], '02-signer-inbox-hero.png', 20);
    await screenshotSelector(inboxPage, '.sgi-list-wrap', '03-daftar-permohonan-signer.png');
    const inboxCards = inboxPage.locator('.sgi-card');
    const inboxCardCount = await inboxCards.count();
    const infoLocators = [];
    for (let i = 0; i < Math.min(2, inboxCardCount); i += 1) {
      infoLocators.push(inboxCards.nth(i));
    }
    await screenshotUnion(inboxPage, infoLocators, '04-informasi-daftar-permohonan-signer.png', 20);
    await screenshotSelector(inboxPage, '.sgi-list-wrap', '10-inbox-setelah-keputusan-signer.png');
    await inboxPage.close();

    const showPage = await openMirror(browser, 'requests-show-mirror.html');
    const ringkasanCard = showPage.locator('div.rounded-2xl').filter({ hasText: 'Ringkasan Permohonan' }).first();
    const keputusanCard = showPage.locator('div.rounded-2xl').filter({ hasText: 'Keputusan Signer' }).first();
    const snapshotCard = showPage.locator('div.rounded-2xl').filter({ hasText: 'Data Snapshot' }).first();
    const previewButton = showPage.getByRole('link', { name: 'Preview Dokumen' }).first();

    await screenshotSelector(showPage, 'div.rounded-2xl:has(.ars-card-title)', '05-ringkasan-permohonan-signer.png');
    await screenshotUnion(showPage, [previewButton, snapshotCard], '06-preview-dokumen-dan-snapshot-signer.png', 24);
    await screenshotSelector(showPage, 'div.rounded-2xl:has(.ars-card-title)', '07-status-permohonan-signer.png');
    await keputusanCard.screenshot({ path: path.join(screenshotDir, '08-form-keputusan-signer.png') });
    await keputusanCard.screenshot({ path: path.join(screenshotDir, '09-form-keputusan-dengan-signature-file.png') });
    await showPage.evaluate(() => {
      const select = document.querySelector('select[name="decision"]');
      if (select) {
        select.value = 'REVISION';
        select.dispatchEvent(new Event('change', { bubbles: true }));
      }

      const textarea = document.querySelector('textarea[name="note"]');
      if (textarea) {
        textarea.value = 'Mohon perbaiki bagian identitas dan lampiran pendukung sebelum dokumen dilanjutkan ke tahap berikutnya.';
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
        textarea.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
    await screenshotUnion(showPage, [ringkasanCard, keputusanCard], '11-status-atau-dampak-keputusan-signer.png', 24);
    await showPage.close();
  } finally {
    await browser.close();
  }
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
