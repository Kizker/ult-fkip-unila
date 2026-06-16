# Web ULT FKIP Universitas Lampung — Fullpower

Template production-ready untuk **Web ULT FKIP Universitas Lampung** berbasis **Laravel 12 (PHP 8.4+)** dengan:
- Portal Publik (beranda, katalog layanan, detail layanan, tentang ULT, blog/pengumuman)
- Portal Mahasiswa (permohonan dinamis, riwayat, detail/timeline auditable, notifikasi, download output private)
- Portal Admin/Staf (dashboard operasional, manajemen permohonan, review ULT gatekeeper, master layanan + workflow, CMS, audit log)

UI mengadaptasi pola layout dashboard dari aset referensi:
`/mnt/data/wowdash-tailwind-bootstrap-react-next-django-2026-01-24-16-08-49-utc.zip` (bagian Laravel),
namun implementasi final **menggunakan Blade + Tailwind + Alpine** (tanpa React/Next/Django).

---

## Stack

- Backend: Laravel 12 + PHP 8.4+
- Auth: Laravel Breeze (Blade) + email verification + reset password + throttle
- RBAC: spatie/laravel-permission + Laravel Policies + permission middleware
- DB: MySQL (dev/prod), SQLite untuk test
- Frontend: Blade + Vite + TailwindCSS + Alpine.js
- WYSIWYG CMS: Tiptap (open-source) + sanitasi allowlist server-side (anti-XSS)
- Storage: local **private** (default) + siap migrasi S3-compatible
- PWA: manifest + service worker + offline fallback (tanpa cache route sensitif)
- Notifikasi: database notifications + UI notification center
- Security-by-default: security headers, rate limit, audit trail, validasi ketat, transaksi atomik untuk workflow kritikal

---

## Akun seed (development)

- Superadmin:
  - Email: `superadmin@example.com`
  - Password: `SuperAdmin@12345!`

Akun demo lain (Mahasiswa/Admin/Approver/Staf ULT) dibuat melalui seeder (lihat output `php artisan db:seed`).

---

## Instalasi & Run (Dev)

1) Setup environment
```bash
cp .env.example .env
```

2) Install dependencies
```bash
composer install
npm install
```

3) Generate key + migrate + seed
```bash
php artisan key:generate
php artisan migrate:fresh --seed
```

4) Run
```bash
npm run dev
php artisan serve
```

Akses:
- Publik: `/`
- Login: `/login`
- Mahasiswa: `/mahasiswa/dashboard`
- Admin/Staf: `/admin/dashboard`

> Jika menggunakan Laragon/Nginx: pastikan **document root** ke folder `public/`.

---

## Build Production

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Public Upload Path (cPanel/Shared Hosting)

Agar file upload publik langsung tersimpan ke `public/storage` (tanpa symlink), set:

```bash
PUBLIC_DISK_ROOT=public
```

Mode alternatif (standar Laravel, butuh `php artisan storage:link`):

```bash
PUBLIC_DISK_ROOT=storage
```

---

## SMTP Setup (Mailtrap dev)

Atur di `.env`:
- `MAIL_MAILER=smtp`
- `MAIL_HOST=...`
- `MAIL_PORT=...`
- `MAIL_USERNAME=...`
- `MAIL_PASSWORD=...`
- `MAIL_FROM_ADDRESS=no-reply@ult-fkip.unila.ac.id`
- `MAIL_FROM_NAME="ULT FKIP Universitas Lampung"`

Fitur yang bergantung email:
- Email verification (Breeze)
- Reset password (Breeze)
- Throttle pada endpoint auth (lihat `RateLimiter`)

---

## CMS Publik

Menu admin:
- CMS Dashboard: `/admin/cms`
- Hero banners: `/admin/cms/hero`
- Kategori: `/admin/cms/categories`
- Post (Blog/Pengumuman): `/admin/cms/posts`
- Site settings: `/admin/cms/settings`

i18n konten CMS:
- Semua konten disimpan dua bahasa: `*_id` dan `*_en`.
- Admin mengedit via tab Bahasa (ID/EN).
- Publik menampilkan sesuai locale (`/locale/id`, `/locale/en`).

Tentang ULT:
- Implementasi dipilih: **site_settings**
  - `about_ult_html_id`
  - `about_ult_html_en`

---

## Format Nomor Dokumen (Master Data CRUD)

DATA TIDAK TERSEDIA: Format nomor dokumen final FKIP.

Solusi:
- Tersedia master data **Format Nomor** per unit agar admin Prodi/Jurusan/Fakultas bisa menyesuaikan sendiri:
  - Menu: `/admin/document-number-formats`
  - Scope: hanya unit yang menjadi tanggung jawabnya (Superadmin bisa semua)
- Fallback jika belum ada format per unit:
  - `site_settings.doc_number_format_<format_key>`
  - lalu `site_settings.doc_number_format_default`

Placeholder yang didukung:
- `{SEQ}` atau `{SEQ:n}` (padding)
- `{UNIT_CODE}` (units.code)
- `{YYYY}` tahun
- `{MM}` bulan

Default fallback:
- `{SEQ:3}/ULT-FKIP/{UNIT_CODE}/{YYYY}`

---

## SOP Operasional Staf ULT (Ringkas)

1) Pantau antrean:
- `/admin/dashboard` (KPI ringkas dan antrean)

2) Review ULT (gatekeeper):
- `/admin/ult/review`
- Aksi:
  - Teruskan (jika butuh ttd fakultas: ke `MENUNGGU_TTD_FAKULTAS`)
  - Minta perbaikan (status `PERLU_PERBAIKAN`)
  - Tolak (status `DITOLAK`)
- Aturan ketat:
  - **Transisi REVIEW_ULT → MENUNGGU_TTD_FAKULTAS hanya Staf ULT** (permission `requests.review_ult`)

3) Terbit nomor dokumen:
- Mengikuti `issue_number_at_step` pada workflow layanan
- Sistem concurrency-safe (transaction + locking)

4) Upload output:
- Upload output tetap **private storage** (download via controller + policy)

---

## Tambah Unit (Jurusan/Prodi) + Buat Adminnya

1) Tambah unit:
- Menu: `/admin/units`
- Tipe: fakultas / jurusan / prodi
- Set `parent_id` untuk membentuk struktur (misal Prodi parent = Jurusan)

2) Buat admin unit:
- Superadmin membuka `/admin/users`
- Set `unit_id` ke unit target
- Assign role:
  - Admin Prodi / Admin Jurusan / Staf ULT / Approver Unit / Approver Fakultas

---

## Dummy DOCX untuk Uji Upload (WAJIB)

File dummy `.docx` (CONTOH/DUMMY) tersedia:
- `storage/app/seed/dummy-docx/`
- `public/demo-files/` (**DEV ONLY**, jangan dipakai untuk data sensitif / produksi)

Generate ulang:
```bash
php artisan ult:generate-dummy-docx --force
```

Library: `phpoffice/phpword`
- Open-source
- Output `.docx` macro-free
- Styling minimal (judul + paragraf)

Langkah uji coba:
1) Login sebagai Mahasiswa demo
2) Pilih layanan contoh, buat permohonan
3) Upload file dari `public/demo-files/`
4) Pastikan file tersimpan di private storage dan hanya pihak berwenang yang dapat download
5) Pastikan audit log mencatat event upload

---

## Testing

```bash
php artisan test
```

Test minimal mencakup:
- Ownership policy (mahasiswa tidak akses request orang lain)
- Unit scope (admin prodi/jurusan hanya unitnya)
- Gatekeeper ULT (REVIEW_ULT → MENUNGGU_TTD_FAKULTAS hanya Staf ULT)
- Document number uniqueness (simulasi concurrency)
- Workflow B end-to-end sampai nomor terbit
- Workflow C end-to-end + legalisir hanya muncul setelah ttd lengkap
- Private download unauthorized 403; authorized success
- Audit log: perubahan permission + download tercatat

---

## Deploy Nginx (Contoh)

Server block (ringkas):
- Root: `/var/www/web-ult-fkip/current/public`
- PHP-FPM: PHP 8.4
- Deny akses:
  - `/.env`, `/storage`, `/vendor`
- Tambahkan header keamanan (atau andalkan middleware + Nginx)

Pastikan permission:
- `storage/` dan `bootstrap/cache/` writable oleh user web server

---

## Hardening Checklist

- `APP_ENV=production`, `APP_DEBUG=false`
- HTTPS aktif, HSTS di level server/proxy
- Secure cookies + SameSite (Laravel)
- Pastikan rate-limit aktif (login/register/upload/download/status change/approval/forward)
- Pastikan `public/demo-files/` dihapus/di-block pada produksi
- Rotasi log dan backup DB
- Review CSP sesuai domain aset final
