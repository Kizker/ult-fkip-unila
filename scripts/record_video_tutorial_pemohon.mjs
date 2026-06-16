import { chromium } from 'playwright';
import fs from 'node:fs/promises';
import path from 'node:path';
import { execFile } from 'node:child_process';
import { promisify } from 'node:util';

const rootDir = path.resolve('c:/laragon/www/ult-fkip-unila');
const outputDir = path.join(rootDir, 'docs', 'video tutorial', 'footage');
const baseUrl = process.env.BASE_URL || 'http://ult-fkip-unila.test';
const studentEmail = 'mahasiswa@demo.test';
const studentPassword = 'Password!2345';
const ffmpegPath = path.join(
  process.env.LOCALAPPDATA || '',
  'Microsoft',
  'WinGet',
  'Packages',
  'Gyan.FFmpeg.Essentials_Microsoft.Winget.Source_8wekyb3d8bbwe',
  'ffmpeg-8.1-essentials_build',
  'bin',
  'ffmpeg.exe',
);
const execFileAsync = promisify(execFile);

const videoNo = process.argv[2] || '1';

const scenarios = {
  '1': {
    fileName: 'video-01-pengenalan-sistem-layanan',
    title: 'Pengenalan Sistem Layanan',
    run: recordVideo1,
  },
};

async function ensureDir(dir) {
  await fs.mkdir(dir, { recursive: true });
}

async function getLatestRecordedVideo(dir) {
  const entries = await fs.readdir(dir, { withFileTypes: true });
  const files = [];
  for (const entry of entries) {
    if (!entry.isFile() || !entry.name.endsWith('.webm')) continue;
    const fullPath = path.join(dir, entry.name);
    const stat = await fs.stat(fullPath);
    files.push({ fullPath, mtimeMs: stat.mtimeMs });
  }
  files.sort((a, b) => b.mtimeMs - a.mtimeMs);
  return files[0]?.fullPath ?? null;
}

async function cleanupTemp(dir) {
  await fs.mkdir(dir, { recursive: true });
}

async function pause(page, ms = 1200) {
  await page.waitForTimeout(ms);
}

async function animateScrollTo(page, targetY, durationMs = 2800) {
  await page.evaluate(
    async ({ targetY: nextY, durationMs: nextDuration }) => {
      const startY = window.scrollY;
      const diff = nextY - startY;
      const easeInOut = (t) => (t < 0.5 ? 2 * t * t : 1 - ((-2 * t + 2) ** 2) / 2);

      await new Promise((resolve) => {
        const start = performance.now();
        const step = (now) => {
          const elapsed = now - start;
          const progress = Math.min(elapsed / nextDuration, 1);
          const eased = easeInOut(progress);
          window.scrollTo(0, startY + diff * eased);
          if (progress < 1) {
            requestAnimationFrame(step);
          } else {
            resolve();
          }
        };
        requestAnimationFrame(step);
      });
    },
    { targetY, durationMs },
  );
}

async function smoothScrollToTop(page) {
  await animateScrollTo(page, 0, 1800);
  await page.waitForTimeout(500);
}

async function getScrollTargets(page) {
  return page.evaluate(() => {
    const doc = document.documentElement;
    const maxScroll = Math.max(0, doc.scrollHeight - window.innerHeight);
    return {
      maxScroll,
      quarter: Math.round(maxScroll * 0.25),
      half: Math.round(maxScroll * 0.5),
      almostBottom: Math.round(maxScroll * 0.92),
    };
  });
}

async function showPageFlow(page, options = {}) {
  const {
    anchorSelector = 'main',
    introPause = 1400,
    showWholePage = true,
    previewStops = null,
    endPause = 900,
  } = options;

  await moveToLocator(page, anchorSelector, { waitMs: 450, steps: 28 });
  await pause(page, introPause);
  if (showWholePage) {
    const targets = await getScrollTargets(page);
    const sequence = (previewStops || [targets.quarter, targets.half, targets.almostBottom])
      .filter((value, index, arr) => value > 0 && arr.indexOf(value) === index);
    for (const point of sequence) {
      await animateScrollTo(page, point, 3000);
      await pause(page, 900);
    }
    await animateScrollTo(page, Math.max(0, targets.half - 180), 1800);
    await pause(page, 700);
  }
  await pause(page, endPause);
}

async function moveToLocator(page, selector, options = {}) {
  const locator = page.locator(selector).first();
  await locator.waitFor({ state: 'visible', timeout: 30000 });
  await locator.scrollIntoViewIfNeeded();
  const box = await locator.boundingBox();
  if (!box) return;
  const x = box.x + (options.offsetX ?? box.width / 2);
  const y = box.y + (options.offsetY ?? box.height / 2);
  await page.mouse.move(x, y, { steps: options.steps ?? 20 });
  await page.waitForTimeout(options.waitMs ?? 700);
}

async function clickAndWait(page, selector, waitMs = 1200) {
  await moveToLocator(page, selector, { waitMs: 300 });
  await page.locator(selector).first().click();
  await page.waitForTimeout(waitMs);
}

async function clickFirstMatchingLink(page, selector) {
  const locator = page.locator(selector).first();
  await locator.waitFor({ state: 'visible', timeout: 30000 });
  await locator.scrollIntoViewIfNeeded();
  await moveToLocator(page, selector, { waitMs: 300, steps: 24 });
  await Promise.all([
    page.waitForLoadState('domcontentloaded'),
    locator.click(),
  ]);
  await page.waitForLoadState('networkidle', { timeout: 60000 }).catch(() => {});
  await pause(page, 1200);
}

async function goto(page, target, waitUntil = 'domcontentloaded') {
  await page.goto(`${baseUrl}${target}`, { waitUntil, timeout: 60000 });
  await page.waitForLoadState('networkidle', { timeout: 60000 }).catch(() => {});
  await pause(page, 1200);
}

async function loginStudent(page) {
  await goto(page, '/login');
  await moveToLocator(page, 'input[name="email"]');
  await page.fill('input[name="email"]', studentEmail);
  await pause(page, 400);
  await moveToLocator(page, 'input[name="password"]');
  await page.fill('input[name="password"]', studentPassword);
  await pause(page, 500);
  await moveToLocator(page, 'button[type="submit"]', { waitMs: 300 });
  await Promise.all([
    page.waitForURL(/\/mahasiswa\//, { timeout: 30000 }),
    page.locator('button[type="submit"]').click(),
  ]);
  await pause(page, 1500);
}

async function recordVideo1(page) {
  await goto(page, '/');
  await showPageFlow(page, {
    anchorSelector: 'main',
    introPause: 1800,
    endPause: 1000,
  });

  await goto(page, '/layanan');
  await showPageFlow(page, {
    anchorSelector: '.services-catalog-card, .card-item, .service-card, main',
    introPause: 1600,
    endPause: 900,
  });

  await goto(page, '/layanan/surat-persetujuan-pra-penelitian-sFQuYM');
  await showPageFlow(page, {
    anchorSelector: '.service-show-shell, .content-grid, main',
    introPause: 1700,
    endPause: 900,
  });

  await goto(page, '/panduan-pengguna');
  await showPageFlow(page, {
    anchorSelector: '.guides-card-grid, main',
    introPause: 1500,
    downwardDistance: 1600,
    upwardDistance: 280,
    endPause: 800,
  });

  await smoothScrollToTop(page);
  await clickFirstMatchingLink(page, '.guides-card-grid a[href*="/panduan-pengguna/"]');
  await showPageFlow(page, {
    anchorSelector: 'main',
    introPause: 1400,
    endPause: 800,
  });

  await goto(page, '/login');
  await showPageFlow(page, {
    anchorSelector: '.auth-card, form',
    introPause: 1400,
    showWholePage: false,
    endPause: 700,
  });

  await goto(page, '/register');
  await showPageFlow(page, {
    anchorSelector: '.auth-card, form',
    introPause: 1500,
    showWholePage: false,
    endPause: 1200,
  });
}

async function convertToMp4(inputPath, outputPath) {
  await fs.rm(outputPath, { force: true });
  await execFileAsync(ffmpegPath, [
    '-y',
    '-i', inputPath,
    '-vf', 'minterpolate=fps=60:mi_mode=mci:mc_mode=aobmc:me_mode=bidir',
    '-c:v', 'libx264',
    '-preset', 'slow',
    '-crf', '20',
    '-pix_fmt', 'yuv420p',
    '-movflags', '+faststart',
    '-an',
    outputPath,
  ], {
    cwd: rootDir,
    windowsHide: true,
    timeout: 300000,
    maxBuffer: 10 * 1024 * 1024,
  });
}

async function main() {
  const scenario = scenarios[videoNo];
  if (!scenario) {
    throw new Error(`Video ${videoNo} belum didukung oleh script ini.`);
  }

  await ensureDir(outputDir);
  const tempVideoDir = path.join(outputDir, `_tmp_${Date.now()}`);
  await cleanupTemp(tempVideoDir);

  const browser = await chromium.launch({
    headless: true,
    args: ['--window-size=1440,900'],
  });

  const context = await browser.newContext({
    viewport: { width: 1440, height: 900 },
    recordVideo: {
      dir: tempVideoDir,
      size: { width: 1440, height: 900 },
    },
  });

  const page = await context.newPage();
  page.setDefaultTimeout(30000);

  try {
    await scenario.run(page);
  } finally {
    await context.close();
  }

  const webmPath = path.join(outputDir, `${scenario.fileName}.webm`);
  const mp4Path = path.join(outputDir, `${scenario.fileName}.mp4`);
  const tempVideoPath = await getLatestRecordedVideo(tempVideoDir);
  if (!tempVideoPath) {
    throw new Error('File video hasil rekaman tidak ditemukan di folder temporary.');
  }
  await fs.copyFile(tempVideoPath, webmPath);
  await convertToMp4(webmPath, mp4Path);

  console.log(`Saved webm: ${webmPath}`);
  console.log(`Saved mp4: ${mp4Path}`);
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
