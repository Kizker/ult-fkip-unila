# Figma Handoff: Replica UI Web ULT (Current Implementation)

Dokumen ini dipakai untuk merekonstruksi tampilan web publik ULT FKIP Unila di Figma berdasarkan implementasi saat ini.

## File Mirror Siap Import

- HTML mirror siap import: `docs/figma/ult-public-home-mirror.html`
- File ini memakai class dan stylesheet project saat ini (`resources/css/app.css`) agar struktur visual dekat dengan implementasi asli.
- HTML mirror semua view public (sekali import): `docs/figma/ult-public-all-views-mirror.html`
  - Mencakup: About, Services index/detail, Announcements index/detail, Blog index/detail, Feedback create, User Guides index/detail.
- HTML mirror per halaman (import terpisah):
  - `docs/figma/public/home-mirror.html`
  - `docs/figma/public/about-mirror.html`
  - `docs/figma/public/services-index-mirror.html`
  - `docs/figma/public/services-show-mirror.html`
  - `docs/figma/public/announcements-index-mirror.html`
  - `docs/figma/public/announcements-show-mirror.html`
  - `docs/figma/public/blog-index-mirror.html`
  - `docs/figma/public/blog-show-mirror.html`
  - `docs/figma/public/feedback-create-mirror.html`
  - `docs/figma/public/user-guides-index-mirror.html`
  - `docs/figma/public/user-guides-show-mirror.html`

Semua file pada folder `docs/figma/public/` terbaru telah diambil dari hasil render route Laravel lokal (`http://127.0.0.1:8099`) agar struktur section mengikuti tampilan web yang berjalan.

## 1) Frames & Grid

- Desktop frame: `1440 x 4200` (atau auto-height sesuai konten).
- Tablet frame: `1024 x 3600`.
- Mobile frame: `390 x 4200`.
- Content container max width: `1472px` (`92rem`).
- Container horizontal padding:
  - Desktop: `28px`
  - Tablet/mobile: gunakan `clamp(14px - 28px)`, praktis: `16px` mobile, `24px` tablet.
- Header sticky height acuan: `72px`.

## 2) Design Tokens (Light)

- `--c-bg`: `rgb(255,255,255)`
- `--c-fg`: `rgb(17,24,39)`
- `--c-card`: `rgb(255,255,255)`
- `--c-border`: `rgb(226,232,240)`
- `--c-muted`: `rgb(100,116,139)`
- `--c-primary`: `rgb(124,58,237)`
- `--c-primary2`: `rgb(168,85,247)`
- `--home-secondary`: `rgb(14,165,233)`
- `--home-indigo`: `rgb(99,102,241)`
- `--home-focus`: `rgb(124,58,237)`

## 3) Typography

- Family utama: `Poppins` (fallback: `Avenir Next`, `Segoe UI`, `Tahoma`, sans-serif).
- Heading hero:
  - Weight: `800`
  - Size: `clamp(32px - 64px)` (desktop gunakan `64px`, mobile `36px`).
  - Line height: `1.04`
- Section title:
  - Weight: `760`
  - Size: `clamp(20.8px - 26.4px)` (desktop `26px`, mobile `22px`)
- Body/subtitle:
  - Size: `14.5px - 16px`
  - Line height: `1.52 - 1.6`
- Kicker:
  - Size: `12px`
  - Weight: `700`
  - Letter spacing: `0.06em`
  - Uppercase.

## 4) Global Background

Body background adalah layered radial + linear gradient:
- Radial ungu di kiri atas.
- Radial violet di kanan atas.
- Radial ungu lembut di kanan bawah.
- Base putih.

Di Figma: buat 1 rect full page, isi 4 fill (3 radial + 1 linear).

## 5) Header (Public Navbar)

- Position: sticky top.
- Backdrop: putih translucent + blur (`20px`) + saturate.
- Shadow:
  - `0 10 28 rgba(15,23,42,.10)`
  - `0 14 22 -18 rgba(15,23,42,.40)`
  - inset top highlight.
- Brand:
  - 2 logo sejajar horizontal (`gap ~6-7px`).
  - Badge `ULT` pill gradient (primary -> primary2), text putih.
  - Label `FKIP Unila` (hidden di mobile kecil).
- Nav link:
  - Normal: dark text 82%.
  - Hover: background `rgba(0,0,0,.04)`.
  - Active: gradient text (primary -> primary2), weight `700`.
- Action buttons:
  - `ID`, `EN`, theme toggle, login/register/dashboard.
- Mobile:
  - Hamburger button.
  - Collapsible nav panel dengan animasi slide/fade.

## 6) Hero Section (Homepage `.ult-home`)

- Full viewport width (`100vw`) dan tinggi (`100svh`).
- Offset naik setinggi header (`-72px`) agar full-bleed.
- Slide structure:
  - Background layer gradient gelap-violet-biru.
  - Overlay layer gelap.
  - Centered content.
- Hero content:
  - Title max width `20ch`.
  - Subtitle max width `66ch`.
  - CTA row:
    - Primary button min width `182px`.
    - Secondary button min width `158px`.
  - Stats row:
    - 3 card, grid columns 3.
    - Card radius `17px`, border putih translucent, glassmorphism.
- Dots slider:
  - Normal dot `10x10`.
  - Active dot `22x10` gradient.

## 7) Section: Announcements

- Top margin section: `clamp(30px - 56px)`.
- Carousel layout: `prev button | horizontal track | next button`.
- Card:
  - Radius `22px`
  - Border: `home-border` 78%
  - Background putih gradient
  - Shadow `0 16 30 rgba(15,23,42,.10)`
- Media:
  - Aspect ratio `16:9`.
- Body padding: `14px`.
- Title clamp 2 lines, desc clamp 3 lines.
- Dot pagination:
  - Default `10x10`
  - Active `20x10` gradient.

## 8) Section: Most Used Services

- Grid desktop:
  - `3 columns`, row height `168px`, gap `14px`.
  - Card #1 span `2x2`, card #2 span `1x2`.
- Card:
  - Radius `20px`
  - Shadow `0 14 26 rgba(15,23,42,.14)`
  - Overlay gradient gelap di bawah untuk text.
- Chip kategori:
  - Pill radius `999px`
  - Background ungu gelap gradient + stroke tipis.
  - Font `~11px`, uppercase, weight `700`.

## 9) Section: Blog

- Grid desktop `2 columns`, gap `14px`.
- Card:
  - Radius `22px`
  - Border `home-border` 80%
  - 2 kolom internal: media (`min 132px / 38%`) + content.
- Media:
  - Margin `8px`, radius `16px`, min-height `156px`.
- Body padding: `12px 14px 12px 4px`.

## 10) Footer

- Base background: deep purple gradient multi-layer.
- Mesh blobs:
  - 4 blur radial blobs (`a,b,c,d`) dengan opacity ~0.62.
  - Blend mode: `Screen`.
- Inner content:
  - Top: 2 kolom (brand + link columns).
  - Bottom: copyright + meta links.
- Text:
  - Dominan putih (`~92%`) dengan subtle text shadow.
- Top border line di area bottom: `rgba(221,214,254,.24)`.

## 11) Responsive Rules (Wajib)

- `<=1100px`:
  - Announcement cards jadi 2 per view.
  - Service grid jadi 2 kolom.
  - Blog jadi 1 kolom.
- `<=760px`:
  - Hero CTA jadi stacked full width.
  - Announcement nav arrows hide.
  - Announcement card 1 per view.
  - Service grid 1 kolom (`row ~220px`).
  - Blog card jadi 1 kolom (media di atas).
- `<=420px`:
  - Brand mark diperkecil.
  - Logo lebih kecil.

## 12) Auto Layout Structure (Figma)

- `Frame / Page Public Site`
  - `Header (fixed top)`
  - `Main`
    - `Home / Hero`
    - `Section / Announcements`
    - `Section / Services`
    - `Section / Blogs`
  - `Footer`

Gunakan auto-layout vertikal untuk `Main` dan setiap section. Terapkan `hug contents` untuk komponen kecil (chip, nav link, button), dan `fill container` untuk card container.

## 13) Source Mapping (Code)

- Layout public: `resources/views/layouts/public.blade.php`
- Navbar: `resources/views/components/public/navbar.blade.php`
- Footer: `resources/views/components/public/footer.blade.php`
- Home page: `resources/views/public/home.blade.php`
- Styling public + home:
  - `resources/css/app.css` (blok `:root`, `.page-public-site`, `.page-public-home`, `.page-public-home.ult-home`)
