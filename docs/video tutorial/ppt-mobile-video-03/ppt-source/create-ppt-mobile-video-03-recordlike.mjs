import pptxgen from 'pptxgenjs';
import JSZip from 'jszip';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const screenshots = path.join(root, 'screenshots');
const out = path.join(__dirname, 'video-03-cara-mengajukan-layanan-mobile-clean-pointer-fixed.pptx');
const tapPointerIcon = path.join(__dirname, 'tap-pointer.svg');
const scrollArrowIcon = path.join(__dirname, 'scroll-arrow-down.svg');

const pptx = new pptxgen();
pptx.author = 'Codex';
pptx.company = 'ULT FKIP Unila';
pptx.subject = 'Panduan Mahasiswa Video 3';
pptx.title = 'Panduan Mahasiswa: Cara Mengajukan Layanan';
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
const YELLOW = 'FFD84D';

const assets = {
  dashboard: path.join(screenshots, '00-dashboard.png'),
  layananTop: path.join(screenshots, '01-layanan-index-top.png'),
  layananCard: path.join(screenshots, '02-layanan-index-card.png'),
  detailTop: path.join(screenshots, '03-detail-layanan-top.png'),
  detailSyarat: path.join(screenshots, '04-detail-layanan-syarat.png'),
  detailApply: path.join(screenshots, '04-detail-layanan-ajukan.png'),
  formEmpty: path.join(screenshots, '05-form-top-empty.png'),
  formInstansi: path.join(screenshots, '06-form-field-instansi.png'),
  formJabatan: path.join(screenshots, '07-form-field-jabatan.png'),
  formKota: path.join(screenshots, '08-form-field-kota.png'),
  formSemester: path.join(screenshots, '09-form-field-semester.png'),
  attachmentEmpty: path.join(screenshots, '10-form-attachment-empty.png'),
  attachmentPicker: path.join(screenshots, '11-form-attachment-picker.png'),
  attachmentSelected: path.join(screenshots, '12-form-attachment-selected.png'),
  submit: path.join(screenshots, '13-form-submit.png'),
  success: path.join(screenshots, '14-requests-success.png'),
};

for (const file of [...Object.values(assets), tapPointerIcon, scrollArrowIcon]) {
  if (!fs.existsSync(file)) throw new Error(`Aset tidak ditemukan: ${file}`);
}

const px = (value) => value / 96;
const frames = [];

function addFrame({ image, duration = 0.7, highlights = [], taps = [], arrows = [], note = '' }) {
  frames.push({ image, duration, highlights, taps, arrows, note });
}

function addPause(image, duration = 0.75, note = '') {
  addFrame({ image, duration, note });
}

function addHighlightSequence(image, highlights, note = '', duration = 0.5) {
  addFrame({ image, duration: 0.18, note: `${note} - normal` });
  addFrame({ image, duration, highlights, note: `${note} - highlight` });
  addFrame({ image, duration: 0.14, highlights, note: `${note} - stabil` });
}

function addTapSequence(image, tap, highlights = [], note = '') {
  addFrame({ image, duration: 0.16, highlights, note: `${note} - sebelum tap` });
  addFrame({ image, duration: 0.09, highlights, taps: [{ ...tap, scale: 0.56, alpha: 20, offsetY: 4 }], note: `${note} - pointer masuk 1` });
  addFrame({ image, duration: 0.09, highlights, taps: [{ ...tap, scale: 0.76, alpha: 32, offsetY: 2 }], note: `${note} - pointer masuk 2` });
  addFrame({ image, duration: 0.09, highlights, taps: [{ ...tap, scale: 0.96, alpha: 48 }], note: `${note} - tekan 1` });
  addFrame({ image, duration: 0.09, highlights, taps: [{ ...tap, scale: 1.12, alpha: 62, offsetY: -1 }], note: `${note} - tekan 2` });
  addFrame({ image, duration: 0.09, highlights, taps: [{ ...tap, scale: 1.2, alpha: 68, offsetY: -2 }], note: `${note} - pantul 1` });
  addFrame({ image, duration: 0.09, highlights, taps: [{ ...tap, scale: 1.08, alpha: 54, offsetY: -1 }], note: `${note} - pantul 2` });
  addFrame({ image, duration: 0.12, highlights, taps: [{ ...tap, scale: 0.88, alpha: 34 }], note: `${note} - stabil` });
}

function addInputSequence(emptyImage, filledImage, highlights, tap, note = '') {
  addFrame({ image: emptyImage, duration: 0.16, highlights, note: `${note} - fokus field` });
  addFrame({ image: emptyImage, duration: 0.1, highlights, taps: [{ ...tap, scale: 0.74, alpha: 32 }], note: `${note} - tap field` });
  addFrame({ image: emptyImage, duration: 0.1, highlights, taps: [{ ...tap, scale: 1.08, alpha: 58 }], note: `${note} - tekan field` });
  addFrame({ image: filledImage, duration: 0.22, highlights, note: `${note} - data terisi` });
  addFrame({ image: filledImage, duration: 0.16, highlights, note: `${note} - stabil` });
}

function addScrollSequence(fromImage, toImage, note = '', x = 835, y = 1430) {
  addFrame({ image: fromImage, duration: 0.14, arrows: [{ x, y, scale: 0.86, alpha: 24 }], note: `${note} - mulai scroll` });
  addFrame({ image: fromImage, duration: 0.09, arrows: [{ x, y: y - 46, scale: 0.94, alpha: 32 }], note: `${note} - scroll 1` });
  addFrame({ image: fromImage, duration: 0.09, arrows: [{ x, y: y - 92, scale: 1.02, alpha: 42 }], note: `${note} - scroll 2` });
  addFrame({ image: toImage, duration: 0.09, arrows: [{ x, y: y - 140, scale: 1.06, alpha: 50 }], note: `${note} - scroll 3` });
  addFrame({ image: toImage, duration: 0.1, arrows: [{ x, y: y - 188, scale: 1.02, alpha: 42 }], note: `${note} - scroll 4` });
  addFrame({ image: toImage, duration: 0.12, arrows: [{ x, y: y - 230, scale: 0.94, alpha: 30 }], note: `${note} - stabil` });
}

// Koordinat memakai basis screenshot 954 x 1920.
const HIGHLIGHTS = {
  dashboardAjukan: [69, 407, 815, 89],
  layananHeroScroll: [340, 1792, 274, 80],
  layananSearch: [30, 336, 782, 94],
  layananCard: [28, 558, 898, 493],
  layananDetailButton: [60, 937, 150, 84],
  detailHero: [190, 780, 575, 340],
  detailSyarat: [58, 532, 838, 710],
  detailAjukan: [58, 663, 838, 91],
  formHeader: [58, 808, 838, 850],
  fieldInstansi: [58, 1054, 838, 88],
  fieldJabatan: [58, 1224, 838, 88],
  fieldKota: [58, 1396, 838, 88],
  fieldSemester: [58, 1570, 838, 88],
  lampiranCard: [58, 496, 838, 454],
  fileButton: [100, 747, 168, 72],
  filePickerSheet: [48, 1128, 858, 702],
  fileSelected: [86, 735, 782, 98],
  submitButton: [58, 432, 838, 92],
  successAlert: [58, 138, 838, 140],
  successList: [58, 329, 838, 323],
};

addPause(assets.dashboard, 0.75, 'Dashboard mahasiswa sebagai titik awal');
addHighlightSequence(assets.dashboard, [HIGHLIGHTS.dashboardAjukan], 'Tombol ajukan layanan di dashboard');
addTapSequence(assets.dashboard, { x: 477, y: 452, size: 82 }, [HIGHLIGHTS.dashboardAjukan], 'Tap ajukan layanan');

addPause(assets.layananTop, 0.55, 'Halaman daftar layanan terbuka');
addHighlightSequence(assets.layananTop, [HIGHLIGHTS.layananHeroScroll], 'Tombol cari layanan di hero');
addTapSequence(assets.layananTop, { x: 477, y: 1832, size: 78 }, [HIGHLIGHTS.layananHeroScroll], 'Tap tombol Cari layanan');
addScrollSequence(assets.layananTop, assets.layananCard, 'Scroll ke katalog layanan', 835, 1510);
addPause(assets.layananCard, 0.55, 'Hasil pencarian layanan tampil');
addHighlightSequence(assets.layananCard, [HIGHLIGHTS.layananSearch], 'Kolom pencarian sudah berisi Pra Penelitian');
addHighlightSequence(assets.layananCard, [HIGHLIGHTS.layananCard], 'Kartu Surat Persetujuan Pra Penelitian tampil');
addTapSequence(assets.layananCard, { x: 135, y: 979, size: 82 }, [HIGHLIGHTS.layananDetailButton], 'Tap tombol Detail layanan');

addPause(assets.detailTop, 0.65, 'Detail layanan terbuka');
addHighlightSequence(assets.detailTop, [HIGHLIGHTS.detailHero], 'Baca informasi layanan');
addScrollSequence(assets.detailTop, assets.detailSyarat, 'Scroll ke syarat layanan', 842, 1420);
addHighlightSequence(assets.detailSyarat, [HIGHLIGHTS.detailSyarat], 'Cek syarat dan ketentuan layanan');
addScrollSequence(assets.detailSyarat, assets.detailApply, 'Scroll ke tombol ajukan layanan', 842, 1510);
addTapSequence(assets.detailApply, { x: 477, y: 710, size: 88 }, [HIGHLIGHTS.detailAjukan], 'Tap Ajukan layanan');

addPause(assets.formEmpty, 0.65, 'Form permohonan terbuka');
addHighlightSequence(assets.formEmpty, [HIGHLIGHTS.formHeader], 'Form mengikuti kebutuhan layanan');
addInputSequence(assets.formEmpty, assets.formInstansi, [HIGHLIGHTS.fieldInstansi], { x: 260, y: 1098, size: 74 }, 'Isi Penerima Instansi');
addInputSequence(assets.formInstansi, assets.formJabatan, [HIGHLIGHTS.fieldJabatan], { x: 260, y: 1268, size: 74 }, 'Isi Penerima Jabatan');
addInputSequence(assets.formJabatan, assets.formKota, [HIGHLIGHTS.fieldKota], { x: 260, y: 1440, size: 74 }, 'Isi Penerima Kota');
addInputSequence(assets.formKota, assets.formSemester, [HIGHLIGHTS.fieldSemester], { x: 260, y: 1614, size: 74 }, 'Isi Semester');

addScrollSequence(assets.formSemester, assets.attachmentEmpty, 'Scroll ke lampiran umum', 835, 1510);
addHighlightSequence(assets.attachmentEmpty, [HIGHLIGHTS.lampiranCard], 'Lampiran pendukung jika diperlukan');
addTapSequence(assets.attachmentEmpty, { x: 185, y: 783, size: 78 }, [HIGHLIGHTS.fileButton], 'Tap pilih file lampiran');
addPause(assets.attachmentPicker, 0.6, 'Dialog pilih file lampiran tampil');
addTapSequence(assets.attachmentPicker, { x: 675, y: 1772, size: 82 }, [HIGHLIGHTS.filePickerSheet], 'Pilih dokumen lampiran');
addHighlightSequence(assets.attachmentSelected, [HIGHLIGHTS.fileSelected], 'File lampiran sudah terpilih');
addScrollSequence(assets.attachmentSelected, assets.submit, 'Scroll ke tombol submit', 835, 1390);
addTapSequence(assets.submit, { x: 477, y: 478, size: 90 }, [HIGHLIGHTS.submitButton], 'Tap tombol Submit');

addPause(assets.success, 0.75, 'Halaman permohonan setelah submit');
addHighlightSequence(assets.success, [HIGHLIGHTS.successAlert], 'Notifikasi berhasil muncul');
addHighlightSequence(assets.success, [HIGHLIGHTS.successList], 'Permohonan tampil di daftar');

function validateTapTargets() {
  const misses = [];
  frames.forEach((frame, frameIndex) => {
    frame.taps.forEach((tap) => {
      const insideTarget = frame.highlights.some(([x, y, w, h]) => (
        tap.x >= x && tap.x <= x + w && tap.y >= y && tap.y <= y + h
      ));
      if (!insideTarget) misses.push(`Frame ${frameIndex + 1}: ${frame.note} (${tap.x}, ${tap.y})`);
    });
  });
  if (misses.length > 0) throw new Error(`Koordinat tap berada di luar highlight target:\n${misses.join('\n')}`);
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
  const markerSize = Math.max(14, Math.min(tap.size * scale * 0.26, 30));
  const shiftX = tap.offsetX || 0;
  const shiftY = tap.offsetY || 0;
  const iconSize = Math.max(markerSize * 2.2, 34);
  slide.addImage({
    path: tapPointerIcon,
    x: px(tap.x + shiftX - iconSize / 2),
    y: px(tap.y + shiftY - iconSize / 2),
    w: px(iconSize),
    h: px(iconSize),
    transparency: Math.max(0, Math.min((tap.alpha || 45) - 12, 72)),
  });
}

function addArrow(slide, arrow) {
  const scale = arrow.scale || 1;
  const width = 60 * scale;
  const height = 60 * scale;
  slide.addImage({
    path: scrollArrowIcon,
    x: px(arrow.x - width / 2),
    y: px(arrow.y - height / 2),
    w: px(width),
    h: px(height),
    transparency: Math.max(8, Math.min((arrow.alpha || 34) - 8, 50)),
  });
}

async function enhancePackage(fileBuffer) {
  const zip = await JSZip.loadAsync(fileBuffer);

  const contentTypesFile = '[Content_Types].xml';
  let contentTypesXml = await zip.file(contentTypesFile).async('string');
  contentTypesXml = contentTypesXml.replace(
    /\s*<Override PartName="\/ppt\/slideMasters\/slideMaster(?!1\.xml")\d+\.xml" ContentType="application\/vnd\.openxmlformats-officedocument\.presentationml\.slideMaster\+xml"\/>/g,
    '',
  );
  zip.file(contentTypesFile, contentTypesXml);

  for (let i = 1; i <= frames.length; i += 1) {
    const duration = Math.max(0.12, (frames[i - 1].duration || 0.55) * 0.9);
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
}

validateTapTargets();

frames.forEach((frame, index) => {
  const slide = pptx.addSlide();
  slide.background = { color: 'F8F4FF' };
  addBackground(slide, frame.image);
  frame.highlights.forEach((highlight, i) => addHighlight(slide, highlight, i));
  frame.arrows.forEach((arrow) => addArrow(slide, arrow));
  frame.taps.forEach((tap) => addTap(slide, tap));
  slide.addNotes(`Frame ${index + 1}. ${frame.note}. Durasi ${frame.duration} detik.`);
});

await pptx.writeFile({ fileName: out });
const enhanced = await enhancePackage(fs.readFileSync(out));
fs.writeFileSync(out, enhanced);

console.log(out);
console.log(`frames=${frames.length}`);
