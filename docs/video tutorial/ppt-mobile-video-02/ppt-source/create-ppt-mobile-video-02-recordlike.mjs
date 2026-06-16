import pptxgen from 'pptxgenjs';
import JSZip from 'jszip';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const screenshots = path.join(root, 'screenshots');
const email = path.join(root, 'email-simulation');
const out = path.join(__dirname, 'video-02-daftar-login-lengkapi-profil-mobile-clean-pointer.pptx');
const tapPointerIcon = path.join(__dirname, 'tap-pointer.svg');
const scrollArrowIcon = path.join(__dirname, 'scroll-arrow-down.svg');

const pptx = new pptxgen();
pptx.author = 'Codex';
pptx.company = 'ULT FKIP Unila';
pptx.subject = 'Panduan Mahasiswa Video 2';
pptx.title = 'Panduan Mahasiswa: Cara Daftar, Login, dan Melengkapi Profil';
pptx.lang = 'id-ID';
pptx.defineLayout({ name: 'MOBILE_954_1920', width: 9.9375, height: 20 });
pptx.layout = 'MOBILE_954_1920';
pptx.theme = {
  headFontFace: 'Aptos Display',
  bodyFontFace: 'Aptos',
  lang: 'id-ID',
};

const W = 9.9375;
const H = 20;
const PURPLE = '7C3AED';
const PURPLE_SOFT = 'A855F7';
const YELLOW = 'FFD84D';
const WHITE = 'FFFFFF';

const assets = {
  publicHome: path.join(screenshots, '00-public-home.png'),
  publicHomeMenu: path.join(screenshots, '00-public-home-menu.png'),
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
  profilePhotoPicker: path.join(screenshots, '36-profil-photo-file-picker.png'),
  profilePhotoSelected: path.join(screenshots, '37-profil-photo-selected.png'),
  registerFilledIdentity: path.join(screenshots, '12-register-filled-identity.png'),
  registerFilledAcademic: path.join(screenshots, '13-register-filled-academic.png'),
  registerFilledPhoto: path.join(screenshots, '14-register-filled-photo.png'),
  registerFilledPassword: path.join(screenshots, '15-register-filled-password.png'),
  loginFilled: path.join(screenshots, '16-login-filled.png'),
  registerStepEmpty: path.join(screenshots, '17-register-step-empty.png'),
  registerStepName: path.join(screenshots, '18-register-step-name.png'),
  registerStepEmail: path.join(screenshots, '19-register-step-email.png'),
  registerDropdownRole: path.join(screenshots, '20-register-dropdown-jenis-akun-open.png'),
  registerStepRole: path.join(screenshots, '21-register-step-jenis-akun-selected.png'),
  registerStepNpm: path.join(screenshots, '22-register-step-npm.png'),
  registerDropdownJurusan: path.join(screenshots, '23-register-dropdown-jurusan-open.png'),
  registerStepJurusan: path.join(screenshots, '24-register-step-jurusan-selected.png'),
  registerDropdownProdi: path.join(screenshots, '25-register-dropdown-prodi-open.png'),
  registerStepProdi: path.join(screenshots, '26-register-step-prodi-selected.png'),
  registerPhotoBeforePick: path.join(screenshots, '27-register-photo-before-pick.png'),
  registerPhotoPicker: path.join(screenshots, '28-register-photo-file-picker.png'),
  registerPhotoSelected: path.join(screenshots, '29-register-photo-selected.png'),
  registerPasswordEmpty: path.join(screenshots, '30-register-password-empty.png'),
  registerPasswordFilled: path.join(screenshots, '31-register-password-filled.png'),
  registerConfirmPasswordFilled: path.join(screenshots, '32-register-confirm-password-filled.png'),
  loginEmpty: path.join(screenshots, '33-login-empty.png'),
  loginEmailFilled: path.join(screenshots, '34-login-email-filled.png'),
  loginPasswordFilled: path.join(screenshots, '35-login-password-filled.png'),
  inbox: path.join(email, '01-inbox-verification-list.png'),
  spam: path.join(email, '02-spam-folder.png'),
  mail: path.join(email, '03-verification-email-opened.png'),
};

for (const file of Object.values(assets)) {
  if (!fs.existsSync(file)) throw new Error(`Aset tidak ditemukan: ${file}`);
}
if (!fs.existsSync(tapPointerIcon)) throw new Error(`Aset tidak ditemukan: ${tapPointerIcon}`);
if (!fs.existsSync(scrollArrowIcon)) throw new Error(`Aset tidak ditemukan: ${scrollArrowIcon}`);

const px = (value) => value / 96;

const frames = [];

function addFrame({
  image,
  duration = 1.0,
  highlights = [],
  taps = [],
  arrows = [],
  dim = false,
  note = '',
  blendImage = null,
  blendTransparency = 100,
  blendXOffset = 0,
  blendYOffset = 0,
  blendScale = 1,
}) {
  frames.push({ image, duration, highlights, taps, arrows, dim, note, blendImage, blendTransparency, blendXOffset, blendYOffset, blendScale });
}

function addPause(image, duration = 1.0, note = '') {
  addFrame({ image, duration, note });
}

function addHighlightSequence(image, highlights, note = '', duration = 0.55) {
  addFrame({ image, duration: 0.22, note: `${note} - tampil normal` });
  addFrame({ image, duration, highlights, note: `${note} - highlight` });
  addFrame({ image, duration: 0.16, highlights, note: `${note} - stabil` });
}

function addTapSequence(image, tap, highlights = [], note = '') {
  addFrame({ image, duration: 0.18, highlights, note: `${note} - sebelum tap` });
  addFrame({ image, duration: 0.10, highlights, taps: [{ ...tap, scale: 0.55, alpha: 18, offsetY: 4 }], note: `${note} - pointer masuk 1` });
  addFrame({ image, duration: 0.10, highlights, taps: [{ ...tap, scale: 0.72, alpha: 30, offsetY: 2 }], note: `${note} - pointer masuk 2` });
  addFrame({ image, duration: 0.10, highlights, taps: [{ ...tap, scale: 0.92, alpha: 46 }], note: `${note} - tap tekan 1` });
  addFrame({ image, duration: 0.10, highlights, taps: [{ ...tap, scale: 1.06, alpha: 60, offsetY: -1 }], note: `${note} - tap tekan 2` });
  addFrame({ image, duration: 0.10, highlights, taps: [{ ...tap, scale: 1.16, alpha: 68, offsetY: -2 }], note: `${note} - pointer keluar 1` });
  addFrame({ image, duration: 0.10, highlights, taps: [{ ...tap, scale: 1.06, alpha: 56, offsetY: -1 }], note: `${note} - pointer keluar 2` });
  addFrame({ image, duration: 0.14, highlights, taps: [{ ...tap, scale: 0.88, alpha: 36 }], note: `${note} - stabil` });
}

function addInputSequence(emptyImage, filledImage, highlights, tap, note = '') {
  addFrame({ image: emptyImage, duration: 0.18, highlights, note: `${note} - kolom siap diisi` });
  if (tap) {
    addFrame({ image: emptyImage, duration: 0.12, highlights, taps: [{ ...tap, scale: 0.75, alpha: 32 }], note: `${note} - tap kolom` });
    addFrame({ image: emptyImage, duration: 0.12, highlights, taps: [{ ...tap, scale: 1.08, alpha: 58 }], note: `${note} - tap tekan` });
  }
  addFrame({ image: emptyImage, duration: 0.14, highlights, note: `${note} - jeda perpindahan` });
  addFrame({ image: filledImage, duration: 0.22, highlights, note: `${note} - data terisi` });
  addFrame({ image: filledImage, duration: 0.18, highlights, note: `${note} - stabil` });
}

function addSelectionSequence(emptyImage, filledImage, highlights, tap, note = '') {
  addFrame({ image: emptyImage, duration: 0.18, highlights, note: `${note} - sebelum memilih` });
  addFrame({ image: emptyImage, duration: 0.12, highlights, taps: [{ ...tap, scale: 0.8, alpha: 32 }], note: `${note} - tap pilihan` });
  addFrame({ image: emptyImage, duration: 0.14, highlights, note: `${note} - jeda perpindahan` });
  addFrame({ image: filledImage, duration: 0.22, highlights, note: `${note} - pilihan terisi` });
  addFrame({ image: filledImage, duration: 0.18, highlights, note: `${note} - stabil` });
}

function addScrollSequence(fromImage, toImage, direction = 'down', note = '') {
  addFrame({ image: fromImage, duration: 0.18, arrows: [{ x: 830, y: 1380, direction }], note: `${note} - mulai scroll` });
  addFrame({ image: fromImage, duration: 0.10, arrows: [{ x: 830, y: 1342, direction, scale: 0.9, alpha: 26 }], note: `${note} - transisi scroll 1` });
  addFrame({ image: fromImage, duration: 0.10, arrows: [{ x: 830, y: 1300, direction, scale: 0.98, alpha: 34 }], note: `${note} - transisi scroll 2` });
  addFrame({ image: toImage, duration: 0.10, arrows: [{ x: 830, y: 1250, direction, scale: 1.02, alpha: 42 }], note: `${note} - transisi scroll 3` });
  addFrame({ image: toImage, duration: 0.10, arrows: [{ x: 830, y: 1200, direction, scale: 1.04, alpha: 52 }], note: `${note} - transisi scroll 4` });
  addFrame({ image: toImage, duration: 0.12, arrows: [{ x: 830, y: 1150, direction, scale: 1.0, alpha: 44 }], note: `${note} - setelah scroll` });
  addFrame({ image: toImage, duration: 0.14, arrows: [{ x: 830, y: 1120, direction, scale: 0.96, alpha: 34 }], note: `${note} - stabil` });
}

// Semua koordinat memakai basis gambar 954 x 1920.
const HIGHLIGHTS = {
  publicMenuButton: [838, 26, 88, 88],
  publicRegisterButton: [30, 890, 896, 76],
  publicHomeHero: [30, 665, 895, 640],
  burger: [840, 26, 86, 86],
  nameField: [66, 586, 822, 88],
  emailField: [66, 759, 822, 88],
  roleField: [66, 931, 822, 88],
  roleOption: [92, 1152, 768, 72],
  npmField: [66, 1103, 822, 88],
  jurusanField: [66, 1315, 822, 88],
  jurusanDropdown: [66, 790, 822, 508],
  jurusanOption: [92, 992, 768, 72],
  prodiField: [66, 1504, 822, 88],
  prodiDropdown: [66, 966, 822, 508],
  prodiOption: [92, 1170, 768, 72],
  photoPickerButton: [362, 693, 170, 72],
  photoPickerSheet: [48, 1145, 858, 720],
  photoSelected: [96, 693, 756, 110],
  passwordField: [66, 347, 822, 88],
  confirmPasswordField: [66, 596, 822, 88],
  registerSubmit: [66, 724, 822, 76],
  loginEmailField: [66, 588, 822, 86],
  loginPasswordField: [66, 752, 822, 86],
  nameEmail: [60, 515, 835, 330],
  roleNpm: [60, 890, 835, 365],
  academicTop: [60, 0, 835, 410],
  photoRegister: [60, 465, 835, 440],
  password: [60, 0, 835, 410],
  registerButton: [60, 330, 835, 96],
  verifyCard: [30, 206, 895, 493],
  resendButton: [66, 486, 822, 74],
  inboxMailItem: [44, 464, 842, 168],
  spamMailItem: [44, 517, 842, 84],
  mailVerifyButton: [77, 700, 775, 76],
  loginFields: [60, 420, 835, 370],
  loginButton: [66, 1000, 822, 76],
  accountTrigger: [793, 14, 132, 76],
  dashboardHero: [30, 145, 895, 392],
  menuPanel: [159, 130, 638, 650],
  menuSettings: [190, 622, 240, 72],
  profileIdentity: [62, 361, 830, 148],
  profileAcademic: [60, 160, 835, 430],
  profileAcademicJurusan: [62, 140, 830, 86],
  profileAcademicProdi: [62, 315, 830, 86],
  profilePhoto: [116, 310, 175, 74],
  profilePhotoPickerSheet: [48, 1145, 858, 720],
  profilePhotoSelected: [96, 293, 762, 108],
  saveButton: [62, 482, 830, 88],
};

// Opening dibuat tetap seperti rekaman layar: tidak ada kartu presentasi, hanya UI yang muncul.
addPause(assets.publicHome, 1.0, 'Opening beranda publik');
addHighlightSequence(assets.publicHome, [HIGHLIGHTS.publicHomeHero], 'Beranda publik ULT FKIP Unila', 0.7);
addTapSequence(assets.publicHome, { x: 882, y: 60, size: 74 }, [HIGHLIGHTS.publicMenuButton], 'Tap menu beranda publik');
addPause(assets.publicHomeMenu, 0.55, 'Menu beranda publik terbuka');
addTapSequence(assets.publicHomeMenu, { x: 477, y: 928, size: 86 }, [HIGHLIGHTS.publicRegisterButton], 'Tap tombol Daftar dari beranda');
addPause(assets.registerStepEmpty, 0.9, 'Halaman daftar terbuka');

addInputSequence(assets.registerStepEmpty, assets.registerStepName, [HIGHLIGHTS.nameField], { x: 235, y: 630, size: 74 }, 'Isi field nama lengkap');
addInputSequence(assets.registerStepName, assets.registerStepEmail, [HIGHLIGHTS.emailField], { x: 235, y: 803, size: 74 }, 'Isi field email aktif');
addTapSequence(assets.registerStepEmail, { x: 832, y: 975, size: 74 }, [HIGHLIGHTS.roleField], 'Buka dropdown jenis akun');
addPause(assets.registerDropdownRole, 0.65, 'Dropdown jenis akun tampil');
addTapSequence(assets.registerDropdownRole, { x: 190, y: 1190, size: 74 }, [HIGHLIGHTS.roleOption], 'Pilih jenis akun Mahasiswa');
addInputSequence(assets.registerStepRole, assets.registerStepNpm, [HIGHLIGHTS.npmField], { x: 235, y: 1152, size: 74 }, 'Isi field NPM');
addTapSequence(assets.registerStepNpm, { x: 832, y: 1358, size: 74 }, [HIGHLIGHTS.jurusanField], 'Buka dropdown jurusan');
addPause(assets.registerDropdownJurusan, 0.85, 'Dropdown jurusan tampil');
addTapSequence(assets.registerDropdownJurusan, { x: 235, y: 1022, size: 76 }, [HIGHLIGHTS.jurusanOption], 'Pilih jurusan Ilmu Pendidikan');
addTapSequence(assets.registerStepJurusan, { x: 832, y: 1548, size: 74 }, [HIGHLIGHTS.prodiField], 'Buka dropdown program studi');
addPause(assets.registerDropdownProdi, 0.85, 'Dropdown program studi tampil');
addTapSequence(assets.registerDropdownProdi, { x: 330, y: 1200, size: 76 }, [HIGHLIGHTS.prodiOption], 'Pilih program studi Bimbingan dan Konseling');
addScrollSequence(assets.registerStepProdi, assets.registerPhotoBeforePick, 'down', 'Scroll ke foto profil');
addTapSequence(assets.registerPhotoBeforePick, { x: 446, y: 730, size: 78 }, [HIGHLIGHTS.photoPickerButton], 'Tap tombol pilih file foto');
addPause(assets.registerPhotoPicker, 0.85, 'Dialog pemilihan foto tampil');
addTapSequence(assets.registerPhotoPicker, { x: 675, y: 1775, size: 82 }, [HIGHLIGHTS.photoPickerSheet], 'Pilih file foto profil');
addPause(assets.registerPhotoSelected, 0.75, 'Foto profil sudah terpilih');
addScrollSequence(assets.registerPhotoSelected, assets.registerPasswordEmpty, 'down', 'Scroll ke password');
addInputSequence(assets.registerPasswordEmpty, assets.registerPasswordFilled, [HIGHLIGHTS.passwordField], { x: 235, y: 391, size: 74 }, 'Isi field password');
addInputSequence(assets.registerPasswordFilled, assets.registerConfirmPasswordFilled, [HIGHLIGHTS.confirmPasswordField], { x: 235, y: 640, size: 74 }, 'Isi field konfirmasi password');
addTapSequence(assets.registerConfirmPasswordFilled, { x: 492, y: 762, size: 82 }, [HIGHLIGHTS.registerSubmit], 'Tap tombol Daftar');

addPause(assets.verify, 0.8, 'Masuk halaman verifikasi email');
addHighlightSequence(assets.verify, [HIGHLIGHTS.verifyCard], 'Halaman verifikasi email', 0.75);
addPause(assets.inbox, 0.6, 'Buka inbox email');
addTapSequence(assets.inbox, { x: 250, y: 552, size: 82 }, [HIGHLIGHTS.inboxMailItem], 'Tap email verifikasi');
addPause(assets.spam, 0.8, 'Cek spam atau junk');
addTapSequence(assets.spam, { x: 245, y: 560, size: 82 }, [HIGHLIGHTS.spamMailItem], 'Tap folder spam');
addPause(assets.mail, 0.7, 'Buka email verifikasi');
addTapSequence(assets.mail, { x: 465, y: 738, size: 86 }, [HIGHLIGHTS.mailVerifyButton], 'Tap tautan verifikasi');
addTapSequence(assets.verify, { x: 477, y: 523, size: 82 }, [HIGHLIGHTS.resendButton], 'Tap kirim ulang bila perlu');

addPause(assets.loginEmpty, 0.9, 'Masuk halaman login');
addInputSequence(assets.loginEmpty, assets.loginEmailFilled, [HIGHLIGHTS.loginEmailField], { x: 250, y: 632, size: 74 }, 'Isi field email login');
addInputSequence(assets.loginEmailFilled, assets.loginPasswordFilled, [HIGHLIGHTS.loginPasswordField], { x: 250, y: 795, size: 74 }, 'Isi field password login');
addTapSequence(assets.loginPasswordFilled, { x: 477, y: 1038, size: 82 }, [HIGHLIGHTS.loginButton], 'Tap tombol Masuk');

addPause(assets.dashboard, 1.0, 'Dashboard pemohon terbuka');
addHighlightSequence(assets.dashboard, [HIGHLIGHTS.dashboardHero], 'Dashboard pemohon', 0.75);
addTapSequence(assets.dashboard, { x: 858, y: 52, size: 74 }, [HIGHLIGHTS.accountTrigger], 'Tap avatar akun');
addPause(assets.menu, 0.7, 'Menu akun terbuka');
addTapSequence(assets.menu, { x: 285, y: 655, size: 74 }, [HIGHLIGHTS.menuSettings], 'Tap Pengaturan');

addPause(assets.profileTop, 0.9, 'Halaman pengaturan profil');
addHighlightSequence(assets.profileTop, [HIGHLIGHTS.profileIdentity], 'Cek identitas profil', 0.85);
addScrollSequence(assets.profileTop, assets.profilePhoto, 'down', 'Scroll ke area foto profil');
addHighlightSequence(assets.profilePhoto, [HIGHLIGHTS.profilePhoto], 'Foto profil sesuai UI asli', 0.8);
addTapSequence(assets.profilePhoto, { x: 203, y: 347, size: 78 }, [HIGHLIGHTS.profilePhoto], 'Tap pilih file profil');
addPause(assets.profilePhotoPicker, 0.8, 'Dialog pilih file profil tampil');
addTapSequence(assets.profilePhotoPicker, { x: 675, y: 1775, size: 82 }, [HIGHLIGHTS.profilePhotoPickerSheet], 'Pilih file foto profil di halaman profil');
addHighlightSequence(assets.profilePhotoSelected, [HIGHLIGHTS.profilePhotoSelected], 'File foto profil sudah terpilih', 0.75);
addScrollSequence(assets.profilePhotoSelected, assets.profileSave, 'down', 'Scroll ke data akademik dan simpan');
addHighlightSequence(assets.profileSave, [HIGHLIGHTS.profileAcademicJurusan, HIGHLIGHTS.profileAcademicProdi], 'Cek jurusan dan program studi', 0.8);
addTapSequence(assets.profileSave, { x: 477, y: 526, size: 86 }, [HIGHLIGHTS.saveButton], 'Tap Simpan Perubahan');
addPause(assets.dashboard, 1.1, 'Akun siap mengajukan layanan');

function validateTapTargets() {
  const misses = [];

  frames.forEach((frame, frameIndex) => {
    frame.taps.forEach((tap) => {
      const insideTarget = frame.highlights.some(([x, y, w, h]) => (
        tap.x >= x && tap.x <= x + w && tap.y >= y && tap.y <= y + h
      ));

      if (!insideTarget) {
        misses.push(`Frame ${frameIndex + 1}: ${frame.note} (${tap.x}, ${tap.y})`);
      }
    });
  });

  if (misses.length > 0) {
    throw new Error(`Koordinat tap berada di luar highlight target:\n${misses.join('\n')}`);
  }
}

function addBackground(slide, image) {
  slide.addImage({ path: image, x: 0, y: 0, w: W, h: H });
}

function addHighlight(slide, [x, y, w, h], index) {
  slide.addShape(pptx.ShapeType.roundRect, {
    x: px(x),
    y: px(y),
    w: px(w),
    h: px(h),
    rectRadius: 0.045,
    fill: { color: YELLOW, transparency: 90 },
    line: { color: YELLOW, transparency: 0, width: 2.2 },
    shadow: { type: 'outer', color: '000000', opacity: 0.14, blur: 1, angle: 45, distance: 0.5 },
  });
  // Stroke kedua membuat highlight terlihat rapi di atas UI terang.
  slide.addShape(pptx.ShapeType.roundRect, {
    x: px(x - 3),
    y: px(y - 3),
    w: px(w + 6),
    h: px(h + 6),
    rectRadius: 0.05,
    fill: { color: YELLOW, transparency: 100 },
    line: { color: PURPLE, transparency: 35 + index * 8, width: 0.9 },
  });
}

function addTap(slide, tap) {
  const scale = tap.scale || 1;
  const markerSize = Math.max(14, Math.min(tap.size * scale * 0.26, 28));
  const shiftX = tap.offsetX || 0;
  const shiftY = tap.offsetY || 0;
  const markerX = tap.x + shiftX - markerSize / 2;
  const markerY = tap.y + shiftY - markerSize / 2;
  const iconSize = Math.max(markerSize * 2.2, 34);
  const iconX = tap.x + shiftX - iconSize / 2;
  const iconY = tap.y + shiftY - iconSize / 2;
  const fillTransparency = Math.max(0, Math.min((tap.alpha || 45) - 12, 72));

  slide.addImage({
    path: tapPointerIcon,
    x: px(iconX),
    y: px(iconY),
    w: px(iconSize),
    h: px(iconSize),
    transparency: fillTransparency,
  });
}

function addArrow(slide, arrow) {
  const scale = arrow.scale || 1;
  const width = 60 * scale;
  const height = 60 * scale;
  const x = arrow.x - width / 2;
  const y = arrow.y - height / 2;
  const fillTransparency = Math.max(8, Math.min((arrow.alpha || 34) - 8, 50));
  slide.addImage({
    path: scrollArrowIcon,
    x: px(x + (arrow.offsetX || 0)),
    y: px(y + (arrow.offsetY || 0)),
    w: px(width),
    h: px(height),
    transparency: fillTransparency,
  });
}

function setTimingXml(fileBuffer) {
  return JSZip.loadAsync(fileBuffer).then(async (zip) => {
    for (let i = 1; i <= frames.length; i += 1) {
      const duration = Math.max(0.12, (frames[i - 1].duration || 0.55) * 0.92);
      const file = `ppt/slides/slide${i}.xml`;
      let xml = await zip.file(file).async('string');
      const transitionXml = `<p:transition spd="fast" advClick="0" advTm="${Math.round(duration * 1000)}"><p:fade/></p:transition>`;
      if (!xml.includes('<p:transition')) {
        if (xml.includes('</p:clrMapOvr>')) {
          xml = xml.replace('</p:clrMapOvr>', `</p:clrMapOvr>${transitionXml}`);
        } else {
          xml = xml.replace('</p:cSld>', `</p:cSld><p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr>${transitionXml}`);
        }
      }
      zip.file(file, xml);
    }
    return zip.generateAsync({
      type: 'nodebuffer',
      compression: 'DEFLATE',
      compressionOptions: { level: 6 },
    });
  });
}

validateTapTargets();

frames.forEach((frame, index) => {
  const slide = pptx.addSlide();
  slide.background = { color: 'F8F4FF' };
  addBackground(slide, frame.image);
  if (frame.blendImage) {
    slide.addImage({
      path: frame.blendImage,
      x: px(frame.blendXOffset || 0),
      y: px(frame.blendYOffset || 0),
      w: W * (frame.blendScale || 1),
      h: H * (frame.blendScale || 1),
      transparency: frame.blendTransparency,
    });
  }
  if (frame.dim) {
    slide.addShape(pptx.ShapeType.rect, {
      x: 0,
      y: 0,
      w: W,
      h: H,
      fill: { color: '000000', transparency: 82 },
      line: { color: '000000', transparency: 100 },
    });
  }
  frame.highlights.forEach((h, i) => addHighlight(slide, h, i));
  frame.arrows.forEach((a) => addArrow(slide, a));
  frame.taps.forEach((t) => addTap(slide, t));
  slide.addNotes(`Frame ${index + 1}. ${frame.note}. Durasi ${frame.duration} detik.`);
});

await pptx.writeFile({ fileName: out });
const enhanced = await setTimingXml(fs.readFileSync(out));
fs.writeFileSync(out, enhanced);

console.log(out);
console.log(`frames=${frames.length}`);
