import os
from pathlib import Path

def create_use_case_svg():
    svg = """<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg width="1200" height="950" xmlns="http://www.w3.org/2000/svg" style="background-color: #ffffff;">
    <defs>
        <marker id="arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
            <path d="M 0 0 L 10 5 L 0 10 z" fill="#000000" />
        </marker>
    </defs>

    <!-- System Boundary -->
    <rect x="300" y="40" width="600" height="870" fill="none" stroke="#000000" stroke-width="2" />
    <text x="320" y="70" font-family="'Times New Roman', Georgia, serif" font-size="20px" font-weight="bold" fill="#000000">Sistem Web ULT FKIP Unila</text>

    <!-- ACTORS -->
    <g transform="translate(150, 300)">
        <circle cx="0" cy="-30" r="15" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <line x1="0" y1="-15" x2="0" y2="25" fill="none" stroke="#000000" stroke-width="2" />
        <line x1="-25" y1="0" x2="25" y2="0" fill="none" stroke="#000000" stroke-width="2" />
        <line x1="0" y1="25" x2="-15" y2="55" fill="none" stroke="#000000" stroke-width="2" />
        <line x1="0" y1="25" x2="15" y2="55" fill="none" stroke="#000000" stroke-width="2" />
        <text x="0" y="85" font-family="'Times New Roman', Georgia, serif" font-size="22px" font-weight="bold" fill="#000000" text-anchor="middle">Mahasiswa</text>
    </g>

    <g transform="translate(150, 700)">
        <circle cx="0" cy="-30" r="15" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <line x1="0" y1="-15" x2="0" y2="25" fill="none" stroke="#000000" stroke-width="2" />
        <line x1="-25" y1="0" x2="25" y2="0" fill="none" stroke="#000000" stroke-width="2" />
        <line x1="0" y1="25" x2="-15" y2="55" fill="none" stroke="#000000" stroke-width="2" />
        <line x1="0" y1="25" x2="15" y2="55" fill="none" stroke="#000000" stroke-width="2" />
        <text x="0" y="85" font-family="'Times New Roman', Georgia, serif" font-size="22px" font-weight="bold" fill="#000000" text-anchor="middle">Admin Utama</text>
    </g>

    <g transform="translate(1050, 150)">
        <circle cx="0" cy="-30" r="15" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <line x1="0" y1="-15" x2="0" y2="25" fill="none" stroke="#000000" stroke-width="2" />
        <line x1="-25" y1="0" x2="25" y2="0" fill="none" stroke="#000000" stroke-width="2" />
        <line x1="0" y1="25" x2="-15" y2="55" fill="none" stroke="#000000" stroke-width="2" />
        <line x1="0" y1="25" x2="15" y2="55" fill="none" stroke="#000000" stroke-width="2" />
        <text x="0" y="85" font-family="'Times New Roman', Georgia, serif" font-size="22px" font-weight="bold" fill="#000000" text-anchor="middle">Staf ULT</text>
    </g>

    <g transform="translate(1050, 450)">
        <circle cx="0" cy="-30" r="15" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <line x1="0" y1="-15" x2="0" y2="25" fill="none" stroke="#000000" stroke-width="2" />
        <line x1="-25" y1="0" x2="25" y2="0" fill="none" stroke="#000000" stroke-width="2" />
        <line x1="0" y1="25" x2="-15" y2="55" fill="none" stroke="#000000" stroke-width="2" />
        <line x1="0" y1="25" x2="15" y2="55" fill="none" stroke="#000000" stroke-width="2" />
        <text x="0" y="85" font-family="'Times New Roman', Georgia, serif" font-size="22px" font-weight="bold" fill="#000000" text-anchor="middle">Admin Jurusan</text>
    </g>

    <g transform="translate(1050, 750)">
        <circle cx="0" cy="-30" r="15" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <line x1="0" y1="-15" x2="0" y2="25" fill="none" stroke="#000000" stroke-width="2" />
        <line x1="-25" y1="0" x2="25" y2="0" fill="none" stroke="#000000" stroke-width="2" />
        <line x1="0" y1="25" x2="-15" y2="55" fill="none" stroke="#000000" stroke-width="2" />
        <line x1="0" y1="25" x2="15" y2="55" fill="none" stroke="#000000" stroke-width="2" />
        <text x="0" y="85" font-family="'Times New Roman', Georgia, serif" font-size="22px" font-weight="bold" fill="#000000" text-anchor="middle">Pejabat (Signer)</text>
    </g>

    <!-- USE CASES -->
    <!-- MHS UC -->
    <g transform="translate(430, 110)">
        <ellipse cx="170" cy="30" rx="150" ry="28" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="170" y="36" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Mengajukan Permohonan</text>
    </g>
    <g transform="translate(430, 190)">
        <ellipse cx="170" cy="30" rx="150" ry="28" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="170" y="36" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Melacak Status (Timeline)</text>
    </g>
    <g transform="translate(430, 270)">
        <ellipse cx="170" cy="30" rx="150" ry="28" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="170" y="36" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Mengunduh Dokumen Hasil</text>
    </g>

    <!-- Admin Jurusan & Staf UC -->
    <g transform="translate(430, 350)">
        <ellipse cx="170" cy="30" rx="150" ry="28" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="170" y="36" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Pengecekan Pertama Berkas</text>
    </g>
    <g transform="translate(430, 430)">
        <ellipse cx="170" cy="30" rx="150" ry="28" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="170" y="36" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Penerbitan Nomor Otomatis</text>
    </g>
    <g transform="translate(430, 510)">
        <ellipse cx="170" cy="30" rx="150" ry="28" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="170" y="36" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Validasi Permohonan ULT</text>
    </g>
    <g transform="translate(430, 590)">
        <ellipse cx="170" cy="30" rx="150" ry="28" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="170" y="36" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Kelola Layanan &amp; Template</text>
    </g>
    <g transform="translate(430, 670)">
        <ellipse cx="170" cy="30" rx="150" ry="28" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="170" y="36" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Merakit Dokumen (Assembly)</text>
    </g>

    <!-- Signer -->
    <g transform="translate(430, 750)">
        <ellipse cx="170" cy="30" rx="150" ry="28" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="170" y="36" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Verifikasi &amp; TTD Elektronik</text>
    </g>

    <!-- Admin Utama -->
    <g transform="translate(430, 830)">
        <ellipse cx="170" cy="30" rx="150" ry="28" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="170" y="36" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Kelola Pengguna &amp; Hak Akses</text>
    </g>

    <!-- CONNECTIONS -->
    <!-- Mahasiswa Links -->
    <line x1="175" y1="310" x2="430" y2="140" fill="none" stroke="#000000" stroke-width="1.5" />
    <line x1="175" y1="310" x2="430" y2="220" fill="none" stroke="#000000" stroke-width="1.5" />
    <line x1="175" y1="310" x2="430" y2="300" fill="none" stroke="#000000" stroke-width="1.5" />

    <!-- Admin Jurusan Links -->
    <line x1="1025" y1="460" x2="770" y2="380" fill="none" stroke="#000000" stroke-width="1.5" />
    <line x1="1025" y1="460" x2="770" y2="460" fill="none" stroke="#000000" stroke-width="1.5" />

    <!-- Staf ULT Links -->
    <line x1="1025" y1="160" x2="770" y2="540" fill="none" stroke="#000000" stroke-width="1.5" />
    <line x1="1025" y1="160" x2="770" y2="620" fill="none" stroke="#000000" stroke-width="1.5" />
    <line x1="1025" y1="160" x2="770" y2="700" fill="none" stroke="#000000" stroke-width="1.5" />

    <!-- Signer Links -->
    <line x1="1025" y1="760" x2="770" y2="780" fill="none" stroke="#000000" stroke-width="1.5" />

    <!-- Admin Utama Links -->
    <line x1="175" y1="710" x2="430" y2="860" fill="none" stroke="#000000" stroke-width="1.5" />

</svg>
"""
    return svg


def create_arsitektur_svg():
    svg = """<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg width="1200" height="1000" xmlns="http://www.w3.org/2000/svg" style="background-color: #ffffff;">
    <defs>
        <marker id="arrow-head" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
            <path d="M 0 0 L 10 5 L 0 10 z" fill="#000000" />
        </marker>
    </defs>

    <!-- LAYERS -->
    <!-- 1. Client Layer -->
    <rect x="50" y="50" width="1100" height="130" rx="8" ry="8" fill="none" stroke="#aaaaaa" stroke-width="1.5" stroke-dasharray="6,6" />
    <text x="70" y="80" font-family="'Times New Roman', Georgia, serif" font-size="20px" font-weight="bold" fill="#333333">I. CLIENT &amp; INTERFACE LAYER (PWA READY)</text>
    
    <rect x="90" y="100" width="220" height="60" rx="5" fill="#ffffff" stroke="#000000" stroke-width="2" />
    <text x="200" y="136" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Public Portal</text>
    
    <rect x="350" y="100" width="220" height="60" rx="5" fill="#ffffff" stroke="#000000" stroke-width="2" />
    <text x="460" y="136" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Student Portal</text>
    
    <rect x="610" y="100" width="220" height="60" rx="5" fill="#ffffff" stroke="#000000" stroke-width="2" />
    <text x="720" y="136" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Staff/Admin Portal</text>
    
    <rect x="870" y="100" width="220" height="60" rx="5" fill="#ffffff" stroke="#000000" stroke-width="2" />
    <text x="980" y="136" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Signer Portal</text>

    <!-- 2. Security Layer -->
    <rect x="50" y="220" width="1100" height="140" rx="8" ry="8" fill="none" stroke="#aaaaaa" stroke-width="1.5" stroke-dasharray="6,6" />
    <text x="70" y="250" font-family="'Times New Roman', Georgia, serif" font-size="20px" font-weight="bold" fill="#333333">II. SECURITY &amp; ROUTING GATEKEEPER</text>
    
    <rect x="80" y="270" width="240" height="70" rx="5" fill="#f0f0f0" stroke="#000000" stroke-width="2" />
    <text x="200" y="300" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Laravel Router</text>
    <text x="200" y="325" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">&amp; Security CSP</text>
    
    <rect x="340" y="270" width="240" height="70" rx="5" fill="#f0f0f0" stroke="#000000" stroke-width="2" />
    <text x="460" y="300" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Spatie RBAC</text>
    <text x="460" y="325" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Middleware Otorisasi</text>
    
    <rect x="600" y="270" width="240" height="70" rx="5" fill="#f0f0f0" stroke="#000000" stroke-width="2" />
    <text x="720" y="300" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Anti-IDOR</text>
    <text x="720" y="325" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Akses Privat</text>

    <rect x="860" y="270" width="240" height="70" rx="5" fill="#f0f0f0" stroke="#000000" stroke-width="2" />
    <text x="980" y="300" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">HtmlSanitizer</text>
    <text x="980" y="325" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Proteksi Input XSS</text>

    <!-- 3. Core App Layer -->
    <rect x="50" y="400" width="1100" height="140" rx="8" ry="8" fill="none" stroke="#aaaaaa" stroke-width="1.5" stroke-dasharray="6,6" />
    <text x="70" y="430" font-family="'Times New Roman', Georgia, serif" font-size="20px" font-weight="bold" fill="#333333">III. LARAVEL 12 APPLICATION CORE</text>
    
    <rect x="150" y="450" width="280" height="70" rx="5" fill="#ffffff" stroke="#000000" stroke-width="2" />
    <text x="290" y="480" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Controllers Core</text>
    <text x="290" y="505" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">(RequestAdminController)</text>
    
    <rect x="460" y="450" width="280" height="70" rx="5" fill="#ffffff" stroke="#000000" stroke-width="2" />
    <text x="600" y="480" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">DocumentAssembler</text>
    <text x="600" y="505" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Fasilitator Perakitan</text>
    
    <rect x="770" y="450" width="280" height="70" rx="5" fill="#ffffff" stroke="#000000" stroke-width="2" />
    <text x="910" y="480" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">HtmlToOpenXMLParser</text>
    <text x="910" y="505" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Parser Tag WYSIWYG</text>

    <!-- 4. Data Layer -->
    <rect x="50" y="580" width="1100" height="140" rx="8" ry="8" fill="none" stroke="#aaaaaa" stroke-width="1.5" stroke-dasharray="6,6" />
    <text x="70" y="610" font-family="'Times New Roman', Georgia, serif" font-size="20px" font-weight="bold" fill="#333333">IV. DATABASE &amp; STORAGE LAYER</text>
    
    <rect x="250" y="630" width="300" height="70" rx="5" fill="#f0f0f0" stroke="#000000" stroke-width="2" />
    <text x="400" y="660" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">MySQL Database</text>
    <text x="400" y="685" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Data Form, User, Audit</text>
    
    <rect x="650" y="630" width="300" height="70" rx="5" fill="#f0f0f0" stroke="#000000" stroke-width="2" />
    <text x="800" y="660" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Private Storage Disk</text>
    <text x="800" y="685" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Berkas Privat (.docx)</text>

    <!-- 5. Assembly Engine -->
    <rect x="50" y="760" width="1100" height="140" rx="8" ry="8" fill="none" stroke="#aaaaaa" stroke-width="1.5" stroke-dasharray="6,6" />
    <text x="70" y="790" font-family="'Times New Roman', Georgia, serif" font-size="20px" font-weight="bold" fill="#333333">V. DOCUMENT ASSEMBLY ENGINE (PHPOFFICE)</text>
    
    <rect x="150" y="810" width="260" height="70" rx="5" fill="#ffffff" stroke="#000000" stroke-width="2" />
    <text x="280" y="840" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Word Templates (.docx)</text>
    <text x="280" y="865" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Placeholder Surat</text>
    
    <rect x="470" y="810" width="260" height="70" rx="5" fill="#ffffff" stroke="#000000" stroke-width="2" />
    <text x="600" y="840" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">PhpWord &amp; ZipArchive</text>
    <text x="600" y="865" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Pengemas XML Mentah</text>
    
    <rect x="790" y="810" width="260" height="70" rx="5" fill="#ffffff" stroke="#000000" stroke-width="2" />
    <text x="920" y="840" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">PDF Converter</text>
    <text x="920" y="865" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Format Unduhan PDF</text>

    <!-- ARROWS -->
    <line x1="200" y1="160" x2="200" y2="270" fill="none" stroke="#000000" stroke-width="2" marker-end="url(#arrow-head)" />
    <line x1="460" y1="160" x2="460" y2="270" fill="none" stroke="#000000" stroke-width="2" marker-end="url(#arrow-head)" />
    <line x1="720" y1="160" x2="720" y2="270" fill="none" stroke="#000000" stroke-width="2" marker-end="url(#arrow-head)" />
    <line x1="980" y1="160" x2="980" y2="270" fill="none" stroke="#000000" stroke-width="2" marker-end="url(#arrow-head)" />

    <line x1="200" y1="340" x2="200" y2="450" fill="none" stroke="#000000" stroke-width="2" marker-end="url(#arrow-head)" />
    <path d="M 720 340 L 720 400 L 600 400 L 600 450" fill="none" stroke="#000000" stroke-width="2" marker-end="url(#arrow-head)" />

    <line x1="430" y1="485" x2="460" y2="485" fill="none" stroke="#000000" stroke-width="2" marker-end="url(#arrow-head)" />
    <line x1="740" y1="485" x2="770" y2="485" fill="none" stroke="#000000" stroke-width="2" marker-end="url(#arrow-head)" />

    <path d="M 290 520 L 290 560 L 400 560 L 400 630" fill="none" stroke="#000000" stroke-width="2" marker-end="url(#arrow-head)" />
    <path d="M 600 520 L 600 560 L 800 560 L 800 630" fill="none" stroke="#000000" stroke-width="2" marker-end="url(#arrow-head)" />
    
    <path d="M 910 520 L 910 730 L 600 730 L 600 810" fill="none" stroke="#000000" stroke-width="2" marker-end="url(#arrow-head)" />

    <line x1="410" y1="845" x2="470" y2="845" fill="none" stroke="#000000" stroke-width="2" marker-end="url(#arrow-head)" />
    <line x1="730" y1="845" x2="790" y2="845" fill="none" stroke="#000000" stroke-width="2" marker-end="url(#arrow-head)" />
</svg>
"""
    return svg


def create_flowchart_svg():
    svg = """<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg width="1000" height="1400" xmlns="http://www.w3.org/2000/svg" style="background-color: #ffffff;">

    <!-- ARROWS (Drawn with explicit polygons to fix JPG renderer ignoring <marker>) -->
    
    <line x1="500" y1="90" x2="500" y2="130" fill="none" stroke="#000000" stroke-width="2" />
    <polygon points="495,120 505,120 500,130" fill="#000000" />

    <line x1="500" y1="210" x2="500" y2="250" fill="none" stroke="#000000" stroke-width="2" />
    <polygon points="495,240 505,240 500,250" fill="#000000" />

    <line x1="500" y1="330" x2="500" y2="370" fill="none" stroke="#000000" stroke-width="2" />
    <polygon points="495,360 505,360 500,370" fill="#000000" />
    
    <!-- Decision Yes -->
    <line x1="500" y1="470" x2="500" y2="510" fill="none" stroke="#000000" stroke-width="2" />
    <polygon points="495,500 505,500 500,510" fill="#000000" />
    <text x="510" y="495" font-family="'Times New Roman', Georgia, serif" font-size="16px" font-weight="bold" fill="#000000">Ya</text>
    
    <!-- Decision No -->
    <line x1="570" y1="420" x2="750" y2="420" fill="none" stroke="#000000" stroke-width="2" />
    <polygon points="740,415 740,425 750,420" fill="#000000" />
    <text x="650" y="410" font-family="'Times New Roman', Georgia, serif" font-size="16px" font-weight="bold" fill="#000000" text-anchor="middle">Tidak (Revisi)</text>
    
    <path d="M 825 450 L 825 1290 L 570 1290" fill="none" stroke="#000000" stroke-width="2" stroke-dasharray="6,6" />
    <polygon points="580,1285 580,1295 570,1290" fill="#000000" />

    <line x1="500" y1="590" x2="500" y2="630" fill="none" stroke="#000000" stroke-width="2" />
    <polygon points="495,620 505,620 500,630" fill="#000000" />

    <line x1="500" y1="710" x2="500" y2="750" fill="none" stroke="#000000" stroke-width="2" />
    <polygon points="495,740 505,740 500,750" fill="#000000" />

    <line x1="500" y1="830" x2="500" y2="870" fill="none" stroke="#000000" stroke-width="2" />
    <polygon points="495,860 505,860 500,870" fill="#000000" />

    <line x1="500" y1="970" x2="500" y2="1010" fill="none" stroke="#000000" stroke-width="2" />
    <polygon points="495,1000 505,1000 500,1010" fill="#000000" />

    <line x1="500" y1="1090" x2="500" y2="1130" fill="none" stroke="#000000" stroke-width="2" />
    <polygon points="495,1120 505,1120 500,1130" fill="#000000" />

    <line x1="500" y1="1210" x2="500" y2="1270" fill="none" stroke="#000000" stroke-width="2" />
    <polygon points="495,1260 505,1260 500,1270" fill="#000000" />

    <!-- NODES -->
    <g transform="translate(500, 70)">
        <rect x="-70" y="-20" width="140" height="40" rx="20" ry="20" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="0" y="6" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">MULAI</text>
    </g>

    <g transform="translate(500, 170)">
        <rect x="-250" y="-40" width="500" height="80" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="0" y="-5" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">1. Pengisian Form Dinamis (Mahasiswa)</text>
        <text x="0" y="20" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Input isian teks kaya (Rich Text HTML) via Tiptap Editor</text>
    </g>

    <g transform="translate(500, 290)">
        <rect x="-250" y="-40" width="500" height="80" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="0" y="-5" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">2. Pengecekan Pertama Berkas (Admin Jurusan)</text>
        <text x="0" y="20" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Verifikasi kelayakan &amp; kelengkapan dokumen ajuan</text>
    </g>

    <g transform="translate(500, 420)">
        <path d="M 0 -50 L 70 0 L 0 50 L -70 0 Z" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="0" y="6" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Layak?</text>
    </g>

    <g transform="translate(500, 550)">
        <rect x="-250" y="-40" width="500" height="80" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="0" y="-5" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">3. Pemberian Nomor Surat Otomatis</text>
        <text x="0" y="20" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Nomor diterbitkan otomatis sesuai format jurusan</text>
    </g>

    <g transform="translate(500, 670)">
        <rect x="-250" y="-40" width="500" height="80" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="0" y="-5" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">4. Review &amp; Validasi ULT (Staf ULT)</text>
        <text x="0" y="20" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Validasi keabsahan berkas &amp; proses lanjut</text>
    </g>

    <g transform="translate(500, 790)">
        <rect x="-250" y="-40" width="500" height="80" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="0" y="-5" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">5. Verifikasi &amp; TTD Elektronik (Pejabat)</text>
        <text x="0" y="20" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Persetujuan &amp; penandatanganan dokumen digital</text>
    </g>

    <g transform="translate(500, 920)">
        <rect x="-250" y="-50" width="500" height="100" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="0" y="-20" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">6. Perakitan Dokumen Otomatis (Assembly Core)</text>
        <text x="0" y="5" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Parser HTML-to-OpenXML menerjemahkan tag Tiptap,</text>
        <text x="0" y="30" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">mengkloning run asli placeholder, &amp; merakit .docx</text>
    </g>

    <g transform="translate(500, 1050)">
        <rect x="-250" y="-40" width="500" height="80" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="0" y="-5" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">7. Penyimpanan Berkas &amp; Audit Trail</text>
        <text x="0" y="20" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Menyimpan hasil ke Private Disk, log aksi ke DB</text>
    </g>

    <g transform="translate(500, 1170)">
        <rect x="-250" y="-40" width="500" height="80" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="0" y="-5" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">8. Unduh Berkas Privat (Mahasiswa)</text>
        <text x="0" y="20" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#000000" text-anchor="middle">Akses aman via anti-IDOR middleware</text>
    </g>

    <g transform="translate(500, 1290)">
        <rect x="-70" y="-20" width="140" height="40" rx="20" ry="20" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="0" y="6" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">SELESAI</text>
    </g>

    <g transform="translate(825, 420)">
        <rect x="-75" y="-30" width="150" height="60" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <text x="0" y="6" font-family="'Times New Roman', Georgia, serif" font-size="18px" font-weight="bold" fill="#000000" text-anchor="middle">Ditolak / Draft</text>
    </g>

</svg>
"""
    return svg

def create_erd_svg():
    svg = """<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg width="1300" height="950" xmlns="http://www.w3.org/2000/svg" style="background-color: #ffffff;">

    <!-- TABLE: users -->
    <g transform="translate(50, 50)">
        <rect x="0" y="0" width="280" height="200" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <rect x="0" y="0" width="280" height="35" fill="#000000" stroke="#000000" stroke-width="2" />
        <text x="140" y="25" font-family="'Times New Roman', Georgia, serif" font-size="20px" font-weight="bold" fill="#ffffff" text-anchor="middle">users</text>
        
        <text x="10" y="60" font-family="'Times New Roman', Georgia, serif" font-size="16px" font-weight="bold" fill="#000000">PK</text>
        <text x="45" y="60" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">id</text>
        <text x="270" y="60" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">BIGINT</text>
        <line x1="0" y1="70" x2="280" y2="70" stroke="#dddddd" stroke-width="1.5" />
        
        <text x="45" y="90" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">name</text>
        <text x="270" y="90" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">VARCHAR</text>
        <line x1="0" y1="100" x2="280" y2="100" stroke="#dddddd" stroke-width="1.5" />
        
        <text x="45" y="120" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">email</text>
        <text x="270" y="120" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">VARCHAR</text>
        <line x1="0" y1="130" x2="280" y2="130" stroke="#dddddd" stroke-width="1.5" />

        <text x="45" y="150" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">password</text>
        <text x="270" y="150" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">VARCHAR</text>
        <line x1="0" y1="160" x2="280" y2="160" stroke="#dddddd" stroke-width="1.5" />
        
        <text x="45" y="180" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">created_at</text>
        <text x="270" y="180" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">TIMESTAMP</text>
    </g>

    <!-- TABLE: model_has_roles -->
    <g transform="translate(450, 50)">
        <rect x="0" y="0" width="280" height="140" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <rect x="0" y="0" width="280" height="35" fill="#000000" stroke="#000000" stroke-width="2" />
        <text x="140" y="25" font-family="'Times New Roman', Georgia, serif" font-size="20px" font-weight="bold" fill="#ffffff" text-anchor="middle">model_has_roles</text>
        
        <text x="10" y="60" font-family="'Times New Roman', Georgia, serif" font-size="16px" font-weight="bold" fill="#000000">FK</text>
        <text x="45" y="60" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">role_id</text>
        <text x="270" y="60" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">BIGINT</text>
        <line x1="0" y1="70" x2="280" y2="70" stroke="#dddddd" stroke-width="1.5" />
        
        <text x="45" y="90" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">model_type</text>
        <text x="270" y="90" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">VARCHAR</text>
        <line x1="0" y1="100" x2="280" y2="100" stroke="#dddddd" stroke-width="1.5" />
        
        <text x="10" y="120" font-family="'Times New Roman', Georgia, serif" font-size="16px" font-weight="bold" fill="#000000">FK</text>
        <text x="45" y="120" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">model_id</text>
        <text x="270" y="120" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">BIGINT</text>
    </g>

    <!-- TABLE: roles -->
    <g transform="translate(850, 50)">
        <rect x="0" y="0" width="280" height="110" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <rect x="0" y="0" width="280" height="35" fill="#000000" stroke="#000000" stroke-width="2" />
        <text x="140" y="25" font-family="'Times New Roman', Georgia, serif" font-size="20px" font-weight="bold" fill="#ffffff" text-anchor="middle">roles</text>
        
        <text x="10" y="60" font-family="'Times New Roman', Georgia, serif" font-size="16px" font-weight="bold" fill="#000000">PK</text>
        <text x="45" y="60" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">id</text>
        <text x="270" y="60" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">BIGINT</text>
        <line x1="0" y1="70" x2="280" y2="70" stroke="#dddddd" stroke-width="1.5" />
        
        <text x="45" y="90" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">name</text>
        <text x="270" y="90" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">VARCHAR</text>
    </g>

    <!-- TABLE: services -->
    <g transform="translate(50, 350)">
        <rect x="0" y="0" width="280" height="170" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <rect x="0" y="0" width="280" height="35" fill="#000000" stroke="#000000" stroke-width="2" />
        <text x="140" y="25" font-family="'Times New Roman', Georgia, serif" font-size="20px" font-weight="bold" fill="#ffffff" text-anchor="middle">services</text>
        
        <text x="10" y="60" font-family="'Times New Roman', Georgia, serif" font-size="16px" font-weight="bold" fill="#000000">PK</text>
        <text x="45" y="60" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">id</text>
        <text x="270" y="60" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">BIGINT</text>
        <line x1="0" y1="70" x2="280" y2="70" stroke="#dddddd" stroke-width="1.5" />
        
        <text x="45" y="90" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">name</text>
        <text x="270" y="90" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">VARCHAR</text>
        <line x1="0" y1="100" x2="280" y2="100" stroke="#dddddd" stroke-width="1.5" />
        
        <text x="45" y="120" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">template_path</text>
        <text x="270" y="120" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">VARCHAR</text>
        <line x1="0" y1="130" x2="280" y2="130" stroke="#dddddd" stroke-width="1.5" />

        <text x="45" y="150" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">is_active</text>
        <text x="270" y="150" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">BOOLEAN</text>
    </g>

    <!-- TABLE: requests -->
    <g transform="translate(450, 320)">
        <rect x="0" y="0" width="280" height="260" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <rect x="0" y="0" width="280" height="35" fill="#000000" stroke="#000000" stroke-width="2" />
        <text x="140" y="25" font-family="'Times New Roman', Georgia, serif" font-size="20px" font-weight="bold" fill="#ffffff" text-anchor="middle">requests</text>
        
        <text x="10" y="60" font-family="'Times New Roman', Georgia, serif" font-size="16px" font-weight="bold" fill="#000000">PK</text>
        <text x="45" y="60" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">id</text>
        <text x="270" y="60" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">BIGINT</text>
        <line x1="0" y1="70" x2="280" y2="70" stroke="#dddddd" stroke-width="1.5" />
        
        <text x="10" y="90" font-family="'Times New Roman', Georgia, serif" font-size="16px" font-weight="bold" fill="#000000">FK</text>
        <text x="45" y="90" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">user_id</text>
        <text x="270" y="90" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">BIGINT</text>
        <line x1="0" y1="100" x2="280" y2="100" stroke="#dddddd" stroke-width="1.5" />
        
        <text x="10" y="120" font-family="'Times New Roman', Georgia, serif" font-size="16px" font-weight="bold" fill="#000000">FK</text>
        <text x="45" y="120" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">service_id</text>
        <text x="270" y="120" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">BIGINT</text>
        <line x1="0" y1="130" x2="280" y2="130" stroke="#dddddd" stroke-width="1.5" />

        <text x="45" y="150" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">status</text>
        <text x="270" y="150" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">VARCHAR</text>
        <line x1="0" y1="160" x2="280" y2="160" stroke="#dddddd" stroke-width="1.5" />

        <text x="45" y="180" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">tracking_code</text>
        <text x="270" y="180" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">VARCHAR</text>
        <line x1="0" y1="190" x2="280" y2="190" stroke="#dddddd" stroke-width="1.5" />

        <text x="45" y="210" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">issue_number</text>
        <text x="270" y="210" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">VARCHAR</text>
        <line x1="0" y1="220" x2="280" y2="220" stroke="#dddddd" stroke-width="1.5" />

        <text x="45" y="240" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">created_at</text>
        <text x="270" y="240" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">TIMESTAMP</text>
    </g>

    <!-- TABLE: request_inputs -->
    <g transform="translate(850, 350)">
        <rect x="0" y="0" width="280" height="170" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <rect x="0" y="0" width="280" height="35" fill="#000000" stroke="#000000" stroke-width="2" />
        <text x="140" y="25" font-family="'Times New Roman', Georgia, serif" font-size="20px" font-weight="bold" fill="#ffffff" text-anchor="middle">request_inputs</text>
        
        <text x="10" y="60" font-family="'Times New Roman', Georgia, serif" font-size="16px" font-weight="bold" fill="#000000">PK</text>
        <text x="45" y="60" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">id</text>
        <text x="270" y="60" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">BIGINT</text>
        <line x1="0" y1="70" x2="280" y2="70" stroke="#dddddd" stroke-width="1.5" />
        
        <text x="10" y="90" font-family="'Times New Roman', Georgia, serif" font-size="16px" font-weight="bold" fill="#000000">FK</text>
        <text x="45" y="90" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">request_id</text>
        <text x="270" y="90" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">BIGINT</text>
        <line x1="0" y1="100" x2="280" y2="100" stroke="#dddddd" stroke-width="1.5" />
        
        <text x="45" y="120" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">field_name</text>
        <text x="270" y="120" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">VARCHAR</text>
        <line x1="0" y1="130" x2="280" y2="130" stroke="#dddddd" stroke-width="1.5" />

        <text x="45" y="150" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">field_value</text>
        <text x="270" y="150" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">TEXT</text>
    </g>

    <!-- TABLE: request_documents -->
    <g transform="translate(250, 680)">
        <rect x="0" y="0" width="280" height="200" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <rect x="0" y="0" width="280" height="35" fill="#000000" stroke="#000000" stroke-width="2" />
        <text x="140" y="25" font-family="'Times New Roman', Georgia, serif" font-size="20px" font-weight="bold" fill="#ffffff" text-anchor="middle">request_documents</text>
        
        <text x="10" y="60" font-family="'Times New Roman', Georgia, serif" font-size="16px" font-weight="bold" fill="#000000">PK</text>
        <text x="45" y="60" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">id</text>
        <text x="270" y="60" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">BIGINT</text>
        <line x1="0" y1="70" x2="280" y2="70" stroke="#dddddd" stroke-width="1.5" />
        
        <text x="10" y="90" font-family="'Times New Roman', Georgia, serif" font-size="16px" font-weight="bold" fill="#000000">FK</text>
        <text x="45" y="90" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">request_id</text>
        <text x="270" y="90" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">BIGINT</text>
        <line x1="0" y1="100" x2="280" y2="100" stroke="#dddddd" stroke-width="1.5" />
        
        <text x="45" y="120" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">file_path</text>
        <text x="270" y="120" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">VARCHAR</text>
        <line x1="0" y1="130" x2="280" y2="130" stroke="#dddddd" stroke-width="1.5" />

        <text x="45" y="150" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">file_type</text>
        <text x="270" y="150" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">VARCHAR</text>
        <line x1="0" y1="160" x2="280" y2="160" stroke="#dddddd" stroke-width="1.5" />

        <text x="45" y="180" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">file_size</text>
        <text x="270" y="180" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">INT</text>
    </g>

    <!-- TABLE: audit_trails -->
    <g transform="translate(650, 680)">
        <rect x="0" y="0" width="280" height="170" fill="#ffffff" stroke="#000000" stroke-width="2" />
        <rect x="0" y="0" width="280" height="35" fill="#000000" stroke="#000000" stroke-width="2" />
        <text x="140" y="25" font-family="'Times New Roman', Georgia, serif" font-size="20px" font-weight="bold" fill="#ffffff" text-anchor="middle">audit_trails</text>
        
        <text x="10" y="60" font-family="'Times New Roman', Georgia, serif" font-size="16px" font-weight="bold" fill="#000000">PK</text>
        <text x="45" y="60" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">id</text>
        <text x="270" y="60" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">BIGINT</text>
        <line x1="0" y1="70" x2="280" y2="70" stroke="#dddddd" stroke-width="1.5" />
        
        <text x="10" y="90" font-family="'Times New Roman', Georgia, serif" font-size="16px" font-weight="bold" fill="#000000">FK</text>
        <text x="45" y="90" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">user_id</text>
        <text x="270" y="90" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">BIGINT</text>
        <line x1="0" y1="100" x2="280" y2="100" stroke="#dddddd" stroke-width="1.5" />
        
        <text x="45" y="120" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">action</text>
        <text x="270" y="120" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">VARCHAR</text>
        <line x1="0" y1="130" x2="280" y2="130" stroke="#dddddd" stroke-width="1.5" />

        <text x="45" y="150" font-family="'Times New Roman', Georgia, serif" font-size="18px" fill="#000000">ip_address</text>
        <text x="270" y="150" font-family="'Times New Roman', Georgia, serif" font-size="16px" fill="#555555" text-anchor="end">VARCHAR</text>
    </g>

    <!-- RELATIONSHIPS -->
    <!-- users -> model_has_roles -->
    <path d="M 330 110 L 450 110" fill="none" stroke="#000000" stroke-width="2" />
    <circle cx="335" cy="110" r="3" fill="#000000" />
    
    <!-- roles -> model_has_roles -->
    <path d="M 850 110 L 730 110" fill="none" stroke="#000000" stroke-width="2" />
    <circle cx="845" cy="110" r="3" fill="#000000" />

    <!-- users -> requests (Clean path avoiding services) -->
    <path d="M 190 250 L 190 300 L 520 300 L 520 320" fill="none" stroke="#000000" stroke-width="2" />
    <circle cx="190" cy="255" r="3" fill="#000000" />

    <!-- services -> requests -->
    <path d="M 330 450 L 450 450" fill="none" stroke="#000000" stroke-width="2" />
    <circle cx="335" cy="450" r="3" fill="#000000" />

    <!-- requests -> request_inputs -->
    <path d="M 730 450 L 850 450" fill="none" stroke="#000000" stroke-width="2" />
    <circle cx="735" cy="450" r="3" fill="#000000" />

    <!-- requests -> request_documents -->
    <path d="M 520 580 L 520 630 L 390 630 L 390 680" fill="none" stroke="#000000" stroke-width="2" />
    <circle cx="520" cy="585" r="3" fill="#000000" />

    <!-- users -> audit_trails -->
    <path d="M 50 200 L 25 200 L 25 760 L 650 760" fill="none" stroke="#000000" stroke-width="2" />
    <circle cx="55" cy="200" r="3" fill="#000000" />

</svg>
"""
    return svg


def create_sequence_svg():
    svg = """<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg width="5200" height="2900" xmlns="http://www.w3.org/2000/svg" style="background-color: #ffffff;">
    <defs>
        <marker id="seq-arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="8" markerHeight="8" orient="auto-start-reverse">
            <path d="M 0 0 L 10 5 L 0 10 z" fill="#000000" />
        </marker>
    </defs>

    <!-- LIFELINE HEADERS -->
    <g transform="translate(300, 50)">
        <rect x="-225" y="-50" width="450" height="100" fill="#000000" stroke="#000000" stroke-width="4" />
        <text x="0" y="15" font-family="'Times New Roman', Georgia, serif" font-size="50px" font-weight="bold" fill="#ffffff" text-anchor="middle">Staf ULT / User</text>
        <line x1="0" y1="50" x2="0" y2="2800" fill="none" stroke="#000000" stroke-width="4" stroke-dasharray="12,12" />
    </g>

    <g transform="translate(1500, 50)">
        <rect x="-250" y="-50" width="500" height="100" fill="#000000" stroke="#000000" stroke-width="4" />
        <text x="0" y="15" font-family="'Times New Roman', Georgia, serif" font-size="50px" font-weight="bold" fill="#ffffff" text-anchor="middle">RequestController</text>
        <line x1="0" y1="50" x2="0" y2="2800" fill="none" stroke="#000000" stroke-width="4" stroke-dasharray="12,12" />
    </g>

    <g transform="translate(2700, 50)">
        <rect x="-350" y="-50" width="700" height="100" fill="#000000" stroke="#000000" stroke-width="4" />
        <text x="0" y="15" font-family="'Times New Roman', Georgia, serif" font-size="50px" font-weight="bold" fill="#ffffff" text-anchor="middle">DocumentAssemblerService</text>
        <line x1="0" y1="50" x2="0" y2="2800" fill="none" stroke="#000000" stroke-width="4" stroke-dasharray="12,12" />
    </g>

    <g transform="translate(3900, 50)">
        <rect x="-275" y="-50" width="550" height="100" fill="#000000" stroke="#000000" stroke-width="4" />
        <text x="0" y="15" font-family="'Times New Roman', Georgia, serif" font-size="50px" font-weight="bold" fill="#ffffff" text-anchor="middle">HtmlToOpenXMLParser</text>
        <line x1="0" y1="50" x2="0" y2="2800" fill="none" stroke="#000000" stroke-width="4" stroke-dasharray="12,12" />
    </g>

    <g transform="translate(4800, 50)">
        <rect x="-225" y="-50" width="450" height="100" fill="#000000" stroke="#000000" stroke-width="4" />
        <text x="0" y="15" font-family="'Times New Roman', Georgia, serif" font-size="50px" font-weight="bold" fill="#ffffff" text-anchor="middle">Private Storage</text>
        <line x1="0" y1="50" x2="0" y2="2800" fill="none" stroke="#000000" stroke-width="4" stroke-dasharray="12,12" />
    </g>

    <!-- ACTIVATIONS -->
    <rect x="270" y="200" width="60" height="2600" fill="#f0f0f0" stroke="#000000" stroke-width="4" />
    <rect x="1470" y="200" width="60" height="2400" fill="#f0f0f0" stroke="#000000" stroke-width="4" />
    <rect x="2670" y="550" width="60" height="1850" fill="#f0f0f0" stroke="#000000" stroke-width="4" />
    <rect x="3870" y="1000" width="60" height="750" fill="#f0f0f0" stroke="#000000" stroke-width="4" />
    <rect x="4770" y="2050" width="60" height="350" fill="#f0f0f0" stroke="#000000" stroke-width="4" />

    <!-- SEQUENCE ACTIONS -->
    <line x1="330" y1="400" x2="1470" y2="400" fill="none" stroke="#000000" stroke-width="6" />
    <polygon points="1440,385 1440,415 1470,400" fill="#000000" />
    <text x="370" y="370" font-family="'Times New Roman', Georgia, serif" font-size="60px" fill="#000000">1. clickAssemble(requestId)</text>

    <line x1="1530" y1="600" x2="2670" y2="600" fill="none" stroke="#000000" stroke-width="6" />
    <polygon points="2640,585 2640,615 2670,600" fill="#000000" />
    <text x="1570" y="570" font-family="'Times New Roman', Georgia, serif" font-size="60px" fill="#000000">2. assembleDocument(requestId)</text>

    <path d="M 2730 720 C 2900 720, 2900 800, 2730 800" fill="none" stroke="#000000" stroke-width="6" />
    <polygon points="2760,785 2760,815 2730,800" fill="#000000" />
    <text x="2930" y="770" font-family="'Times New Roman', Georgia, serif" font-size="60px" fill="#000000">3. loadTemplateAndFormData()</text>

    <line x1="2730" y1="1060" x2="3870" y2="1060" fill="none" stroke="#000000" stroke-width="6" />
    <polygon points="3840,1045 3840,1075 3870,1060" fill="#000000" />
    <text x="2770" y="940" font-family="'Times New Roman', Georgia, serif" font-size="60px" fill="#000000">4. writeHtmlToWordRun(</text>
    <text x="2770" y="1010" font-family="'Times New Roman', Georgia, serif" font-size="60px" fill="#000000">   htmlString, placeholderRun)</text>

    <path d="M 3930 1180 C 4100 1180, 4100 1260, 3930 1260" fill="none" stroke="#000000" stroke-width="6" />
    <polygon points="3960,1245 3960,1275 3930,1260" fill="#000000" />
    <text x="4130" y="1230" font-family="'Times New Roman', Georgia, serif" font-size="60px" fill="#000000">5. traverseHtmlAndInsertRuns()</text>

    <path d="M 3930 1380 C 4100 1380, 4100 1460, 3930 1460" fill="none" stroke="#000000" stroke-width="6" />
    <polygon points="3960,1445 3960,1475 3930,1460" fill="#000000" />
    <text x="4130" y="1430" font-family="'Times New Roman', Georgia, serif" font-size="60px" fill="#000000">6. cloneRunPropertiesAndFormat()</text>

    <line x1="3870" y1="1660" x2="2730" y2="1660" fill="none" stroke="#000000" stroke-width="6" stroke-dasharray="15,10" />
    <polygon points="2760,1645 2760,1675 2730,1660" fill="#000000" />
    <text x="2770" y="1630" font-family="'Times New Roman', Georgia, serif" font-size="60px" font-style="italic" fill="#000000">7. return parsedOpenXMLRuns</text>

    <path d="M 2730 1780 C 2900 1780, 2900 1860, 2730 1860" fill="none" stroke="#000000" stroke-width="6" />
    <polygon points="2760,1845 2760,1875 2730,1860" fill="#000000" />
    <text x="2930" y="1830" font-family="'Times New Roman', Georgia, serif" font-size="60px" fill="#000000">8. compileAndZipArchive()</text>

    <line x1="2730" y1="2120" x2="4770" y2="2120" fill="none" stroke="#000000" stroke-width="6" />
    <polygon points="4740,2105 4740,2135 4770,2120" fill="#000000" />
    <text x="2770" y="2000" font-family="'Times New Roman', Georgia, serif" font-size="60px" fill="#000000">9. putFileInPrivateDisk(</text>
    <text x="2770" y="2070" font-family="'Times New Roman', Georgia, serif" font-size="60px" fill="#000000">   filename, docxContent)</text>

    <line x1="4770" y1="2320" x2="2730" y2="2320" fill="none" stroke="#000000" stroke-width="6" stroke-dasharray="15,10" />
    <polygon points="2760,2305 2760,2335 2730,2320" fill="#000000" />
    <text x="2770" y="2290" font-family="'Times New Roman', Georgia, serif" font-size="60px" font-style="italic" fill="#000000">10. return saveSuccess</text>

    <line x1="2670" y1="2520" x2="1530" y2="2520" fill="none" stroke="#000000" stroke-width="6" stroke-dasharray="15,10" />
    <polygon points="1560,2505 1560,2535 1530,2520" fill="#000000" />
    <text x="1570" y="2490" font-family="'Times New Roman', Georgia, serif" font-size="60px" font-style="italic" fill="#000000">11. return successResponse</text>

    <line x1="1470" y1="2720" x2="330" y2="2720" fill="none" stroke="#000000" stroke-width="6" stroke-dasharray="15,10" />
    <polygon points="360,2705 360,2735 330,2720" fill="#000000" />
    <text x="370" y="2690" font-family="'Times New Roman', Georgia, serif" font-size="60px" font-style="italic" fill="#000000">12. redirectWithSuccessToast()</text>
</svg>
"""
    return svg

def main():
    target_dir = Path(r"C:\laragon\www\ult-fkip-unila\docs\skripsi\rancangan_diagram")
    target_dir.mkdir(parents=True, exist_ok=True)
    
    diagrams = {
        "01_diagram_use_case.svg": create_use_case_svg(),
        "02_diagram_arsitektur_sistem.svg": create_arsitektur_svg(),
        "03_diagram_flowchart_dokumen.svg": create_flowchart_svg(),
        "04_diagram_erd_database.svg": create_erd_svg(),
        "05_diagram_sequence_parser.svg": create_sequence_svg()
    }

    for name, content in diagrams.items():
        file_path = target_dir / name
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"Created SVG: {name}")

    try:
        import fitz
        for name in diagrams.keys():
            svg_path = target_dir / name
            jpg_path = target_dir / name.replace(".svg", ".jpg")
            doc = fitz.open(str(svg_path))
            # Base width is already 1200+, standard 150-200 dpi is enough for high-res now
            # To match previous detail, zoom = 3
            matrix = fitz.Matrix(3.0, 3.0)
            pix = doc[0].get_pixmap(matrix=matrix, colorspace=fitz.csRGB)
            pix.save(str(jpg_path))
            print(f"Created JPG: {jpg_path.name}")
    except Exception as e:
        print(f"JPG conversion error: {e}")

if __name__ == "__main__":
    main()
