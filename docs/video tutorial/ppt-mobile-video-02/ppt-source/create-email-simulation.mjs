import playwright from '../../../../node_modules/playwright/index.js';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const { chromium } = playwright;

const chromePath = process.env.CHROME_PATH || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const outputDir = fileURLToPath(new URL('../email-simulation/', import.meta.url));

fs.mkdirSync(outputDir, { recursive: true });

const baseStyles = `
  * { box-sizing: border-box; }
  body {
    margin: 0;
    width: 954px;
    min-height: 1920px;
    font-family: Inter, Arial, sans-serif;
    background: #f4f7fb;
    color: #162033;
  }
  .phone {
    width: 954px;
    height: 1920px;
    background: linear-gradient(180deg, #f8fbff 0%, #eef3fb 100%);
    overflow: hidden;
    position: relative;
  }
  .status {
    height: 86px;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    padding: 0 52px 18px;
    font-size: 28px;
    font-weight: 700;
    color: #111827;
  }
  .appbar {
    height: 132px;
    padding: 22px 44px;
    display: flex;
    align-items: center;
    gap: 24px;
    border-bottom: 1px solid #d8e0ef;
    background: rgba(255,255,255,.92);
  }
  .menu {
    width: 54px;
    height: 54px;
    border-radius: 18px;
    border: 1px solid #d5ddec;
    background: white;
    position: relative;
  }
  .menu::before,
  .menu::after {
    content: "";
    position: absolute;
    left: 14px;
    right: 14px;
    height: 4px;
    border-radius: 4px;
    background: #334155;
  }
  .menu::before { top: 18px; box-shadow: 0 8px 0 #334155; }
  .menu::after { top: 34px; }
  .title { flex: 1; }
  .title h1 { margin: 0; font-size: 42px; line-height: 1.1; letter-spacing: 0; }
  .title p { margin: 8px 0 0; font-size: 23px; color: #667085; }
  .avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #4f46e5;
    color: white;
    display: grid;
    place-items: center;
    font-size: 24px;
    font-weight: 800;
  }
  .label {
    position: absolute;
    top: 232px;
    left: 44px;
    z-index: 5;
    border: 1px solid #c7d2fe;
    background: #eef2ff;
    color: #3730a3;
    font-size: 22px;
    font-weight: 700;
    border-radius: 999px;
    padding: 12px 20px;
  }
  .content { padding: 104px 44px 44px; }
  .search {
    height: 72px;
    border-radius: 24px;
    background: white;
    border: 1px solid #d8e0ef;
    color: #7a8599;
    display: flex;
    align-items: center;
    padding: 0 28px;
    font-size: 25px;
    box-shadow: 0 14px 30px rgba(30,41,59,.06);
  }
  .section {
    margin: 36px 0 18px;
    font-size: 24px;
    color: #667085;
    font-weight: 700;
  }
  .email {
    position: relative;
    display: grid;
    grid-template-columns: 72px 1fr auto;
    gap: 20px;
    align-items: start;
    padding: 28px 24px;
    margin-bottom: 18px;
    background: white;
    border: 1px solid #dde5f2;
    border-radius: 28px;
    box-shadow: 0 18px 36px rgba(30,41,59,.08);
  }
  .sender {
    width: 72px;
    height: 72px;
    border-radius: 24px;
    background: #4338ca;
    color: white;
    display: grid;
    place-items: center;
    font-size: 26px;
    font-weight: 800;
  }
  .email h2 { margin: 0; font-size: 29px; line-height: 1.25; letter-spacing: 0; }
  .email p { margin: 8px 0 0; font-size: 23px; line-height: 1.42; color: #667085; }
  .time { font-size: 21px; color: #7a8599; padding-top: 4px; }
  .highlight {
    border: 4px solid #7c3aed;
    box-shadow: 0 0 0 10px rgba(124,58,237,.15), 0 22px 48px rgba(67,56,202,.16);
  }
  .folder {
    display: flex;
    align-items: center;
    gap: 22px;
    min-height: 88px;
    padding: 22px 26px;
    margin-bottom: 16px;
    border-radius: 28px;
    background: white;
    border: 1px solid #dde5f2;
    font-size: 29px;
    font-weight: 700;
    box-shadow: 0 14px 28px rgba(30,41,59,.06);
  }
  .folder .icon {
    width: 52px;
    height: 42px;
    border-radius: 12px;
    background: #c7d2fe;
    position: relative;
  }
  .folder .icon::before {
    content: "";
    position: absolute;
    left: 0;
    top: -10px;
    width: 34px;
    height: 16px;
    border-radius: 10px 10px 0 0;
    background: #a5b4fc;
  }
  .mail-card {
    margin: 26px 0;
    padding: 34px;
    border-radius: 34px;
    background: white;
    border: 1px solid #dde5f2;
    box-shadow: 0 20px 44px rgba(30,41,59,.08);
  }
  .mail-card h2 {
    margin: 0 0 16px;
    font-size: 38px;
    line-height: 1.18;
    letter-spacing: 0;
  }
  .mail-card .meta {
    border-bottom: 1px solid #e5eaf4;
    padding-bottom: 24px;
    margin-bottom: 28px;
    font-size: 23px;
    line-height: 1.5;
    color: #667085;
  }
  .mail-card p {
    font-size: 27px;
    line-height: 1.55;
    color: #344054;
  }
  .verify-btn {
    width: 100%;
    margin-top: 28px;
    min-height: 78px;
    border-radius: 24px;
    background: #4f46e5;
    color: white;
    display: grid;
    place-items: center;
    font-size: 29px;
    font-weight: 800;
    box-shadow: 0 18px 32px rgba(79,70,229,.28);
  }
`;

const pages = {
  '01-inbox-verification-list.png': `
    <div class="phone">
      <div class="status"><span>09.20</span><span>5G 87%</span></div>
      <div class="appbar"><div class="menu"></div><div class="title"><h1>Inbox</h1><p>Email masuk terbaru</p></div><div class="avatar">M</div></div>
      <div class="label">Simulasi inbox email</div>
      <div class="content">
        <div class="search">Cari email</div>
        <div class="section">Hari ini</div>
        <div class="email highlight">
          <div class="sender">U</div>
          <div><h2>ULT FKIP Unila</h2><p>Verifikasi Email Akun - Klik tautan verifikasi untuk mengaktifkan akun Anda.</p></div>
          <div class="time">09.18</div>
        </div>
        <div class="email">
          <div class="sender" style="background:#0f766e;">A</div>
          <div><h2>Akademik FKIP</h2><p>Informasi layanan akademik dan administrasi mahasiswa.</p></div>
          <div class="time">08.42</div>
        </div>
        <div class="email">
          <div class="sender" style="background:#0369a1;">P</div>
          <div><h2>Pemberitahuan Sistem</h2><p>Riwayat aktivitas akun dan keamanan login.</p></div>
          <div class="time">Kemarin</div>
        </div>
      </div>
    </div>
  `,
  '02-spam-folder.png': `
    <div class="phone">
      <div class="status"><span>09.21</span><span>5G 87%</span></div>
      <div class="appbar"><div class="menu"></div><div class="title"><h1>Folder Email</h1><p>Periksa folder lain</p></div><div class="avatar">M</div></div>
      <div class="label">Simulasi inbox email</div>
      <div class="content">
        <div class="folder"><div class="icon"></div><span>Inbox</span></div>
        <div class="folder"><div class="icon"></div><span>Promosi</span></div>
        <div class="folder highlight"><div class="icon"></div><span>Spam / Junk</span></div>
        <div class="folder"><div class="icon"></div><span>Arsip</span></div>
        <div class="mail-card" style="margin-top:70px;">
          <h2>Belum menemukan email?</h2>
          <p>Cek folder spam atau junk mail. Beberapa layanan email bisa memindahkan pesan verifikasi ke folder ini.</p>
        </div>
      </div>
    </div>
  `,
  '03-verification-email-opened.png': `
    <div class="phone">
      <div class="status"><span>09.22</span><span>5G 87%</span></div>
      <div class="appbar"><div class="menu"></div><div class="title"><h1>Pesan Email</h1><p>Verifikasi akun</p></div><div class="avatar">M</div></div>
      <div class="label">Simulasi inbox email</div>
      <div class="content">
        <div class="mail-card">
          <h2>Verifikasi Email Akun ULT FKIP</h2>
          <div class="meta">Dari: ULT FKIP Unila<br>Kepada: mahasiswa.tutorial@example.test</div>
          <p>Halo Mahasiswa Tutorial, klik tombol di bawah ini untuk mengaktifkan akun Anda sebelum menggunakan sistem layanan.</p>
          <div class="verify-btn">Verifikasi Email</div>
          <p style="font-size:22px;color:#667085;margin-top:34px;">Jika tombol tidak dapat dibuka, salin tautan verifikasi yang tersedia pada email.</p>
        </div>
      </div>
    </div>
  `,
};

const browser = await chromium.launch({
  headless: true,
  executablePath: chromePath,
});
const page = await browser.newPage({
  viewport: { width: 954, height: 1920 },
  deviceScaleFactor: 1,
  isMobile: true,
  hasTouch: true,
});

for (const [fileName, body] of Object.entries(pages)) {
  await page.setContent(`<!doctype html><html><head><meta charset="utf-8"><style>${baseStyles}</style></head><body>${body}</body></html>`);
  await page.screenshot({
    path: path.join(outputDir, fileName),
    fullPage: false,
  });
  console.log(fileName);
}

await browser.close();
