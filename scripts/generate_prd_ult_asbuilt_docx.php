<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

$tz = new DateTimeZone('Asia/Jakarta');
$now = new DateTimeImmutable('now', $tz);
$dateIso = $now->format('Y-m-d');
$monthMap = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember',
];
$dateLabel = $now->format('d').' '.$monthMap[$now->format('m')].' '.$now->format('Y');

$docsDir = realpath(__DIR__.'/../docs') ?: (__DIR__.'/../docs');
$pattern = rtrim($docsDir, '/\\').DIRECTORY_SEPARATOR.'PRD_Web_ULT_FKIP_Unila_AsBuilt_'.$dateIso.'_v*.docx';
$existing = glob($pattern) ?: [];
$maxVersion = 0;
foreach ($existing as $filePath) {
    if (preg_match('/_v(\\d+)\\.docx$/', $filePath, $m)) {
        $maxVersion = max($maxVersion, (int) $m[1]);
    }
}
$docVersion = 'v'.($maxVersion + 1);
$outputPath = rtrim($docsDir, '/\\').DIRECTORY_SEPARATOR.'PRD_Web_ULT_FKIP_Unila_AsBuilt_'.$dateIso.'_'.$docVersion.'.docx';

$phpWord = new PhpWord();
$phpWord->setDefaultFontName('Times New Roman');
$phpWord->setDefaultFontSize(12);

$phpWord->addTitleStyle(1, ['bold' => true, 'size' => 18, 'name' => 'Times New Roman'], ['spaceAfter' => Converter::pointToTwip(8), 'lineHeight' => 1.5]);
$phpWord->addTitleStyle(2, ['bold' => true, 'size' => 14, 'name' => 'Times New Roman'], ['spaceBefore' => Converter::pointToTwip(8), 'spaceAfter' => Converter::pointToTwip(4), 'lineHeight' => 1.5]);
$phpWord->addTitleStyle(3, ['bold' => true, 'size' => 12, 'name' => 'Times New Roman'], ['spaceBefore' => Converter::pointToTwip(4), 'spaceAfter' => Converter::pointToTwip(2), 'lineHeight' => 1.5]);

$phpWord->addParagraphStyle('p', ['spaceAfter' => Converter::pointToTwip(6), 'lineHeight' => 1.5]);
$phpWord->addParagraphStyle('small', ['spaceAfter' => Converter::pointToTwip(4), 'lineHeight' => 1.5]);
$phpWord->addParagraphStyle('list', ['spaceAfter' => Converter::pointToTwip(2), 'lineHeight' => 1.5]);
$phpWord->addParagraphStyle('cover_center', ['alignment' => Jc::CENTER, 'spaceAfter' => Converter::pointToTwip(6), 'lineHeight' => 1.5]);

$phpWord->addNumberingStyle(
    'bullet_main',
    [
        'type' => 'multilevel',
        'levels' => [
            ['format' => 'bullet', 'text' => "\xE2\x80\xA2", 'left' => 720, 'hanging' => 360, 'tabPos' => 720],
        ],
    ]
);

$section = $phpWord->addSection([
    'pageSizeW' => 11906,
    'pageSizeH' => 16838,
    'marginTop' => (int) round(Converter::cmToTwip(2)),
    'marginRight' => (int) round(Converter::cmToTwip(2)),
    'marginBottom' => (int) round(Converter::cmToTwip(2)),
    'marginLeft' => (int) round(Converter::cmToTwip(2.2)),
]);

// Cover page
$section->addTextBreak(6);
$section->addText('PRODUCT REQUIREMENTS DOCUMENT (PRD)', ['bold' => true, 'size' => 18, 'name' => 'Times New Roman'], 'cover_center');
$section->addText('WEB ULT FKIP UNIVERSITAS LAMPUNG', ['bold' => true, 'size' => 14, 'name' => 'Times New Roman'], 'cover_center');
$section->addTextBreak(2);
$section->addText('Versi Dokumen: '.$docVersion, ['size' => 12, 'name' => 'Times New Roman'], 'cover_center');
$section->addText('Tanggal: '.$dateLabel, ['size' => 12, 'name' => 'Times New Roman'], 'cover_center');
$section->addText('Status: As-Built Baseline', ['size' => 12, 'name' => 'Times New Roman'], 'cover_center');
$section->addTextBreak(8);
$section->addText('ULT FKIP Universitas Lampung', ['bold' => true, 'size' => 12, 'name' => 'Times New Roman'], 'cover_center');
$section->addPageBreak();

$section->addTitle('Product Requirements Document (PRD)', 1);
$section->addText('Web ULT FKIP Universitas Lampung (As-Built Baseline)', null, 'p');
$section->addText('Tanggal dokumen: '.$dateLabel, ['italic' => true], 'small');
$section->addText('Status: baseline produk aktif (reverse PRD dari implementasi berjalan)', ['italic' => true], 'small');

$section->addTitle('1. Ringkasan Produk', 2);
$section->addText(
    'Web ULT FKIP Unila adalah platform layanan administrasi akademik dan dokumen yang menggabungkan portal publik, portal mahasiswa, dan portal operasional internal (admin, signer, staff final) dalam satu sistem berbasis Laravel.',
    null,
    'p'
);
$section->addText(
    'Dokumen ini merangkum kebutuhan produk berdasarkan kondisi implementasi saat ini untuk menjadi acuan pengembangan lanjutan, QA, dan alignment lintas tim.',
    null,
    'p'
);

$section->addTitle('1.1 Stack Teknologi Lengkap', 3);
$section->addText(
    'Subbab ini merangkum stack aktual yang digunakan pada implementasi Web ULT saat ini, agar tim produk, developer, QA, dan operasional memiliki referensi teknis yang sama.',
    null,
    'p'
);
foreach ([
    'Backend dan framework: PHP 8.4+, Laravel 12.',
    'Autentikasi: Laravel Breeze (Blade), email verification, reset password, Google OAuth.',
    'RBAC dan otorisasi: spatie/laravel-permission + Laravel Policies + permission middleware.',
    'Database: MySQL untuk operasional, SQLite untuk pengujian.',
    'Frontend: Blade templates, Vite, TailwindCSS, Alpine.js.',
    'Editor konten: Tiptap + sanitasi server-side (ezyang/htmlpurifier).',
    'Dokumen dan office processing: phpoffice/phpword, phpoffice/phpspreadsheet.',
    'Office engine server-side: LibreOffice (headless) untuk kompatibilitas dan kebutuhan konversi dokumen di VPS.',
    'PDF rendering: dompdf/dompdf.',
    'Storage: private storage sebagai default untuk lampiran/output sensitif.',
    'Notifikasi: database notifications + notification center.',
    'PWA: manifest, service worker, halaman offline.',
    'Keamanan: security headers, rate-limiter per domain aksi, audit trail.',
    'Testing dan quality: PHPUnit (Laravel test), Laravel Pint.',
] as $item) {
    $section->addListItem($item, 0, null, 'bullet_main', 'list');
}
$section->addText(
    'Dengan stack ini, sistem menyeimbangkan kebutuhan kecepatan pengembangan, keamanan dokumen, dan kemudahan pemeliharaan. Penambahan LibreOffice headless pada sisi VPS membantu meningkatkan kompatibilitas file office lintas format serta membuka opsi konversi dokumen terotomasi di sisi server. Arsitektur yang dipilih juga mendukung pengembangan bertahap, termasuk kemungkinan migrasi storage, penguatan observability, dan perluasan modul layanan di fase berikutnya.',
    null,
    'p'
);

$section->addTitle('1.2 Struktur File Web ULT (Level Proyek)', 3);
$section->addText(
    'Subbab ini menjelaskan struktur folder utama agar pembaca memahami peta kode sumber dan lokasi komponen penting tanpa harus menelusuri proyek secara manual.',
    null,
    'p'
);
foreach ([
    'app/: inti logika aplikasi (Controllers, Models, Services, Policies, Notifications, Middleware).',
    'bootstrap/: bootstrap Laravel dan cache konfigurasi runtime.',
    'config/: konfigurasi aplikasi (app, auth, cache, filesystems, queue, dll).',
    'database/: migrations, factories, dan seeders data awal.',
    'docs/: dokumentasi proyek, termasuk UML, mirror UI, dan artefak analisis.',
    'public/: entry point web dan aset publik.',
    'resources/: view Blade, komponen UI, dan aset source frontend.',
    'routes/: definisi route web/console aplikasi.',
    'storage/: penyimpanan file runtime (logs, cache, private files, generated outputs).',
    'tests/: pengujian otomatis (feature/unit).',
    'scripts/: utilitas otomasi proyek (termasuk generator PRD).',
    'vendor/: dependency PHP hasil Composer install.',
    'node_modules/: dependency frontend hasil npm install.',
] as $item) {
    $section->addListItem($item, 0, null, 'bullet_main', 'list');
}
$section->addText(
    'Secara arsitektural, struktur ini mengikuti konvensi Laravel sehingga onboarding anggota tim baru menjadi lebih cepat. Bagi proses audit dan maintenance, pemisahan folder tersebut memudahkan identifikasi area perubahan, dampak deployment, serta isolasi masalah ketika terjadi bug pada modul tertentu.',
    null,
    'p'
);

$section->addTitle('2. Tujuan Produk', 2);
$section->addText(
    'Subbab ini menjelaskan arah utama yang ingin dicapai oleh platform Web ULT FKIP Unila dari sisi layanan, efisiensi proses, keamanan data, dan kualitas informasi publik.',
    null,
    'p'
);
foreach ([
    'Menyediakan layanan administrasi ULT yang terstruktur, terukur, dan transparan untuk mahasiswa.',
    'Mempercepat proses verifikasi, persetujuan, penomoran, penandatanganan, dan distribusi dokumen.',
    'Menjaga keamanan data dokumen melalui private storage, policy akses, dan audit trail.',
    'Menyediakan kanal informasi resmi fakultas melalui CMS publik bilingual (ID/EN).',
] as $item) {
    $section->addListItem($item, 0, null, 'bullet_main', 'list');
}
$section->addText(
    'Secara menyeluruh, tujuan produk ini bukan hanya menyediakan form digital, tetapi membangun rantai layanan administrasi yang tertib dari sisi pengguna, operator, dan pengambil keputusan. Platform dirancang agar proses yang sebelumnya tersebar dan sulit dipantau menjadi satu alur yang transparan, terukur, dan dapat dipertanggungjawabkan. Dengan begitu, fakultas memperoleh manfaat operasional berupa waktu proses yang lebih konsisten, risiko kehilangan dokumen yang lebih rendah, serta visibilitas status layanan yang lebih baik untuk semua pihak.',
    null,
    'p'
);

$section->addTitle('3. Persona dan Aktor', 2);
$section->addText(
    'Subbab ini memetakan peran pengguna yang terlibat agar alur kerja dapat dipahami sebagai kolaborasi antaraktor, bukan aktivitas satu pihak saja.',
    null,
    'p'
);
foreach ([
    'Mahasiswa: mengajukan permohonan, melacak progres, revisi data, mengunduh output.',
    'Staf ULT: review ULT, gatekeeping, penomoran, upload output, monitoring operasional.',
    'Admin Jurusan: verifikasi unit, gate dokumen, proses permohonan sesuai scope unit.',
    'Admin Fakultas: otorisasi tahap fakultas dan dukungan pengelolaan operasional.',
    'Signer (Dekan/WD/Kaprodi/Kajur/Sekjur/dll): memberikan keputusan pada tahap tanda tangan.',
    'Staff Final: assembly dokumen akhir dan finalisasi output.',
    'Petugas Legalisir: memproses legalisir untuk layanan yang membutuhkan legalisasi.',
    'Superadmin: konfigurasi master data, RBAC, layanan, CMS, dan audit.',
] as $item) {
    $section->addListItem($item, 0, null, 'bullet_main', 'list');
}
$section->addText(
    'Pembagian aktor ini penting dipahami sebagai pembagian tanggung jawab, bukan sekadar daftar role. Mahasiswa berfokus pada pengajuan dan pemantauan, admin unit/fakultas memastikan validitas proses sesuai kewenangan, signer menangani keputusan otorisasi, dan staff final memastikan keluaran dokumen siap dikirim. Superadmin berada di lapisan tata kelola untuk menjaga struktur sistem tetap sehat. Dengan model ini, setiap peran memiliki batas kerja yang jelas sehingga konflik kewenangan dan tumpang tindih proses dapat dikurangi.',
    null,
    'p'
);

$section->addTitle('4. Scope Produk', 2);
$section->addText(
    'Subbab ini menetapkan batas implementasi saat ini agar ekspektasi fitur tetap terarah dan setiap perubahan dapat dievaluasi terhadap baseline yang sama.',
    null,
    'p'
);
$section->addTitle('4.1 In Scope (As-Built)', 3);
$section->addText(
    'Bagian in-scope berisi kemampuan yang saat ini sudah tersedia dan digunakan pada aplikasi produksi/baseline.',
    null,
    'p'
);
foreach ([
    'Portal publik: beranda, katalog layanan, detail layanan, tentang ULT, blog, pengumuman, panduan pengguna.',
    'Autentikasi: register, login, reset password, email verification, Google OAuth.',
    'Portal mahasiswa: dashboard, daftar/riwayat permohonan, form dinamis, upload lampiran, timeline status, notifikasi.',
    'Workflow operasional: verifikasi unit, review ULT, approval/signoff, penomoran, legalisir, finalisasi.',
    'Portal signer: inbox permohonan yang menunggu keputusan signer aktif.',
    'Portal staff final: preview assembly dan finalisasi dokumen.',
    'CMS admin: hero, kategori, blog, pengumuman, site settings, preview konten.',
    'Master data: users, roles, jurusan, prodi, format nomor dokumen, template nomor surat.',
    'Audit log dan pusat notifikasi.',
    'Internasionalisasi dasar konten publik (ID/EN).',
] as $item) {
    $section->addListItem($item, 0, null, 'bullet_main', 'list');
}
$section->addText(
    'Ruang lingkup in-scope ini merepresentasikan kemampuan yang sudah benar-benar berjalan pada aplikasi saat ini. Artinya, semua item pada bagian ini dapat dijadikan acuan operasional, QA, dan training pengguna tanpa menunggu pengembangan tambahan. Daftar ini juga menjadi baseline penting saat tim ingin menilai dampak perubahan, karena perubahan pada fitur in-scope berpotensi memengaruhi alur layanan utama yang sudah dipakai pengguna aktif.',
    null,
    'p'
);

$section->addTitle('4.2 Out of Scope (Saat ini)', 3);
$section->addText(
    'Bagian out-of-scope memuat area yang sengaja belum dikerjakan agar fokus tim tetap pada prioritas layanan inti.',
    null,
    'p'
);
foreach ([
    'Integrasi payment gateway.',
    'Mobile app native (Android/iOS).',
    'Integrasi tanda tangan elektronik tersertifikasi eksternal.',
    'Analitik BI lanjutan lintas fakultas berbasis data warehouse.',
] as $item) {
    $section->addListItem($item, 0, null, 'bullet_main', 'list');
}
$section->addText(
    'Bagian out-of-scope berfungsi sebagai pagar ekspektasi agar pembahasan produk tetap fokus pada kebutuhan prioritas. Dengan menuliskan batas ini secara eksplisit, tim dapat menghindari perluasan pekerjaan yang tidak terencana dan menjaga stabilitas sistem inti. Ke depan, item out-of-scope dapat dipindahkan ke roadmap jika ada justifikasi bisnis, kesiapan teknis, dan sumber daya implementasi yang memadai.',
    null,
    'p'
);

$section->addTitle('5. Kebutuhan Fungsional Utama', 2);
$section->addText(
    'Subbab ini merinci kebutuhan perilaku sistem dari perspektif fungsi yang langsung digunakan oleh pengguna dan operator.',
    null,
    'p'
);
$section->addTitle('5.1 Portal Publik', 3);
$section->addText(
    'Bagian ini menegaskan fungsi portal publik sebagai kanal informasi sebelum pengguna masuk ke proses layanan terautentikasi.',
    null,
    'p'
);
foreach ([
    'Pengunjung dapat melihat daftar layanan dan detail persyaratan/SOP layanan.',
    'Pengunjung dapat mengakses halaman blog, pengumuman, dan panduan pengguna.',
    'Pengunjung dapat mengganti locale ID/EN pada konten publik.',
] as $item) {
    $section->addListItem($item, 0, null, 'bullet_main', 'list');
}
$section->addText(
    'Portal publik berperan sebagai pintu informasi resmi, sehingga kualitas konten di sini sangat memengaruhi persepsi layanan sebelum pengguna login. Kejelasan informasi persyaratan, SOP, dan artikel pendukung dapat menurunkan kesalahan pengajuan sejak awal. Dengan dukungan bilingual, portal publik juga memperluas akses pemahaman bagi pengguna dengan preferensi bahasa berbeda.',
    null,
    'p'
);

$section->addTitle('5.2 Portal Mahasiswa', 3);
$section->addText(
    'Bagian ini menjelaskan kebutuhan fitur dari sudut pandang mahasiswa sebagai pemohon utama layanan administrasi.',
    null,
    'p'
);
foreach ([
    'Mahasiswa terverifikasi dapat membuat permohonan berdasarkan service aktif.',
    'Form permohonan mendukung field dinamis per layanan dan validasi sesuai rule.',
    'Mahasiswa dapat upload lampiran input dan mengajukan revisi saat status PERLU_PERBAIKAN.',
    'Mahasiswa dapat melihat status/timeline, catatan, dan notifikasi perubahan status.',
    'Mahasiswa dapat mengunduh output dokumen private jika policy terpenuhi.',
    'Mahasiswa dapat mengajukan legalisir pada request yang memenuhi syarat.',
] as $item) {
    $section->addListItem($item, 0, null, 'bullet_main', 'list');
}
$section->addText(
    'Portal mahasiswa dirancang untuk memberi pengalaman layanan yang jelas dari awal sampai akhir. Mahasiswa tidak hanya mengirim data, tetapi juga dapat memahami posisi prosesnya secara real-time melalui status, catatan, dan notifikasi. Mekanisme revisi disediakan agar perbaikan dapat dilakukan di jalur yang benar tanpa membuat pengajuan baru dari nol. Ini membantu efisiensi pengguna sekaligus menjaga kualitas data yang diproses oleh unit internal.',
    null,
    'p'
);

$section->addTitle('5.3 Operasional Internal (Admin/Signer/Staff)', 3);
$section->addText(
    'Bagian ini memuat kebutuhan fungsi operasional internal yang menjaga alur layanan tetap terkendali, akuntabel, dan sesuai kewenangan.',
    null,
    'p'
);
foreach ([
    'Admin dapat memproses status permohonan hanya pada transisi yang diizinkan workflow.',
    'Gatekeeper ULT diberlakukan untuk transisi kritikal sesuai permission.',
    'Signer dapat approve/revisi/reject pada step aktif dengan kontrol policy.',
    'Staff final dapat generate preview dan final output saat status READY_FOR_FINAL.',
    'Admin dapat input/terbitkan nomor dokumen sesuai format per unit dan sequence aman konkurensi.',
    'Semua aksi kritikal tercatat pada audit log.',
] as $item) {
    $section->addListItem($item, 0, null, 'bullet_main', 'list');
}
$section->addText(
    'Subbab ini menegaskan bahwa proses internal berjalan dengan prinsip kontrol bertahap. Setiap tindakan penting, seperti perubahan status, keputusan signoff, dan finalisasi output, hanya boleh dilakukan pada kondisi serta peran yang tepat. Model ini menurunkan risiko human error, memperkuat kepatuhan proses, dan memudahkan audit ketika terjadi sengketa atau kebutuhan penelusuran riwayat keputusan.',
    null,
    'p'
);

$section->addTitle('5.4 CMS dan Master Data', 3);
$section->addText(
    'Bagian ini menjelaskan kebutuhan pengelolaan konten dan data referensi agar sistem tetap relevan, konsisten, dan mudah dipelihara.',
    null,
    'p'
);
foreach ([
    'Admin CMS dapat CRUD hero, kategori, blog, pengumuman, dan pengaturan situs.',
    'Konten blog/pengumuman mendukung autosave draft dan preview signed URL.',
    'Superadmin dapat mengelola pengguna, role, jurusan, prodi, template nomor surat, format nomor dokumen.',
    'Role-based access control menggunakan kombinasi roles, permissions, policies, dan unit scope.',
] as $item) {
    $section->addListItem($item, 0, null, 'bullet_main', 'list');
}
$section->addText(
    'Pengelolaan CMS dan master data adalah fondasi agar aplikasi tetap relevan dan terjaga kualitasnya dalam jangka panjang. Konten publik yang terkelola baik membuat informasi selalu mutakhir, sementara master data yang rapi memastikan alur layanan berjalan konsisten lintas unit. Keduanya saling terkait: konten yang jelas mengurangi beban operasional, dan data master yang benar mengurangi kesalahan proses.',
    null,
    'p'
);

$section->addTitle('6. Kebutuhan Non-Fungsional', 2);
$section->addText(
    'Subbab ini mendeskripsikan kualitas sistem yang harus selalu terjaga, meskipun tidak selalu terlihat sebagai fitur langsung oleh pengguna.',
    null,
    'p'
);
foreach ([
    'Security: private storage untuk dokumen sensitif, anti-IDOR via policy, security headers, input sanitization.',
    'Reliability: transaksi atomik untuk proses kritikal (workflow, numbering, assembly).',
    'Performance: rate limit per domain aksi (auth, upload, download, approvals, status change).',
    'Auditability: pencatatan event kritikal append-only untuk kebutuhan compliance.',
    'Maintainability: arsitektur Laravel modular (controllers, services, policies, model enums).',
    'Availability: dukungan PWA dasar (manifest, service worker, halaman offline).',
] as $item) {
    $section->addListItem($item, 0, null, 'bullet_main', 'list');
}
$section->addText(
    'Kebutuhan non-fungsional pada dokumen ini menjadi jaminan kualitas sistem di luar sekadar “fitur berjalan”. Sistem harus aman, stabil, cepat, mudah dirawat, dan dapat diaudit. Dalam konteks layanan dokumen, aspek ini sangat krusial karena menyangkut data sensitif, legalitas proses, serta kepercayaan pengguna. Oleh sebab itu, non-fungsional perlu diperlakukan sebagai kebutuhan utama, bukan pelengkap.',
    null,
    'p'
);

$section->addTitle('7. Alur Bisnis Inti (Ringkas)', 2);
$section->addText(
    'Subbab ini memberikan gambaran urutan proses end-to-end layanan dokumen dalam bentuk alur kerja standar.',
    null,
    'p'
);
$section->addText('Baseline alur layanan dokumen:', ['bold' => true], 'small');
foreach ([
    'Mahasiswa submit permohonan dan lampiran.',
    'Gate awal oleh Admin Jurusan atau Staf ULT (sesuai konfigurasi layanan).',
    'Input nomor surat (jika diwajibkan) dan start signing.',
    'Signer chain memproses approval/revisi/reject berurutan.',
    'Saat semua signer wajib approve: status menjadi READY_FOR_FINAL.',
    'Staff final melakukan assembly preview dan finalize output.',
    'Request selesai (COMPLETED/SELESAI), mahasiswa dapat download output.',
    'Jika layanan butuh legalisir, request masuk tahap MENUNGGU_LEGALISIR sebelum selesai.',
] as $item) {
    $section->addListItem($item, 0, null, 'bullet_main', 'list');
}
$section->addText(
    'Alur bisnis inti ini dapat dibaca sebagai siklus layanan standar dari sudut pandang end-to-end: mulai dari input pemohon, kontrol internal, otorisasi, produksi output, hingga distribusi hasil. Meski tiap layanan dapat memiliki variasi detail, pola besarnya tetap sama. Konsistensi pola ini memudahkan pelatihan operator baru, standarisasi SOP, dan evaluasi kinerja lintas layanan.',
    null,
    'p'
);

$section->addTitle('8. Acceptance Criteria Tingkat Produk', 2);
$section->addText(
    'Subbab ini menetapkan kriteria minimum yang harus terpenuhi agar perilaku sistem dianggap valid pada level produk.',
    null,
    'p'
);
foreach ([
    'Akses route internal hanya dapat dilakukan aktor dengan permission relevan.',
    'Mahasiswa tidak dapat mengakses request milik user lain.',
    'Transisi status hanya boleh mengikuti rules workflow (tanpa lompatan status arbitrer).',
    'Nomor dokumen yang diterbitkan unik per format/sequence.',
    'Output dokumen hanya dapat diunduh melalui endpoint policy-gated.',
    'Aksi penting (status change, role/permission change, download private, output upload) muncul di audit log.',
] as $item) {
    $section->addListItem($item, 0, null, 'bullet_main', 'list');
}
$section->addText(
    'Acceptance criteria pada level produk dipakai sebagai batas minimal kualitas perilaku sistem yang harus selalu terpenuhi. Kriteria ini berfungsi sebagai acuan bersama antara tim produk, developer, QA, dan operator saat memvalidasi perubahan. Jika salah satu kriteria tidak terpenuhi, maka perubahan dianggap belum siap diluncurkan karena berpotensi mengganggu integritas proses layanan.',
    null,
    'p'
);

$section->addTitle('9. KPI Produk (Usulan Operasional)', 2);
$section->addText(
    'Subbab ini menyajikan indikator kinerja untuk memantau kualitas layanan secara terukur dan berkelanjutan.',
    null,
    'p'
);
foreach ([
    'Median lead time permohonan dari DIAJUKAN ke COMPLETED per jenis layanan.',
    'Persentase permohonan selesai sesuai SLA per layanan.',
    'Rasio request yang kembali PERLU_PERBAIKAN terhadap total pengajuan.',
    'Waktu respons rata-rata pada step signer aktif.',
    'Jumlah insiden akses tidak sah (expected: 0).',
] as $item) {
    $section->addListItem($item, 0, null, 'bullet_main', 'list');
}
$section->addText(
    'KPI ini bertujuan mengukur kualitas layanan secara kuantitatif dan berkelanjutan. Dengan indikator yang konsisten, manajemen dapat menilai apakah perbaikan proses benar-benar berdampak pada kecepatan layanan, tingkat keberhasilan, dan keamanan akses. KPI juga membantu menentukan prioritas perbaikan berbasis data, bukan asumsi, sehingga pengembangan berikutnya lebih tepat sasaran.',
    null,
    'p'
);

$section->addTitle('10. Risiko dan Mitigasi', 2);
$section->addText(
    'Subbab ini memetakan risiko utama yang berpotensi mengganggu layanan serta pendekatan mitigasi yang perlu dijalankan secara konsisten.',
    null,
    'p'
);
$table = $section->addTable([
    'borderSize' => 6,
    'borderColor' => '999999',
    'cellMargin' => 80,
    'alignment' => Jc::LEFT,
]);
$table->addRow();
$table->addCell(2500)->addText('Risiko', ['bold' => true]);
$table->addCell(5500)->addText('Mitigasi', ['bold' => true]);

$riskRows = [
    ['Konfigurasi workflow salah', 'Validasi readiness checker sebelum publish + kontrol permission setup.'],
    ['Kebocoran dokumen sensitif', 'Private storage, policy akses berlapis, audit log download.'],
    ['Bottleneck approval signer', 'Notifikasi step aktif + monitoring inbox signer.'],
    ['Konflik penomoran dokumen', 'Gunakan transaction + locking saat issue number.'],
    ['Konten CMS tidak konsisten ID/EN', 'Proses editorial dan QA konten bilingual sebelum publish.'],
];

foreach ($riskRows as [$risk, $mitigation]) {
    $table->addRow();
    $table->addCell(2500)->addText($risk);
    $table->addCell(5500)->addText($mitigation);
}
$section->addText(
    'Daftar risiko di atas perlu dipandang sebagai risk register operasional yang harus ditinjau berkala, bukan catatan sekali jadi. Seiring perubahan kebutuhan layanan, profil risiko juga dapat berubah. Karena itu, mitigasi harus dipantau efektivitasnya melalui indikator nyata, seperti penurunan insiden atau percepatan waktu pemulihan proses. Pendekatan ini membantu organisasi menjaga ketahanan layanan dalam jangka panjang.',
    null,
    'p'
);

$section->addTitle('11. Dependency dan Asumsi', 2);
$section->addText(
    'Subbab ini menjelaskan ketergantungan eksternal dan asumsi operasional yang perlu dipenuhi agar sistem berjalan stabil.',
    null,
    'p'
);
foreach ([
    'Sistem email SMTP aktif untuk verifikasi akun, reset password, dan notifikasi.',
    'Data struktur unit (fakultas-jurusan-prodi) dan role organisasi selalu terbarui.',
    'Template DOCX layanan dikelola sesuai placeholder mapping yang valid.',
    'Tim operator menjalankan SOP yang selaras dengan workflow sistem.',
] as $item) {
    $section->addListItem($item, 0, null, 'bullet_main', 'list');
}
$section->addText(
    'Dependency dan asumsi perlu dinyatakan terbuka agar seluruh tim memahami prasyarat berjalannya sistem. Jika salah satu dependency tidak terpenuhi, misalnya email tidak aktif atau data unit tidak sinkron, dampaknya bisa langsung terasa pada proses layanan. Dengan mencatatnya secara eksplisit, tim dapat menyiapkan rencana cadangan dan prosedur eskalasi lebih cepat ketika kendala terjadi.',
    null,
    'p'
);

$section->addTitle('12. Rencana Pengembangan Lanjutan (Backlog Prioritas)', 2);
$section->addText(
    'Subbab ini menguraikan arah peningkatan berikutnya setelah baseline fitur inti dianggap stabil.',
    null,
    'p'
);
foreach ([
    'Dashboard analitik SLA dan bottleneck proses per unit/layanan.',
    'Integrasi e-sign tersertifikasi untuk dokumen legal formal.',
    'Peningkatan observability (metrics + alerting) untuk proses kritikal.',
    'Penyempurnaan otomasi QA regresi untuk workflow kompleks lintas role.',
] as $item) {
    $section->addListItem($item, 0, null, 'bullet_main', 'list');
}
$section->addText(
    'Rencana pengembangan lanjutan ini disusun untuk meningkatkan kualitas layanan setelah fondasi inti stabil. Urutan prioritas sebaiknya ditentukan berdasarkan kombinasi dampak ke pengguna, risiko operasional, dan kesiapan implementasi. Dengan pendekatan bertahap, tim dapat menjaga stabilitas sistem produksi sambil tetap mendorong peningkatan kapabilitas yang bernilai tinggi.',
    null,
    'p'
);

$section->addPageBreak();
$section->addTitle('13. Lampiran UML', 2);
$section->addText(
    'Diagram berikut merangkum perspektif fungsional dan teknis utama sistem agar mudah dipahami lintas peran (produk, teknis, dan operasional).',
    null,
    'p'
);

$umlDiagrams = [
    [
        'title' => '13.1 Use Case Diagram',
        'desc' => 'Menunjukkan aktor utama dan interaksi fitur inti Web ULT.',
        'how_to_read_story' => 'Bayangkan Use Case Diagram ini seperti peta layanan di sebuah kantor. Langkah pertama, lihat nama orang/peran di luar kotak besar sistem: itulah pihak yang berinteraksi dengan aplikasi, misalnya Mahasiswa, Admin Jurusan, Staf ULT, Signer, Staff Final, dan Superadmin. Langkah kedua, lihat oval-oval di dalam kotak: tiap oval adalah layanan atau pekerjaan yang bisa dijalankan di sistem, seperti Ajukan Permohonan, Review ULT, sampai Finalisasi Output. Langkah ketiga, ikuti garis dari aktor ke oval. Jika Mahasiswa terhubung ke beberapa oval, artinya Mahasiswa memang punya akses ke semua fungsi itu. Jika Staf ULT terhubung ke fungsi review dan penomoran, berarti dua fungsi itu menjadi tanggung jawab Staf ULT. Langkah keempat, perhatikan label relasi [include] yang artinya “proses ini pasti ikut terjadi”. Contohnya, pengajuan biasanya akan mengikutsertakan verifikasi. Langkah kelima, pahami label [extend] sebagai “proses tambahan jika syarat tertentu terpenuhi”, misalnya legalisir yang tidak selalu terjadi pada semua layanan. Cara termudah untuk orang awam: baca dari pertanyaan “siapa melakukan apa?”, lalu “setelah itu proses wajibnya apa?”, lalu “proses tambahannya kapan muncul?”. Jika tiga pertanyaan itu bisa dijawab, berarti diagram sudah terbaca dengan benar.',
        'explanations' => [
            'Fokus diagram: siapa saja pengguna sistem dan layanan apa yang mereka jalankan.',
            'Aktor eksternal utama adalah Mahasiswa, Admin Jurusan, Staf ULT, Signer, Staff Final, dan Superadmin.',
            'Relasi [include] menggambarkan proses yang selalu menjadi bagian dari alur utama (misalnya pengajuan berujung ke verifikasi/gate).',
            'Relasi [extend] menggambarkan proses opsional/bersyarat (misalnya legalisir setelah output tersedia).',
            'Diagram ini digunakan untuk validasi cakupan fitur di level bisnis sebelum masuk detail teknis.',
        ],
        'full_explanation' => 'Use Case Diagram ini penting karena menjadi jembatan antara bahasa bisnis dan bahasa teknis. Dengan melihat siapa aktornya dan fitur apa yang ia jalankan, tim non-teknis bisa cepat memahami batas tanggung jawab setiap peran tanpa harus membaca kode atau database. Pada konteks Web ULT, diagram ini memperjelas bahwa proses layanan bukan hanya urusan mahasiswa, tetapi melibatkan peran berlapis sampai dokumen final diterbitkan. Ini membantu menghindari miskomunikasi seperti "siapa yang harus memproses tahap ini" atau "fitur ini seharusnya milik siapa". Dari sisi pengelolaan produk, diagram ini juga memudahkan saat membahas scope: fitur yang belum punya aktor jelas biasanya perlu ditinjau ulang, sedangkan fitur yang terlalu banyak aktor sering membutuhkan pembatasan hak akses yang lebih ketat.',
        'path' => __DIR__.'/../docs/uml/usecase-ult.png',
    ],
    [
        'title' => '13.2 Activity Diagram',
        'desc' => 'Menjelaskan alur proses permohonan dari pengajuan hingga dokumen selesai.',
        'how_to_read_story' => 'Anggap Activity Diagram ini sebagai cerita perjalanan satu permohonan dari awal sampai tamat. Mulai dari simbol start: ini titik ketika mahasiswa mengirim permohonan dan lampiran. Setelah itu, ikuti panah ke bawah satu per satu seperti membaca alur cerita komik. Setiap kotak aktivitas berisi aksi nyata, misalnya verifikasi, input nomor, tanda tangan, atau finalisasi. Saat menemukan bentuk keputusan (percabangan), berhenti sebentar dan baca pilihan jalurnya: apakah lanjut, revisi, atau ditolak. Jika revisi, jangan bingung, karena artinya pemohon diminta memperbaiki lalu proses kembali ke langkah verifikasi; jadi ini memang loop yang normal, bukan error. Jika ditolak, alur berakhir lebih cepat pada status penolakan. Jika semua tahap lolos, alur menuju penandatanganan lengkap, lanjut ke finalisasi dokumen, lalu status selesai. Pada layanan tertentu, ada bab tambahan legalisir sebelum benar-benar selesai. Untuk orang awam, teknik membacanya sederhana: ikuti panah seperti rute perjalanan, dan di setiap percabangan jawab “kalau Ya lewat mana, kalau Tidak lewat mana”. Dengan cara ini, kamu bisa langsung tahu di titik mana sebuah permohonan sedang berada.',
        'explanations' => [
            'Fokus diagram: urutan aktivitas bisnis beserta percabangan keputusan status.',
            'Proses dimulai dari submit permohonan, dilanjutkan verifikasi gate, penandatanganan, hingga finalisasi dokumen.',
            'Cabang revisi mengembalikan proses ke tahap verifikasi agar pemohon bisa memperbaiki data/lampiran.',
            'Cabang tolak menghentikan alur dengan status DITOLAK atau DITOLAK_ADMIN.',
            'Diagram ini membantu tim operasional memahami kapan request maju, mundur (revisi), atau berhenti.',
        ],
        'full_explanation' => 'Activity Diagram menjelaskan "alur kerja nyata" yang dialami satu permohonan, sehingga sangat cocok untuk operasional harian. Dalam praktiknya, diagram ini membantu staf melihat titik mana yang paling sering menjadi hambatan, misalnya pada verifikasi atau proses signer. Ketika ada permohonan yang terasa lama, tim bisa membandingkan posisi aktual request dengan jalur ideal pada diagram ini untuk menemukan akar masalahnya. Diagram ini juga penting untuk edukasi pengguna baru karena memperlihatkan bahwa revisi adalah bagian normal dari proses, bukan kegagalan sistem. Dengan demikian, pengguna awam dapat memahami kenapa sebuah permohonan bisa kembali ke pemohon sebelum akhirnya selesai.',
        'path' => __DIR__.'/../docs/uml/activity-ult.png',
    ],
    [
        'title' => '13.3 Sequence Diagram',
        'desc' => 'Memvisualkan urutan interaksi antarkomponen aplikasi saat memproses request dokumen.',
        'how_to_read_story' => 'Sequence Diagram paling mudah dibaca kalau dibayangkan seperti rekaman percakapan berurutan antar petugas. Kolom-kolom vertikal adalah “siapa yang ikut bicara”: Mahasiswa, Controller, Service, Database, dan Storage. Bacanya dari atas ke bawah, karena makin ke bawah berarti waktunya makin maju. Ketika ada panah dari Mahasiswa ke Controller, artinya Mahasiswa mengirim permintaan. Lalu Controller meneruskan ke Service, Service menyimpan data ke Database, dan file ke Storage. Jadi diagram ini menjelaskan “siapa meminta apa ke siapa” secara urut. Bagian loop berarti percakapan yang sama bisa terulang, misalnya proses signer yang berlangsung sampai seluruh pihak wajib memberi keputusan. Bagian alt berarti ada skenario berbeda tergantung keputusan: jalur approve, jalur revisi, atau jalur tolak. Untuk orang awam, fokuskan pada tiga hal: awal proses dimulai dari panah pertama, titik cabang terjadi di blok alt, dan akhir proses terlihat saat file output dikirim kembali ke pemohon. Jika tiga titik itu dipahami, sequence diagram akan terasa jauh lebih mudah.',
        'explanations' => [
            'Fokus diagram: pesan antar komponen sistem dari sisi runtime (controller, service, database, storage).',
            'Alur menunjukkan kapan status request diubah, kapan keputusan signer disimpan, dan kapan output dibuat.',
            'Blok loop menjelaskan keputusan signer berulang sampai seluruh signer mandatory selesai.',
            'Blok alt memisahkan jalur approve, revisi, dan tolak agar titik kontrol sistem terlihat jelas.',
            'Diagram ini penting untuk review teknis, debugging alur, dan validasi kontrak antar service.',
        ],
        'full_explanation' => 'Sequence Diagram memperlihatkan urutan komunikasi antarkomponen pada saat sistem berjalan. Ini sangat berguna ketika tim ingin memahami kenapa suatu aksi berhasil, lambat, atau gagal, karena terlihat jelas siapa yang memanggil siapa dan kapan data ditulis ke database atau storage. Untuk pembaca awam, bayangkan diagram ini seperti transkrip percakapan resmi antar bagian dalam kantor: jika satu bagian tidak merespons tepat waktu, seluruh alur bisa tertahan. Dengan membaca diagram ini, tim non-teknis tetap bisa memahami dampak keputusan proses terhadap pengalaman pengguna, misalnya kenapa output belum muncul walau request sudah diajukan. Diagram ini juga membantu memastikan bahwa proses sensitif, seperti perubahan status dan akses file private, benar-benar terjadi di titik kontrol yang aman.',
        'path' => __DIR__.'/../docs/uml/sequence-ult.png',
    ],
    [
        'title' => '13.4 Class Diagram',
        'desc' => 'Merangkum entitas domain inti dan relasinya.',
        'how_to_read_story' => 'Class Diagram bisa dipahami seperti peta data dan hubungan antar “lemari arsip”. Setiap kotak class adalah satu jenis data yang disimpan sistem, misalnya User, Request, Service, Attachment, dan seterusnya. Untuk membaca dengan mudah, mulai dari class Request karena ini pusat cerita permohonan. Dari Request, lihat garis relasinya: Request terhubung ke User (siapa pemohon), ke Service (layanan apa yang diminta), ke Attachment (file yang diunggah), ke RequestSignoff (riwayat persetujuan), dan ke RequestOutput (hasil akhir dokumen). Lalu baca class ServiceWorkflow dan ServiceField untuk memahami aturan permainan tiap layanan, seperti tahapan proses dan form yang harus diisi. Terakhir, lihat AuditLog sebagai catatan jejak tindakan, sehingga sistem bisa diaudit jika ada masalah. Untuk pembaca awam, tidak perlu hafal semua atribut teknis; cukup pahami dulu “data utama”, “data pendukung”, dan “hubungan antardata”. Dengan cara itu, class diagram terasa seperti cerita struktur berkas, bukan diagram teknis yang rumit.',
        'explanations' => [
            'Fokus diagram: struktur data inti domain ULT beserta relasi antar entitas.',
            'Entitas pusat adalah Request yang berelasi ke Service, User (student), field value, attachment, signoff, dan output.',
            'Service terhubung ke workflow dan field untuk membentuk perilaku dinamis per jenis layanan.',
            'AuditLog disajikan sebagai jejak peristiwa untuk kebutuhan akuntabilitas dan investigasi.',
            'Diagram ini menjadi acuan saat perubahan skema database atau refactor model domain.',
        ],
        'full_explanation' => 'Class Diagram berfungsi sebagai peta struktur informasi sistem. Melalui diagram ini, orang awam dapat memahami bahwa data pada Web ULT tidak berdiri sendiri, melainkan saling terhubung membentuk satu cerita utuh dari pengajuan sampai dokumen selesai. Ketika melihat Request terhubung ke banyak entitas, itu menandakan bahwa request adalah pusat proses layanan. Kelebihan diagram ini adalah memudahkan diskusi perubahan: jika satu entitas diubah, tim bisa langsung melihat bagian mana saja yang ikut terdampak. Bagi manajemen, diagram ini memberi gambaran mengapa perubahan kecil pada form atau workflow kadang memerlukan penyesuaian di beberapa area sekaligus. Untuk tim teknis, ini menjadi fondasi agar implementasi tetap konsisten dengan desain data yang sudah disepakati.',
        'path' => __DIR__.'/../docs/uml/class-ult.png',
    ],
];

foreach ($umlDiagrams as $idx => $diagram) {
    $section->addTitle($diagram['title'], 3);
    $section->addText(
        'Subbab ini menyajikan satu diagram UML beserta panduan bacanya agar pembaca non-teknis dapat memahami konteks proses secara runtut.',
        null,
        'p'
    );
    $section->addText($diagram['desc'], null, 'small');

    $section->addText('Cara membaca (alur cerita dari awal sampai selesai):', ['bold' => true], 'small');
    $section->addText($diagram['how_to_read_story'], null, 'p');

    $section->addText('Penjelasan lengkap subdiagram:', ['bold' => true], 'small');
    $summaryParagraph = 'Secara ringkas, subdiagram ini menekankan beberapa hal utama: '.implode(' ', $diagram['explanations']);
    $section->addText($summaryParagraph, null, 'p');
    $section->addText($diagram['full_explanation'], null, 'p');
    $section->addTextBreak(1);

    if (is_file($diagram['path'])) {
        $section->addImage($diagram['path'], [
            'width' => 520,
            'alignment' => Jc::CENTER,
            'spaceAfter' => Converter::pointToTwip(8),
        ]);
    } else {
        $section->addText('Gambar tidak ditemukan: '.$diagram['path'], ['italic' => true], 'small');
    }

    if ($idx < count($umlDiagrams) - 1) {
        $section->addTextBreak(1);
    }
}

$section->addPageBreak();
$section->addTitle('14. Lampiran Struktur File View (Lengkap)', 2);
$section->addText(
    'Lampiran ini menampilkan struktur file view secara lengkap dari direktori resources/views agar memudahkan audit, onboarding, dan pelacakan perubahan UI.',
    null,
    'p'
);

$viewsRoot = realpath(__DIR__.'/../resources/views');
$viewFiles = [];
if ($viewsRoot !== false) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($viewsRoot, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isFile()) {
            $relative = substr($fileInfo->getPathname(), strlen($viewsRoot) + 1);
            $relative = str_replace('\\', '/', $relative);
            $viewFiles[] = 'resources/views/'.$relative;
        }
    }
}

sort($viewFiles, SORT_NATURAL | SORT_FLAG_CASE);

foreach ($viewFiles as $viewPath) {
    $section->addListItem($viewPath, 0, null, 'bullet_main', 'list');
}

$section->addText('Total file view terdaftar: '.count($viewFiles).'.', ['italic' => true], 'small');

$section->addPageBreak();
$section->addTitle('15. Lampiran Referensi Desain UI (Figma)', 2);
$section->addText(
    'Lampiran ini menyimpan referensi utama desain UI dalam bentuk link file Figma. Pendekatan ini dipilih agar dokumen tetap ringan, mudah dibuka, dan selalu mengarah ke sumber desain yang paling mutakhir.',
    null,
    'p'
);
$figmaDesignUrl = 'https://www.figma.com/design/56Dxg50h3xZ4Jo6rzvxnX1/Web-ULT-FKIP-Unila?t=LR5D8uHXD5e7wK2V-0';
$section->addText('Sumber desain UI utama:', ['bold' => true], 'small');
$section->addText($figmaDesignUrl, ['color' => '0000FF', 'underline' => 'single'], 'p');
$section->addText(
    'File Figma di atas menjadi referensi visual resmi untuk screen public, auth, profile, signer, staff, student, dan admin. Dengan menggunakan link tunggal ini, tim dapat meninjau desain terbaru tanpa membebani dokumen dengan puluhan gambar statis yang mudah usang.',
    null,
    'p'
);
$section->addText(
    'Penggunaan link Figma di lampiran ini juga lebih tepat untuk kebutuhan kolaborasi. Product owner dapat meninjau konteks desain secara utuh, developer dapat memeriksa detail layout dan komponen langsung di sumber desain, dan QA dapat membandingkan implementasi dengan frame terbaru tanpa harus menebak apakah screenshot dalam dokumen masih relevan atau tidak.',
    null,
    'p'
);
$section->addText(
    'Jika di kemudian hari diperlukan snapshot visual tertentu untuk kebutuhan presentasi atau approval formal, gambar ekspor dapat ditambahkan secara selektif pada lampiran terpisah. Namun untuk baseline PRD ini, link Figma diperlakukan sebagai sumber desain utama yang lebih ringan, stabil, dan mudah dipelihara.',
    null,
    'p'
);

$section->addTextBreak(1);
$section->addText(
    'Sumber baseline: implementasi aplikasi berjalan (routes, seeder, layanan dokumen, CMS, dan README proyek) per '.$dateLabel.'.',
    ['italic' => true, 'size' => 10],
    'small'
);

if (!is_dir(dirname($outputPath))) {
    mkdir(dirname($outputPath), 0777, true);
}

$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save($outputPath);

echo $outputPath.PHP_EOL;
