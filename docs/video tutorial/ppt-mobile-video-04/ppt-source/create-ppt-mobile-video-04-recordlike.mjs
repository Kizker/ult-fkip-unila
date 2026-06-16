import pptxgen from 'pptxgenjs';
import JSZip from 'jszip';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const screenshots = path.join(root, 'screenshots');
const out = path.join(__dirname, 'video-04-status-revisi-unduh-mobile-clean-pointer-v16.pptx');

// Reuse pointer assets from Video 03 so this PPT keeps the same screencast style.
const tapPointerIcon = path.resolve(__dirname, '../../ppt-mobile-video-03/ppt-source/tap-pointer.svg');
const scrollArrowIcon = path.resolve(__dirname, '../../ppt-mobile-video-03/ppt-source/scroll-arrow-down.svg');

const pptx = new pptxgen();
pptx.author = 'Codex';
pptx.company = 'ULT FKIP Unila';
pptx.subject = 'Panduan Mahasiswa Video 4';
pptx.title = 'Panduan Mahasiswa: Cara Cek Status, Revisi, dan Unduh Hasil Layanan';
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
  riwayatList: path.join(screenshots, '01-riwayat-permohonan-list.png'),
  riwayatListStatusScrolled: path.join(screenshots, '01b-riwayat-permohonan-list-scroll-status.png'),
  detailDiajukan: path.join(screenshots, '02-detail-diajukan.png'),
  detailDiverifikasi: path.join(screenshots, '03-detail-diverifikasi-unit.png'),
  detailReview: path.join(screenshots, '04-detail-review-ult.png'),
  detailMenungguTTD: path.join(screenshots, '05-detail-menunggu-ttd.png'),
  detailNomorTerbit: path.join(screenshots, '06-detail-nomor-terbit.png'),
  detailPenandatanganan: path.join(screenshots, '07-detail-penandatanganan.png'),
  detailDiproses: path.join(screenshots, '08-detail-diproses.png'),
  detailPerluPerbaikan: path.join(screenshots, '09-detail-perlu-perbaikan.png'),
  detailPerluPerbaikanScrolled: path.join(screenshots, '09b-detail-perlu-perbaikan-scroll-data.png'),
  detailDitolak: path.join(screenshots, '10-detail-ditolak.png'),
  detailSelesai: path.join(screenshots, '11-detail-selesai.png'),
  detailSelesaiActions: path.join(screenshots, '11c-detail-selesai-actions-clean.png'),
  formEdit: path.join(screenshots, '12-form-edit-revisi.png'),
  formEditFilled: path.join(screenshots, '12b-form-edit-revisi-filled.png'),
  formPicker: path.join(screenshots, '13c-form-edit-revisi-filepicker-centered-clean.png'),
  formBottom: path.join(screenshots, '14-form-edit-revisi-bottom.png'),
  formBottomNoteFilled: path.join(screenshots, '14b-form-edit-revisi-bottom-note-filled.png'),
  downloadNotif: path.join(screenshots, '15c-os-download-notification-actions-clean.png'),
  pdfPreview: path.join(screenshots, '16-pdf-preview.png'),
  pdfResultFinal: path.join(screenshots, '17-pdf-result-final.png'),
};

for (const file of [...Object.values(assets), tapPointerIcon, scrollArrowIcon]) {
  if (!fs.existsSync(file)) throw new Error(`Aset tidak ditemukan: ${file}`);
}

const px = (value) => value / 96;
const frames = [];

function addFrame({ image, duration = 0.7, highlights = [], taps = [], arrows = [], note = '' }) {
  frames.push({ image, duration, highlights, taps, arrows, note });
}

function addPause(image, duration = 1.0, note = '') {
  addFrame({ image, duration, note });
}

function addHighlightSequence(image, highlights, note = '', duration = 0.95) {
  addFrame({ image, duration: 0.28, note: `${note} - normal` });
  addFrame({ image, duration, highlights, note: `${note} - highlight` });
  addFrame({ image, duration: 0.28, highlights, note: `${note} - stabil` });
}

function addTapSequence(image, tap, highlights = [], note = '') {
  addFrame({ image, duration: 0.26, highlights, note: `${note} - sebelum tap` });
  addFrame({ image, duration: 0.11, highlights, taps: [{ ...tap, scale: 0.56, alpha: 20, offsetY: 4 }], note: `${note} - pointer masuk 1` });
  addFrame({ image, duration: 0.11, highlights, taps: [{ ...tap, scale: 0.76, alpha: 32, offsetY: 2 }], note: `${note} - pointer masuk 2` });
  addFrame({ image, duration: 0.11, highlights, taps: [{ ...tap, scale: 0.96, alpha: 48 }], note: `${note} - tekan 1` });
  addFrame({ image, duration: 0.11, highlights, taps: [{ ...tap, scale: 1.12, alpha: 62, offsetY: -1 }], note: `${note} - tekan 2` });
  addFrame({ image, duration: 0.11, highlights, taps: [{ ...tap, scale: 1.2, alpha: 68, offsetY: -2 }], note: `${note} - pantul 1` });
  addFrame({ image, duration: 0.11, highlights, taps: [{ ...tap, scale: 1.08, alpha: 54, offsetY: -1 }], note: `${note} - pantul 2` });
  addFrame({ image, duration: 0.2, highlights, taps: [{ ...tap, scale: 0.88, alpha: 34 }], note: `${note} - stabil` });
}

function addScrollSequence(fromImage, toImage, note = '', x = 835, y = 1430) {
  addFrame({ image: fromImage, duration: 0.22, arrows: [{ x, y, scale: 0.86, alpha: 24 }], note: `${note} - mulai scroll` });
  addFrame({ image: fromImage, duration: 0.16, arrows: [{ x, y: y - 46, scale: 0.94, alpha: 32 }], note: `${note} - scroll 1` });
  addFrame({ image: fromImage, duration: 0.16, arrows: [{ x, y: y - 92, scale: 1.02, alpha: 42 }], note: `${note} - scroll 2` });
  addFrame({ image: toImage, duration: 0.16, arrows: [{ x, y: y - 140, scale: 1.06, alpha: 50 }], note: `${note} - scroll 3` });
  addFrame({ image: toImage, duration: 0.18, arrows: [{ x, y: y - 188, scale: 1.02, alpha: 42 }], note: `${note} - scroll 4` });
  addFrame({ image: toImage, duration: 0.24, arrows: [{ x, y: y - 230, scale: 0.94, alpha: 30 }], note: `${note} - stabil` });
}

function addDataChangeSequence(beforeImage, afterImage, fieldHighlight, note = '') {
  addFrame({ image: beforeImage, duration: 0.24, highlights: [fieldHighlight], note: `${note} - data awal` });
  addFrame({ image: beforeImage, duration: 0.14, highlights: [fieldHighlight], note: `${note} - fokus perubahan` });
  addFrame({ image: afterImage, duration: 0.22, highlights: [fieldHighlight], note: `${note} - data berubah` });
  addFrame({ image: afterImage, duration: 0.55, highlights: [fieldHighlight], note: `${note} - hasil perubahan stabil` });
}

const HIGHLIGHTS = {
  riwayatHeader: [28, 144, 898, 531],
  riwayatCardSelesai: [28, 1021, 898, 360],
  riwayatBadgeSelesaiScrolled: [58, 959, 134, 50],
  riwayatBadgeDitolakScrolled: [58, 1349, 134, 50],
  riwayatCardSelesaiScrolled: [28, 931, 898, 360],
  detailHeader: [28, 144, 898, 450],
  badgeDiajukan: [90, 768, 158, 56],
  badgeDiverifikasi: [90, 704, 250, 56],
  badgeReview: [90, 768, 200, 56],
  badgeMenungguTTD: [90, 768, 350, 56],
  badgeNomorTerbit: [90, 704, 220, 56],
  badgePenandatanganan: [90, 704, 255, 56],
  badgeDiproses: [90, 704, 165, 56],
  badgePerluPerbaikan: [90, 768, 250, 56],
  badgeDitolak: [90, 704, 145, 56],
  actionCard: [58, 922, 838, 190],
  dataFirstField: [58, 1390, 838, 208],
  revisionDataVisibleScrolled: [28, 1378, 898, 330],
  catatanPerbaikanForm: [58, 986, 838, 505],
  catatanTextArea: [92, 1166, 770, 154],
  btnKirimPerbaikan: [92, 1383, 770, 76],
  fieldInstansi: [58, 550, 838, 84],
  fieldJabatan: [58, 714, 838, 92],
  filePickerButton: [72, 1607, 168, 72],
  osFileOption: [86, 856, 782, 143],
  osFilePickButton: [490, 1192, 378, 96],
  btnSimpanData: [58, 1165, 420, 74],
  completedDetailData: [58, 142, 838, 725],
  downloadButton: [58, 934, 838, 75],
  previewButton: [58, 1038, 838, 75],
  notifDownload: [24, 40, 906, 154],
  pdfDocument: [64, 177, 858, 1160],
  pdfResultDocument: [32, 88, 890, 1320],
};

const centerTap = (hBox, size = 88) => ({
  x: hBox[0] + hBox[2] / 2,
  y: hBox[1] + hBox[3] / 2,
  size,
});

// 1. Riwayat permohonan sebagai titik awal, mengikuti gaya video 03 yang langsung berupa screencast UI.
addPause(assets.riwayatList, 2.2, 'Halaman daftar riwayat permohonan terbuka setelah pengajuan dikirim');
addHighlightSequence(assets.riwayatList, [HIGHLIGHTS.riwayatHeader], 'Kenali halaman Permohonan Saya', 1.15);
addScrollSequence(assets.riwayatList, assets.riwayatListStatusScrolled, 'Scroll daftar untuk melihat beberapa status', 835, 1201);
addPause(assets.riwayatListStatusScrolled, 0.75, 'Daftar bergeser dan beberapa status terlihat');
addHighlightSequence(
  assets.riwayatListStatusScrolled,
  [
    HIGHLIGHTS.riwayatBadgeSelesaiScrolled,
    HIGHLIGHTS.riwayatBadgeDitolakScrolled,
  ],
  'Badge status yang terlihat penuh setelah daftar discroll',
  1.2,
);
addHighlightSequence(assets.riwayatListStatusScrolled, [HIGHLIGHTS.riwayatCardSelesaiScrolled], 'Kartu permohonan dapat dibuka untuk melihat detail', 1.0);

// 2. Detail permohonan dan status utama.
addPause(assets.detailDiajukan, 2.0, 'Detail permohonan menampilkan informasi utama, data, lampiran, catatan, dan riwayat status');
addHighlightSequence(assets.detailDiajukan, [HIGHLIGHTS.detailHeader], 'Identitas permohonan di halaman detail', 1.0);
addHighlightSequence(assets.detailDiajukan, [HIGHLIGHTS.badgeDiajukan], 'Status Diajukan', 1.2);
addHighlightSequence(assets.detailDiajukan, [HIGHLIGHTS.dataFirstField], 'Data permohonan dapat dicek dari halaman detail', 1.0);

addPause(assets.detailDiverifikasi, 1.6, 'Status Diverifikasi Unit');
addHighlightSequence(assets.detailDiverifikasi, [HIGHLIGHTS.badgeDiverifikasi], 'Badge Diverifikasi Unit', 0.95);
addPause(assets.detailReview, 1.6, 'Status Review ULT');
addHighlightSequence(assets.detailReview, [HIGHLIGHTS.badgeReview], 'Badge Review ULT', 0.95);
addPause(assets.detailMenungguTTD, 1.6, 'Status Menunggu TTD');
addHighlightSequence(assets.detailMenungguTTD, [HIGHLIGHTS.badgeMenungguTTD], 'Badge Menunggu TTD', 0.95);
addPause(assets.detailNomorTerbit, 1.6, 'Status Nomor Terbit');
addHighlightSequence(assets.detailNomorTerbit, [HIGHLIGHTS.badgeNomorTerbit], 'Badge Nomor Terbit', 0.95);
addPause(assets.detailPenandatanganan, 1.6, 'Status Penandatangan');
addHighlightSequence(assets.detailPenandatanganan, [HIGHLIGHTS.badgePenandatanganan], 'Badge Penandatangan', 0.95);
addPause(assets.detailDiproses, 1.8, 'Status Diproses');
addHighlightSequence(assets.detailDiproses, [HIGHLIGHTS.badgeDiproses], 'Badge Diproses', 1.05);

// 3. Revisi: pisahkan update data dan pengiriman perbaikan agar sesuai UI asli.
addPause(assets.detailPerluPerbaikan, 2.0, 'Status Perlu Perbaikan');
addHighlightSequence(assets.detailPerluPerbaikan, [HIGHLIGHTS.badgePerluPerbaikan], 'Badge Perlu Perbaikan', 1.1);
addHighlightSequence(assets.detailPerluPerbaikan, [HIGHLIGHTS.catatanPerbaikanForm], 'Area catatan perbaikan dan aksi kirim perbaikan', 1.15);
addScrollSequence(assets.detailPerluPerbaikan, assets.detailPerluPerbaikanScrolled, 'Scroll halaman detail ke data yang perlu diperbaiki', 835, 1410);
addPause(assets.detailPerluPerbaikanScrolled, 0.75, 'Data permohonan mulai terlihat setelah halaman discroll');
addHighlightSequence(assets.detailPerluPerbaikanScrolled, [HIGHLIGHTS.revisionDataVisibleScrolled], 'Data permohonan dapat diperbarui ketika status perlu perbaikan', 1.0);

addPause(assets.formEdit, 1.3, 'Bagian data permohonan siap diedit');
addHighlightSequence(assets.formEdit, [HIGHLIGHTS.fieldInstansi], 'Cek field Penerima Instansi', 0.9);
addTapSequence(assets.formEdit, centerTap(HIGHLIGHTS.fieldInstansi, 78), [HIGHLIGHTS.fieldInstansi], 'Tap field Penerima Instansi');
addDataChangeSequence(assets.formEdit, assets.formEditFilled, HIGHLIGHTS.fieldInstansi, 'Perubahan data Penerima Instansi');
addHighlightSequence(assets.formEditFilled, [HIGHLIGHTS.fieldJabatan], 'Cek field Penerima Jabatan setelah data diperbarui', 0.9);
addDataChangeSequence(assets.formEdit, assets.formEditFilled, HIGHLIGHTS.fieldJabatan, 'Perubahan data Penerima Jabatan');
addHighlightSequence(assets.formEditFilled, [HIGHLIGHTS.filePickerButton], 'Pilih lampiran pendukung baru jika diperlukan', 0.95);
addTapSequence(assets.formEditFilled, centerTap(HIGHLIGHTS.filePickerButton, 78), [HIGHLIGHTS.filePickerButton], 'Tap tombol Pilih file lampiran pendukung');
addPause(assets.formPicker, 1.1, 'Dialog file picker terbuka');
addHighlightSequence(assets.formPicker, [HIGHLIGHTS.osFileOption], 'Pilih file pengganti pada dialog lampiran', 0.85);
addTapSequence(assets.formPicker, centerTap(HIGHLIGHTS.osFilePickButton, 78), [HIGHLIGHTS.osFilePickButton], 'Tap tombol Pilih pada dialog lampiran');
addHighlightSequence(assets.formEditFilled, [HIGHLIGHTS.btnSimpanData], 'Klik Simpan perubahan data', 1.0);
addTapSequence(assets.formEditFilled, centerTap(HIGHLIGHTS.btnSimpanData, 84), [HIGHLIGHTS.btnSimpanData], 'Tap Simpan perubahan data');
addPause(assets.formBottom, 0.8, 'Kembali ke detail setelah data diperbarui');
addHighlightSequence(assets.formBottom, [HIGHLIGHTS.catatanTextArea], 'Isi catatan perbaikan sebelum mengirim perbaikan', 0.95);
addPause(assets.formBottomNoteFilled, 1.1, 'Catatan perbaikan sudah diisi');
addHighlightSequence(assets.formBottomNoteFilled, [HIGHLIGHTS.catatanTextArea], 'Catatan perbaikan terisi', 0.9);
addHighlightSequence(assets.formBottomNoteFilled, [HIGHLIGHTS.btnKirimPerbaikan], 'Klik Kirim perbaikan setelah semua data dan catatan diperbaiki', 1.1);
addTapSequence(assets.formBottomNoteFilled, centerTap(HIGHLIGHTS.btnKirimPerbaikan, 86), [HIGHLIGHTS.btnKirimPerbaikan], 'Tap Kirim perbaikan');

// 4. Status ditolak sebagai kondisi yang juga harus diketahui pemohon.
addPause(assets.detailDitolak, 1.7, 'Contoh status Ditolak');
addHighlightSequence(assets.detailDitolak, [HIGHLIGHTS.badgeDitolak], 'Badge Ditolak', 1.1);
addHighlightSequence(assets.detailDitolak, [HIGHLIGHTS.actionCard], 'Tidak ada aksi lanjutan pada permohonan ditolak', 0.9);

// 5. Selesai: mulai dari katalog, masuk detail, unduh berkas, lalu tampilkan dokumen final.
addPause(assets.riwayatList, 1.5, 'Katalog permohonan menampilkan permohonan yang sudah selesai');
addHighlightSequence(assets.riwayatList, [HIGHLIGHTS.riwayatCardSelesai], 'Pilih permohonan dengan status Selesai dari katalog', 1.0);
addTapSequence(assets.riwayatList, centerTap(HIGHLIGHTS.riwayatCardSelesai, 86), [HIGHLIGHTS.riwayatCardSelesai], 'Tap kartu permohonan selesai');
addPause(assets.detailSelesaiActions, 1.4, 'Detail permohonan selesai terbuka');
addHighlightSequence(assets.detailSelesaiActions, [HIGHLIGHTS.completedDetailData], 'Detail data permohonan selesai dapat dicek sebelum mengunduh', 0.95);
addHighlightSequence(
  assets.detailSelesaiActions,
  [HIGHLIGHTS.downloadButton, HIGHLIGHTS.previewButton],
  'Tersedia tombol Unduh berkas dan Buka preview',
  1.05,
);
addHighlightSequence(assets.detailSelesaiActions, [HIGHLIGHTS.downloadButton], 'Karena permohonan selesai, klik Unduh berkas', 1.05);
addTapSequence(assets.detailSelesaiActions, centerTap(HIGHLIGHTS.downloadButton, 86), [HIGHLIGHTS.downloadButton], 'Tap Unduh berkas');
addPause(assets.downloadNotif, 1.4, 'Berkas berhasil terunduh');
addHighlightSequence(assets.downloadNotif, [HIGHLIGHTS.notifDownload], 'Notifikasi file berhasil diunduh', 1.0);
addTapSequence(assets.downloadNotif, centerTap(HIGHLIGHTS.notifDownload, 86), [HIGHLIGHTS.notifDownload], 'Tap notifikasi untuk membuka file');
addPause(assets.pdfResultFinal, 2.4, 'Dokumen hasil layanan selesai terbuka lengkap');
addHighlightSequence(assets.pdfResultFinal, [HIGHLIGHTS.pdfResultDocument], 'Dokumen selesai terlihat lengkap dan siap disimpan', 1.2);
addPause(assets.pdfResultFinal, 2.2, 'Alur selesai setelah dokumen akhir berhasil dibuka');

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
    const duration = Math.max(0.16, frames[i - 1].duration || 0.55);
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
