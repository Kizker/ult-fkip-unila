import pptxgen from 'pptxgenjs';
import JSZip from 'jszip';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const screenshots = path.join(root, 'screenshots');
const email = path.join(root, 'email-simulation');
const out = path.join(__dirname, 'video-02-daftar-login-lengkapi-profil-mobile-fixed.pptx');

const pptx = new pptxgen();
pptx.author = 'Codex';
pptx.company = 'ULT FKIP Unila';
pptx.subject = 'Panduan Mahasiswa Video 2';
pptx.title = 'Panduan Mahasiswa: Cara Daftar, Login, dan Melengkapi Profil';
pptx.lang = 'id-ID';
pptx.defineLayout({ name: 'LAYOUT_CUSTOM', width: 9.9375, height: 20 });
pptx.theme = {
  headFontFace: 'Aptos Display',
  bodyFontFace: 'Aptos',
  lang: 'id-ID',
};
pptx.layout = 'LAYOUT_CUSTOM';

const W = 9.9375;
const H = 20;
const PURPLE = '7C3AED';
const PURPLE_DARK = '5B21B6';
const YELLOW = 'FFD84D';
const DARK = '111827';
const MUTED = '667085';
const WHITE = 'FFFFFF';

const assets = {
  registerTop: path.join(screenshots, '01-register-top.png'),
  registerAcademic: path.join(screenshots, '02-register-academic.png'),
  registerPhoto: path.join(screenshots, '03-register-photo.png'),
  registerPassword: path.join(screenshots, '04-register-password.png'),
  verify: path.join(screenshots, '05-verify-email.png'),
  login: path.join(screenshots, '06-login.png'),
  dashboard: path.join(screenshots, '07-dashboard-pemohon.png'),
  menu: path.join(screenshots, '08-menu-pengaturan.png'),
  profileTop: path.join(screenshots, '09-profil-top.png'),
  profilePhoto: path.join(screenshots, '10-profil-photo.png'),
  profileSave: path.join(screenshots, '11-profil-save.png'),
  inbox: path.join(email, '01-inbox-verification-list.png'),
  spam: path.join(email, '02-spam-folder.png'),
  mail: path.join(email, '03-verification-email-opened.png'),
};

for (const file of Object.values(assets)) {
  if (!fs.existsSync(file)) throw new Error(`Aset tidak ditemukan: ${file}`);
}

function px(value) {
  return value / 96;
}

function addCaption(slide, text) {
  slide.addShape(pptx.ShapeType.roundRect, {
    x: 0.45,
    y: 17.05,
    w: W - 0.9,
    h: 0.78,
    rectRadius: 0.08,
    fill: { color: WHITE, transparency: 4 },
    line: { color: 'E9D5FF', transparency: 10, width: 1 },
    shadow: { type: 'outer', color: '312E81', opacity: 0.12, blur: 2, angle: 45, distance: 1 },
    rotate: 0,
  });
  slide.addText(text, {
    x: 0.65,
    y: 17.2,
    w: W - 1.3,
    h: 0.42,
    margin: 0,
    fontFace: 'Aptos Display',
    fontSize: 18,
    bold: true,
    color: DARK,
    align: 'center',
    breakLine: false,
    fit: 'shrink',
    transparency: 0,
    animation: { type: 'fadeIn', delay: 0.2, duration: 0.5 },
  });
}

function addSceneBadge(slide, scene) {
  slide.addShape(pptx.ShapeType.roundRect, {
    x: 0.42,
    y: 0.34,
    w: 1.55,
    h: 0.46,
    rectRadius: 0.08,
    fill: { color: PURPLE, transparency: 0 },
    line: { color: PURPLE, transparency: 100 },
    animation: { type: 'fadeIn', delay: 0.1, duration: 0.35 },
  });
  slide.addText(scene, {
    x: 0.42,
    y: 0.44,
    w: 1.55,
    h: 0.22,
    margin: 0,
    fontFace: 'Aptos Display',
    fontSize: 10,
    bold: true,
    color: WHITE,
    align: 'center',
    fit: 'shrink',
  });
}

function addHighlight(slide, [x, y, w, h], index) {
  slide.addShape(pptx.ShapeType.roundRect, {
    x: px(x),
    y: px(y),
    w: px(w),
    h: px(h),
    rectRadius: 0.06,
    fill: { color: YELLOW, transparency: 88 },
    line: { color: YELLOW, transparency: 0, width: 2.5 },
    animation: { type: 'fadeIn', delay: 0.4 + index * 0.16, duration: 0.45 },
  });
}

function addTap(slide, [x, y, size = 58], index) {
  slide.addShape(pptx.ShapeType.ellipse, {
    x: px(x),
    y: px(y),
    w: px(size),
    h: px(size),
    fill: { color: PURPLE, transparency: 72 },
    line: { color: PURPLE, transparency: 15, width: 2 },
    animation: { type: 'fadeIn', delay: 0.85 + index * 0.18, duration: 0.22 },
  });
  slide.addShape(pptx.ShapeType.ellipse, {
    x: px(x + size * 0.28),
    y: px(y + size * 0.28),
    w: px(size * 0.44),
    h: px(size * 0.44),
    fill: { color: PURPLE, transparency: 20 },
    line: { color: PURPLE, transparency: 100 },
    animation: { type: 'fadeOut', delay: 1.15 + index * 0.18, duration: 0.35 },
  });
}

function addMotionHint(slide, direction = 'right') {
  const startX = direction === 'right' ? 7.65 : 0.55;
  const endX = direction === 'right' ? 8.55 : 1.45;
  const arrow = direction === 'right' ? '→' : '←';
  slide.addText(arrow, {
    x: startX,
    y: 16.0,
    w: 0.55,
    h: 0.45,
    margin: 0,
    fontFace: 'Aptos Display',
    fontSize: 24,
    bold: true,
    color: PURPLE_DARK,
    align: 'center',
    animation: { type: 'flyIn', direction, delay: 1.05, duration: 0.45 },
  });
  slide.addShape(pptx.ShapeType.line, {
    x: Math.min(startX, endX),
    y: 16.25,
    w: Math.abs(endX - startX),
    h: 0,
    line: { color: PURPLE_DARK, transparency: 25, width: 1.5 },
    animation: { type: 'fadeIn', delay: 0.95, duration: 0.35 },
  });
}

function setSlideTiming(slide, seconds = 5) {
  slide.background = { color: 'F8F4FF' };
  slide.addNotes(`Timing: ${seconds} detik. Export video dari PowerPoint setelah audio/voice over final dimasukkan.`);
  slide._timingSeconds = seconds;
}

function addImageSlide({ scene, caption, image, highlights = [], taps = [], seconds = 5, motion = false }) {
  const slide = pptx.addSlide();
  setSlideTiming(slide, seconds);
  slide.addImage({
    path: image,
    x: 0,
    y: 0,
    w: W,
    h: H,
    animation: { type: 'fadeIn', delay: 0, duration: 0.25 },
  });
  addSceneBadge(slide, scene);
  highlights.forEach((h, i) => addHighlight(slide, h, i));
  taps.forEach((t, i) => addTap(slide, t, i));
  if (motion) addMotionHint(slide, motion);
  addCaption(slide, caption);
  return slide;
}

function addCoverSlide() {
  const slide = pptx.addSlide();
  setSlideTiming(slide, 4);
  slide.addImage({ path: assets.registerTop, x: 0, y: 0, w: W, h: H });
  slide.addShape(pptx.ShapeType.roundRect, {
    x: 0.7,
    y: 4.25,
    w: W - 1.4,
    h: 4.2,
    rectRadius: 0.12,
    fill: { color: WHITE, transparency: 3 },
    line: { color: 'E9D5FF', transparency: 0, width: 1 },
    shadow: { type: 'outer', color: '312E81', opacity: 0.14, blur: 2, angle: 45, distance: 1 },
    animation: { type: 'fadeIn', delay: 0.15, duration: 0.5 },
  });
  slide.addText('Panduan Mahasiswa', {
    x: 1.0,
    y: 4.78,
    w: W - 2.0,
    h: 0.45,
    margin: 0,
    fontFace: 'Aptos Display',
    fontSize: 20,
    bold: true,
    color: PURPLE,
    align: 'center',
    animation: { type: 'fadeIn', delay: 0.35, duration: 0.4 },
  });
  slide.addText('Cara Daftar, Login, dan Melengkapi Profil', {
    x: 1.0,
    y: 5.45,
    w: W - 2.0,
    h: 1.28,
    margin: 0,
    fontFace: 'Aptos Display',
    fontSize: 30,
    bold: true,
    color: DARK,
    align: 'center',
    fit: 'shrink',
    animation: { type: 'flyIn', direction: 'up', delay: 0.55, duration: 0.45 },
  });
  slide.addText('Video 2 • Format Mobile 954 x 1920 px', {
    x: 1.0,
    y: 7.08,
    w: W - 2.0,
    h: 0.42,
    margin: 0,
    fontFace: 'Aptos',
    fontSize: 14,
    color: MUTED,
    align: 'center',
    animation: { type: 'fadeIn', delay: 0.85, duration: 0.4 },
  });
}

function addSummarySlide() {
  const slide = pptx.addSlide();
  setSlideTiming(slide, 5);
  slide.background = { color: 'F8F4FF' };
  addSceneBadge(slide, 'Scene 29');
  slide.addText('Siap mengajukan layanan', {
    x: 0.72,
    y: 4.15,
    w: W - 1.44,
    h: 0.75,
    margin: 0,
    fontFace: 'Aptos Display',
    fontSize: 30,
    bold: true,
    color: DARK,
    align: 'center',
    animation: { type: 'fadeIn', delay: 0.2, duration: 0.45 },
  });
  ['Daftar akun berhasil', 'Login berhasil', 'Profil sudah lengkap'].forEach((text, i) => {
    slide.addShape(pptx.ShapeType.roundRect, {
      x: 0.75,
      y: 5.45 + i * 1.03,
      w: W - 1.5,
      h: 0.72,
      rectRadius: 0.08,
      fill: { color: WHITE, transparency: 0 },
      line: { color: 'E9D5FF', transparency: 0, width: 1 },
      shadow: { type: 'outer', color: '312E81', opacity: 0.10, blur: 2, angle: 45, distance: 1 },
      animation: { type: 'flyIn', direction: 'right', delay: 0.45 + i * 0.25, duration: 0.35 },
    });
    slide.addText(`✓ ${text}`, {
      x: 1.0,
      y: 5.62 + i * 1.03,
      w: W - 2.0,
      h: 0.35,
      margin: 0,
      fontFace: 'Aptos Display',
      fontSize: 18,
      bold: true,
      color: DARK,
      align: 'center',
    });
  });
}

function addClosingSlide() {
  const slide = pptx.addSlide();
  setSlideTiming(slide, 5);
  slide.background = { color: 'F8F4FF' };
  addSceneBadge(slide, 'Scene 30');
  slide.addText('Berikutnya:', {
    x: 0.75,
    y: 4.5,
    w: W - 1.5,
    h: 0.55,
    margin: 0,
    fontFace: 'Aptos Display',
    fontSize: 22,
    bold: true,
    color: PURPLE,
    align: 'center',
    animation: { type: 'fadeIn', delay: 0.2, duration: 0.35 },
  });
  slide.addText('Mengajukan Layanan', {
    x: 0.75,
    y: 5.18,
    w: W - 1.5,
    h: 0.85,
    margin: 0,
    fontFace: 'Aptos Display',
    fontSize: 32,
    bold: true,
    color: DARK,
    align: 'center',
    animation: { type: 'flyIn', direction: 'up', delay: 0.45, duration: 0.45 },
  });
  slide.addText('Memilih layanan, membaca syarat, mengisi formulir, dan mengirim permohonan.', {
    x: 1.1,
    y: 6.25,
    w: W - 2.2,
    h: 0.9,
    margin: 0,
    fontFace: 'Aptos',
    fontSize: 18,
    color: MUTED,
    align: 'center',
    fit: 'shrink',
    animation: { type: 'fadeIn', delay: 0.75, duration: 0.45 },
  });
}

addCoverSlide();

[
  ['Scene 02', 'Buka halaman Daftar', assets.registerTop, [[590, 36, 72, 72]], [[610, 42, 58]], 5, 'right'],
  ['Scene 03', 'Isi nama dan email aktif', assets.registerTop, [[48, 420, 620, 170]], [], 7, false],
  ['Scene 04', 'Pilih Mahasiswa dan isi NPM', assets.registerTop, [[48, 690, 620, 210]], [], 6, false],
  ['Scene 05', 'Cek data sebelum lanjut', assets.registerTop, [[48, 405, 620, 505]], [], 6, false],
  ['Scene 06', 'Nama, email, dan NPM harus benar', assets.registerTop, [[48, 405, 620, 505]], [], 7, false],
  ['Scene 07', 'Buat password akun', assets.registerPassword, [[48, 78, 620, 210]], [], 6, false],
  ['Scene 08', 'Gunakan password yang aman', assets.registerPassword, [[48, 78, 620, 335]], [], 6, false],
  ['Scene 09', 'Periksa semua isian', assets.registerAcademic, [[48, 120, 620, 720]], [], 7, 'down'],
  ['Scene 10', 'Klik Daftar', assets.registerPassword, [[48, 250, 620, 70]], [[300, 226, 58]], 5, false],
  ['Scene 11', 'Verifikasi email dulu', assets.verify, [[48, 385, 620, 245]], [], 6, false],
  ['Scene 12', 'Buka inbox email', assets.inbox, [], [[560, 505, 58]], 7, 'right'],
  ['Scene 13', 'Cek spam jika email belum ada', assets.spam, [], [[560, 430, 58]], 5, false],
  ['Scene 14', 'Klik tautan verifikasi', assets.mail, [], [[540, 725, 58]], 6, false],
  ['Scene 15', 'Gunakan Kirim ulang jika perlu', assets.verify, [[65, 548, 585, 72]], [[330, 560, 58]], 6, false],
  ['Scene 16', 'Akun sudah aktif', assets.verify, [[65, 390, 585, 240]], [], 4, false],
  ['Scene 17', 'Masuk ke halaman Login', assets.login, [[48, 330, 620, 320]], [], 6, false],
  ['Scene 18', 'Isi email dan password', assets.login, [[48, 445, 620, 245]], [], 7, false],
  ['Scene 19', 'Klik Masuk', assets.login, [[48, 690, 620, 76]], [[330, 700, 58]], 5, false],
  ['Scene 20', 'Dashboard pemohon terbuka', assets.dashboard, [[18, 72, 680, 430]], [], 5, false],
  ['Scene 21', 'Buka Pengaturan', assets.menu, [[236, 62, 242, 255]], [[622, 36, 58]], 6, false],
  ['Scene 22', 'Pilih Pengaturan', assets.menu, [[238, 236, 238, 34]], [[244, 250, 58]], 5, false],
  ['Scene 23', 'Cek data profil', assets.profileTop, [[48, 290, 620, 365]], [], 7, false],
  ['Scene 24', 'Lengkapi data yang kosong', assets.profileTop, [[48, 575, 620, 440]], [], 7, false],
  ['Scene 25', 'Unggah file yang diminta', assets.profilePhoto, [[48, 65, 620, 280]], [[238, 220, 58]], 6, false],
  ['Scene 26', 'Pastikan file benar dan jelas', assets.profilePhoto, [[48, 65, 620, 280]], [], 6, false],
  ['Scene 27', 'Simpan perubahan', assets.profileSave, [[48, 325, 620, 80]], [[330, 330, 58]], 5, false],
  ['Scene 28', 'Profil lengkap membantu proses layanan', assets.profileSave, [[48, 118, 620, 288]], [], 5, false],
].forEach(([scene, caption, image, highlights, taps, seconds, motion]) => {
  addImageSlide({ scene, caption, image, highlights, taps, seconds, motion });
});

addSummarySlide();
addClosingSlide();

await pptx.writeFile({ fileName: out });

const zip = await JSZip.loadAsync(fs.readFileSync(out));
for (let i = 1; i <= pptx._slides.length; i += 1) {
  const slide = pptx._slides[i - 1];
  const seconds = slide._timingSeconds || 5;
  const file = `ppt/slides/slide${i}.xml`;
  let xml = await zip.file(file).async('string');
  const transitionXml = `<p:transition advClick="0" advTm="${seconds * 1000}"><p:fade/></p:transition>`;
  if (!xml.includes('<p:transition')) {
    if (xml.includes('</p:clrMapOvr>')) {
      xml = xml.replace('</p:clrMapOvr>', `</p:clrMapOvr>${transitionXml}`);
    } else {
      xml = xml.replace('</p:cSld>', `</p:cSld><p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr>${transitionXml}`);
    }
    zip.file(file, xml);
  }
}
const enhanced = await zip.generateAsync({
  type: 'nodebuffer',
  compression: 'DEFLATE',
  compressionOptions: { level: 6 },
});
fs.writeFileSync(out, enhanced);
console.log(out);
