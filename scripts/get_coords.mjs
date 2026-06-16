import { chromium } from 'playwright';
import { execFile } from 'node:child_process';
import path from 'node:path';
import { promisify } from 'node:util';

const execFileAsync = promisify(execFile);
const rootDir = path.resolve('c:/laragon/www/ult-fkip-unila');
const baseUrl = process.env.BASE_URL || 'http://ult-fkip-unila.test';
const email = 'mahasiswa@demo.test';
const password = 'Password!2345';

async function seedData() {
  const { stdout } = await execFileAsync(
    'php',
    [path.join(rootDir, 'scripts', 'seed_video04_requests.php')],
    { cwd: rootDir },
  );
  return JSON.parse(String(stdout).trim());
}

async function getCoords(page, locator) {
  try {
    await locator.scrollIntoViewIfNeeded();
    await page.waitForTimeout(300);
    const box = await locator.boundingBox();
    if (!box) return null;
    return {
      x: Math.round(box.x * 2),
      y: Math.round(box.y * 2),
      w: Math.round(box.width * 2),
      h: Math.round(box.height * 2),
    };
  } catch (e) {
    console.log(`Failed to get coords: ${e.message}`);
    return null;
  }
}

async function main() {
  const requestIds = await seedData();
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({
    viewport: { width: 477, height: 960 },
    deviceScaleFactor: 2,
    isMobile: true,
  });

  await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await Promise.all([
    page.waitForURL(/\/mahasiswa\//),
    page.click('button[type="submit"]'),
  ]);

  const results = {};

  // 1. riwayatList tap
  await page.goto(`${baseUrl}/mahasiswa/permohonan`);
  results.riwayatCard = await getCoords(page, page.locator('.student-request-card').first());
  
  // 3. Revisi
  await page.goto(`${baseUrl}/mahasiswa/permohonan/${requestIds.reqPerbaikan}`);
  results.badgeStatus = await getCoords(page, page.locator('.student-request-hero__badge').first());
  results.btnPerbaiki = await getCoords(page, page.locator('button', { hasText: 'Kirim perbaikan' }).first());

  // Form edit
  await page.goto(`${baseUrl}/mahasiswa/permohonan/${requestIds.reqPerbaikan}/edit`);
  // Coba cari label atau area drop filepond
  results.filePickerBox = await getCoords(page, page.locator('.filepond--root').first());
  
  // Bottom form
  await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
  await page.waitForTimeout(500);
  results.btnKirimUlang = await getCoords(page, page.locator('button', { hasText: 'Simpan perubahan data' }).first());

  // 4. Selesai
  await page.goto(`${baseUrl}/mahasiswa/permohonan/${requestIds.reqSelesai}`);
  await page.locator('.student-data-actions').first().scrollIntoViewIfNeeded();
  await page.waitForTimeout(500);
  results.btnUnduh = await getCoords(page, page.locator('a:has-text("Buka preview"), a:has-text("Unduh")').first());

  console.log(JSON.stringify(results, null, 2));

  await browser.close();
}

main().catch(console.error);
