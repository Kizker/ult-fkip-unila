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
  'ppt-mobile-video-04',
  'screenshots',
);
const baseUrl = process.env.BASE_URL || 'http://ult-fkip-unila.test';
const email = 'mahasiswa@demo.test';
const password = 'Password!2345';

const routes = {
  login: '/login',
  requests: '/mahasiswa/permohonan',
};

async function ensureDir(dir) {
  await fs.mkdir(dir, { recursive: true });
}

async function seedData() {
  const { stdout } = await execFileAsync(
    'php',
    [path.join(rootDir, 'scripts', 'seed_video04_requests.php')],
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

async function screenshotUnion(page, locators, outputName, padding = 24) {
  const boxes = [];
  for (const locator of locators) {
    await locator.scrollIntoViewIfNeeded();
    await page.waitForTimeout(150);
    const box = await locator.boundingBox();
    if (box) boxes.push(box);
  }
  if (boxes.length === 0) throw new Error(`Clip tidak ditemukan untuk ${outputName}`);

  const viewport = page.viewportSize() ?? { width: 1600, height: 2400 };
  const x = Math.max(0, Math.min(...boxes.map((box) => box.x)) - padding);
  const y = Math.max(0, Math.min(...boxes.map((box) => box.y)) - padding);
  const right = Math.max(...boxes.map((box) => box.x + box.width)) + padding;
  const bottom = Math.max(...boxes.map((box) => box.y + box.height)) + padding;
  const width = Math.min(viewport.width - x, right - x);
  const height = Math.max(240, bottom - y);

  await page.screenshot({
    path: path.join(outputDir, outputName),
    clip: { x: Math.floor(x), y: Math.floor(y), width: Math.ceil(width), height: Math.ceil(height) },
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

async function main() {
  await ensureDir(outputDir);
  const requestIds = await seedData();
  const browser = await chromium.launch({ headless: true });
  // Gunakan resolusi mobile portrait
  const page = await browser.newPage({
    viewport: { width: 477, height: 960 },
    deviceScaleFactor: 2, // Akan menghasilkan 954 x 1920
    isMobile: true,
  });

  page.setDefaultTimeout(30000);

  const manifest = [
    {
      label: 'Daftar Riwayat Permohonan (List)',
      file: '01-riwayat-permohonan-list.png',
      capture: async () => {
        await loginAsStudent(page);
        await gotoAndWait(page, routes.requests, '.page-student-requests-index');
        await page.screenshot({ path: path.join(outputDir, '01-riwayat-permohonan-list.png') });
      },
    },
    {
      label: 'Detail - Diajukan',
      file: '02-detail-diajukan.png',
      capture: async () => {
        await gotoAndWait(page, `${routes.requests}/${requestIds.reqDiajukan}`, '.page-student-requests-show');
        await page.screenshot({ path: path.join(outputDir, '02-detail-diajukan.png') });
      },
    },
    {
      label: 'Detail - Diverifikasi Unit',
      file: '03-detail-diverifikasi-unit.png',
      capture: async () => {
        await gotoAndWait(page, `${routes.requests}/${requestIds.reqDiverifikasi}`, '.page-student-requests-show');
        await page.screenshot({ path: path.join(outputDir, '03-detail-diverifikasi-unit.png') });
      },
    },
    {
      label: 'Detail - Review ULT',
      file: '04-detail-review-ult.png',
      capture: async () => {
        await gotoAndWait(page, `${routes.requests}/${requestIds.reqReview}`, '.page-student-requests-show');
        await page.screenshot({ path: path.join(outputDir, '04-detail-review-ult.png') });
      },
    },
    {
      label: 'Detail - Menunggu TTD',
      file: '05-detail-menunggu-ttd.png',
      capture: async () => {
        await gotoAndWait(page, `${routes.requests}/${requestIds.reqMenungguTTD}`, '.page-student-requests-show');
        await page.screenshot({ path: path.join(outputDir, '05-detail-menunggu-ttd.png') });
      },
    },
    {
      label: 'Detail - Nomor Terbit',
      file: '06-detail-nomor-terbit.png',
      capture: async () => {
        await gotoAndWait(page, `${routes.requests}/${requestIds.reqNomorTerbit}`, '.page-student-requests-show');
        await page.screenshot({ path: path.join(outputDir, '06-detail-nomor-terbit.png') });
      },
    },
    {
      label: 'Detail - Penandatanganan',
      file: '07-detail-penandatanganan.png',
      capture: async () => {
        await gotoAndWait(page, `${routes.requests}/${requestIds.reqPenandatanganan}`, '.page-student-requests-show');
        await page.screenshot({ path: path.join(outputDir, '07-detail-penandatanganan.png') });
      },
    },
    {
      label: 'Detail - Diproses',
      file: '08-detail-diproses.png',
      capture: async () => {
        await gotoAndWait(page, `${routes.requests}/${requestIds.reqDiproses}`, '.page-student-requests-show');
        await page.screenshot({ path: path.join(outputDir, '08-detail-diproses.png') });
      },
    },
    {
      label: 'Detail - Perlu Perbaikan',
      file: '09-detail-perlu-perbaikan.png',
      capture: async () => {
        await gotoAndWait(page, `${routes.requests}/${requestIds.reqPerbaikan}`, '.page-student-requests-show');
        await page.screenshot({ path: path.join(outputDir, '09-detail-perlu-perbaikan.png') });
      },
    },
    {
      label: 'Form Edit Revisi',
      file: '12-form-edit-revisi.png',
      capture: async () => {
        await gotoAndWait(page, `${routes.requests}/${requestIds.reqPerbaikan}`, '.page-student-requests-show');
        await page.locator('.student-show-card').filter({ hasText: 'Data permohonan' }).first().scrollIntoViewIfNeeded();
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(outputDir, '12-form-edit-revisi.png') });
      },
    },
    {
      label: 'Form Edit Revisi Filled',
      file: '12b-form-edit-revisi-filled.png',
      capture: async () => {
        const inputLocator = page.locator('input[type="text"]').first();
        if (await inputLocator.isVisible()) {
            await inputLocator.fill('Dinas Pendidikan Daerah');
            await page.keyboard.press('Tab');
            await page.waitForTimeout(300);
            await page.screenshot({ path: path.join(outputDir, '12b-form-edit-revisi-filled.png') });
        } else {
            await page.screenshot({ path: path.join(outputDir, '12b-form-edit-revisi-filled.png') });
        }
      },
    },
    {
      label: 'File Picker',
      file: '13-form-edit-revisi-filepicker.png',
      capture: async () => {
        const fsLib = await import('node:fs');
        const bgData = fsLib.readFileSync(path.join(outputDir, '12-form-edit-revisi.png')).toString('base64');
        const pickerData = fsLib.readFileSync(path.join(rootDir, 'docs', 'video tutorial', 'ppt-mobile-video-03', 'screenshots', '11-form-attachment-picker.png')).toString('base64');
        await page.setContent(`
          <div style="width: 477px; height: 960px; position: relative; overflow: hidden; margin: 0; padding: 0;">
            <img src="data:image/png;base64,${bgData}" style="width: 100%; height: 100%; object-fit: cover; display: block;" />
            <div style="position: absolute; top:0; left:0; width: 100%; height: 100%; background: rgba(0,0,0,0.4);"></div>
            <div style="position: absolute; top:0; left:0; width: 100%; height: 100%;">
                <img src="data:image/png;base64,${pickerData}" style="width: 100%; height: 100%; object-fit: cover; clip-path: inset(50% 0 0 0);" />
            </div>
          </div>
        `);
        await page.waitForTimeout(200);
        await page.screenshot({ path: path.join(outputDir, '13-form-edit-revisi-filepicker.png') });
      },
    },
    {
      label: 'Form Edit Bottom',
      file: '14-form-edit-revisi-bottom.png',
      capture: async () => {
        await gotoAndWait(page, `${routes.requests}/${requestIds.reqPerbaikan}`, '.page-student-requests-show');
        await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(outputDir, '14-form-edit-revisi-bottom.png') });
      },
    },
    {
      label: 'Detail - Ditolak',
      file: '10-detail-ditolak.png',
      capture: async () => {
        await gotoAndWait(page, `${routes.requests}/${requestIds.reqDitolak}`, '.page-student-requests-show');
        await page.screenshot({ path: path.join(outputDir, '10-detail-ditolak.png') });
      },
    },
    {
      label: 'Detail - Selesai',
      file: '11-detail-selesai.png',
      capture: async () => {
        await gotoAndWait(page, `${routes.requests}/${requestIds.reqSelesai}`, '.page-student-requests-show');
        await page.locator('.student-data-actions').first().scrollIntoViewIfNeeded();
        await page.waitForTimeout(500);
        await page.screenshot({ path: path.join(outputDir, '11-detail-selesai.png') });
      },
    },
    {
      label: 'OS Download Notification',
      file: '15-os-download-notification.png',
      capture: async () => {
        const fsLib = await import('node:fs');
        const bgData = fsLib.readFileSync(path.join(outputDir, '11-detail-selesai.png')).toString('base64');
        await page.setContent(`
          <div style="width: 477px; height: 960px; position: relative; overflow: hidden; margin: 0; padding: 0;">
            <img src="data:image/png;base64,${bgData}" style="width: 100%; height: 100%; object-fit: cover; display: block;" />
            <div style="position: absolute; top: 12px; left: 12px; right: 12px;">
              <div style="background: #ffffff; border-radius: 20px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); padding: 14px 16px; font-family: sans-serif; display: flex; align-items: flex-start;">
                <div style="width: 32px; height: 32px; background: #E0F2FE; border-radius: 50%; margin-right: 14px; display:flex; align-items:center; justify-content:center; color:#0284C7; flex-shrink: 0;">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                </div>
                <div style="flex: 1;">
                  <div style="font-size: 11px; color: #64748B; display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span>Download Manager</span>
                    <span>Baru saja</span>
                  </div>
                  <div style="font-weight: 600; font-size: 14px; color: #0F172A; margin-bottom: 2px;">Surat_Persetujuan_Pra_Penelitian.pdf</div>
                  <div style="color: #475569; font-size: 13px;">Download selesai. Ketuk untuk membuka.</div>
                </div>
              </div>
            </div>
          </div>
        `);
        await page.waitForTimeout(200);
        await page.screenshot({ path: path.join(outputDir, '15-os-download-notification.png') });
      },
    },
    {
      label: 'PDF Preview',
      file: '16-pdf-preview.png',
      capture: async () => {
        const fsLib = await import('node:fs');
        const unilaLogoPath = path.join(rootDir, 'public', 'icons', 'unila.png');
        let unilaLogoBase64 = '';
        if (fsLib.existsSync(unilaLogoPath)) {
          unilaLogoBase64 = 'data:image/png;base64,' + fsLib.readFileSync(unilaLogoPath).toString('base64');
        }

        const signatureSvgRaw = `<svg viewBox="0 0 150 60" xmlns="http://www.w3.org/2000/svg"><path d="M 10 40 Q 30 10 40 40 T 70 30 T 100 45 T 140 20" fill="none" stroke="#000080" stroke-width="2"/><path d="M 20 50 Q 40 20 50 45 T 80 35 T 120 40" fill="none" stroke="#000080" stroke-width="1.5"/></svg>`;
        const qrSvgRaw = `<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100" fill="white"/><rect x="10" y="10" width="25" height="25" fill="none" stroke="black" stroke-width="4"/><rect x="15" y="15" width="15" height="15" fill="black"/><rect x="65" y="10" width="25" height="25" fill="none" stroke="black" stroke-width="4"/><rect x="70" y="15" width="15" height="15" fill="black"/><rect x="10" y="65" width="25" height="25" fill="none" stroke="black" stroke-width="4"/><rect x="15" y="70" width="15" height="15" fill="black"/><rect x="65" y="65" width="10" height="10" fill="black"/><rect x="80" y="80" width="10" height="10" fill="black"/><rect x="45" y="45" width="10" height="10" fill="black"/><rect x="40" y="15" width="15" height="5" fill="black"/><rect x="15" y="40" width="15" height="5" fill="black"/></svg>`;
        
        const signatureSvg = 'data:image/svg+xml;base64,' + Buffer.from(signatureSvgRaw).toString('base64');
        const qrSvg = 'data:image/svg+xml;base64,' + Buffer.from(qrSvgRaw).toString('base64');

        await page.setContent(`
          <div style="width: 477px; height: 960px; background: #F1F5F9; display: flex; flex-direction: column; margin: 0; font-family: Roboto, sans-serif;">
            <div style="height: 56px; background: #ffffff; display: flex; align-items: center; padding: 0 16px; color: #333; box-shadow: 0 1px 3px rgba(0,0,0,0.1); z-index: 10;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 20px;"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
              <div style="font-size: 16px; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1;">Surat_Persetujuan_Pra_Penelitian.pdf</div>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left: 16px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
            <div style="flex: 1; padding: 24px; display: flex; justify-content: center; align-items:flex-start; overflow:hidden;">
              <div style="width: 100%; aspect-ratio: 1 / 1.414; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 32px; font-family: 'Times New Roman', serif; border-radius: 2px;">
                <div style="display: flex; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 12px;">
                   <div style="width: 48px; height: 48px; margin-right: 12px; display: flex; align-items: center; justify-content: center;">
                     ${unilaLogoBase64 ? `<img src="${unilaLogoBase64}" style="width: 48px; height: auto;" />` : '<div style="width:48px; height:48px; background:#E2E8F0; border-radius:50%;"></div>'}
                   </div>
                   <div style="text-align: center; flex: 1;">
                     <div style="font-weight: bold; font-size: 13px; font-family: Arial, sans-serif;">KEMENTERIAN PENDIDIKAN DAN KEBUDAYAAN</div>
                     <div style="font-weight: bold; font-size: 12px; font-family: Arial, sans-serif;">UNIVERSITAS LAMPUNG</div>
                     <div style="font-weight: bold; font-size: 14px; font-family: Arial, sans-serif;">FAKULTAS KEGURUAN DAN ILMU PENDIDIKAN</div>
                   </div>
                </div>
                <div style="text-align: center; font-weight: bold; font-size: 13px; text-decoration: underline; margin-bottom: 4px;">SURAT PERSETUJUAN PRA PENELITIAN</div>
                <div style="text-align: center; font-size: 11px; margin-bottom: 20px;">Nomor: 123/UN26.13/PN/2026</div>
                <div style="font-size: 11px; line-height: 1.5; text-align: justify; margin-bottom: 12px;">
                  Dekan Fakultas Keguruan dan Ilmu Pendidikan Universitas Lampung, menerangkan dengan sebenarnya bahwa mahasiswa di bawah ini:
                </div>
                <table style="width: 100%; font-size: 11px; margin-bottom: 12px; margin-left: 12px;">
                  <tr><td style="width: 90px; padding: 2px 0;">Nama Lengkap</td><td>: Mahasiswa Demo</td></tr>
                  <tr><td style="padding: 2px 0;">NPM</td><td>: 2613041001</td></tr>
                  <tr><td style="padding: 2px 0;">Program Studi</td><td>: Pendidikan Bahasa</td></tr>
                </table>
                <div style="font-size: 11px; line-height: 1.5; text-align: justify;">
                  Diberikan izin untuk melaksanakan pra penelitian di instansi terkait guna penyusunan tugas akhir/skripsi. Surat persetujuan ini diberikan agar dapat dipergunakan sebagaimana mestinya.
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 40px;">
                  <div style="width: 60px; height: 60px;">
                    <img src="${qrSvg}" style="width: 100%; height: 100%;" />
                  </div>
                  <div style="text-align: left; width: 160px; position: relative;">
                    <div style="font-size: 11px; margin-bottom: 10px;">Bandar Lampung, 03 Mei 2026<br>Dekan,</div>
                    <img src="${signatureSvg}" style="width: 120px; height: 50px; position: absolute; left: 0; top: 20px; z-index: 1;" />
                    <div style="font-size: 11px; font-weight: bold; text-decoration: underline; position: relative; z-index: 2; margin-top: 40px;">Prof. Dr. Ir. Sugiyono, M.Pd.</div>
                    <div style="font-size: 11px; position: relative; z-index: 2;">NIP. 19700101 199512 1 001</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `);
        await page.waitForTimeout(200);
        await page.screenshot({ path: path.join(outputDir, '16-pdf-preview.png') });
      },
    },
  ];

  try {
    for (const item of manifest) {
      console.log(`Menangkap layar: ${item.file}`);
      await item.capture();
    }
  } finally {
    await browser.close();
  }
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
