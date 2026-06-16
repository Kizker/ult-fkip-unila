# Panduan Deployment Laravel Docker ke VPS Hostinger
**Projek:** Web ULT FKIP Unila
**Versi:** 1.0 (Berdasarkan Konfigurasi Docker)

Panduan ini disusun untuk memberikan instruksi *step-by-step* dari awal hingga akhir dalam mendistribusikan projek Laravel Anda menggunakan infrastruktur Docker pada VPS Hostinger (`72.62.127.119`).

---

## 🛑 PERHATIAN PENTING: Pembaruan Konfigurasi Lokal
Sebelum kita mulai mengeksekusi *deployment* di VPS, saya telah **memperbarui** `Dockerfile` dan `docker-compose.yml` Anda secara otomatis di komputer lokal ini untuk menyertakan:
1. **LibreOffice** (dibutuhkan oleh variabel `ULT_SOFFICE_PATH=/usr/bin/soffice` untuk pembuatan PDF).
2. **Ekstensi PHP penting** (`pdo_mysql`, `zip`, dll) agar `php artisan migrate` bisa berjalan.
3. **Integrasi `.env` otomatis** pada file `docker-compose.yml` agar kredensial database di Docker sinkron dengan variabel environment Anda.

**JALANKAN PERINTAH INI DI TERMINAL LOKAL ANDA SEKARANG** untuk menyimpan perubahan tersebut ke GitHub:
```bash
git add Dockerfile docker-compose.yml
git commit -m "fix: Update Dockerfile dengan LibreOffice dan ekstensi PHP, sinkronasi env MariaDB"
git push origin main
```

---

## Fasa 1: Persediaan di VPS Hostinger

### 1. SSH Login ke VPS
Buka Terminal/PowerShell/PuTTY, lalu login ke server Hostinger Anda:
```bash
ssh root@72.62.127.119
```
*(Masukkan password VPS Anda saat diminta)*

### 2. Kemaskini Peladen & Pasang Docker
Setelah berhasil login, jalankan perintah (salin-tempel) berikut ini sekaligus untuk memasang mesin Docker di Ubuntu Anda:

```bash
sudo apt update && sudo apt upgrade -y

# Pasang sijil yang diperlukan
sudo apt install -y ca-certificates curl gnupg

# Tambah GPG key rasmi Docker
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

# Tetapkan repositori Docker
echo \
  "deb [arch="$(dpkg --print-architecture)" signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  "$(. /etc/os-release && echo "$VERSION_CODENAME")" stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Pasang Docker dan Docker Compose
sudo apt update
sudo apt install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin -y

# Beri kebenaran agar Docker boleh dijalankan tanpa 'sudo'
sudo usermod -aG docker $USER
```

### 3. Klon Projek dari GitHub
Siapkan direktori server Anda dan tarik repositori GitHub projek ULT:
```bash
mkdir -p /var/www/fastuser/data/www/ult-fkip-unila.site
cd /var/www/fastuser/data/www/ult-fkip-unila.site
git clone https://github.com/Kizker/ult-fkip-unila.git .
```
*(Catatan: Penggunaan titik `.` di akhir perintah clone berfungsi agar file langsung diekstrak di dalam folder tersebut tanpa membuat folder baru `ult-fkip-unila` di dalamnya).*

---

## Fasa 2: Tetapan Environment (`.env`)

Kita harus membuat file konfigurasi `.env` dengan kredensial yang telah disepakati untuk *production*.

1. Buat file baru bernama `.env`:
```bash
nano .env
```

2. Salin teks berikut seluruhnya dan tempel *(paste)* ke dalam jendela Nano Anda:

```env
APP_NAME="Web ULT FKIP Unila"
APP_ENV=production
APP_KEY="[GENERATE_NEW_KEY_DI_VPS_NANTI]"
APP_DEBUG=false
APP_URL=https://ult-fkip-unila.site
APP_TIMEZONE=Asia/Jakarta

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning
LOG_DAILY_DAYS=14

# Database Config (Telah Disesuaikan untuk Docker)
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=ult_fkip
DB_USERNAME=admin_ult
DB_PASSWORD="[GANTI_PASSWORD_DB_ANDA]"
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Cache/Session/Queue via Database
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

SESSION_LIFETIME=120
SESSION_EXPIRE_ON_CLOSE=false
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.ult-fkip-unila.site
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_PARTITIONED_COOKIE=false

# Email SMTP Config
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=ultfkipunila@gmail.com
MAIL_PASSWORD="[GANTI_DENGAN_PASSWORD_EMAIL_ANDA]"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="ultfkipunila@gmail.com"
MAIL_FROM_NAME="ULT FKIP Unila"

# Google OAuth
GOOGLE_CLIENT_ID="[GANTI_DENGAN_GOOGLE_CLIENT_ID_ANDA]"
GOOGLE_CLIENT_SECRET="[GANTI_DENGAN_GOOGLE_CLIENT_SECRET_ANDA]"
GOOGLE_REDIRECT_URI=https://ult-fkip-unila.site/auth/google/callback
GOOGLE_STATELESS=true

# Recaptcha
RECAPTCHA_ENABLED=false
RECAPTCHA_SITE_KEY=
RECAPTCHA_SECRET_KEY=

# Storage Config
FILESYSTEM_DISK=local
PRIVATE_FILESYSTEM_DISK=private
PUBLIC_DISK_ROOT=public
FILESYSTEM_CLOUD=s3

S3_ACCESS_KEY_ID=
S3_SECRET_ACCESS_KEY=
S3_DEFAULT_REGION=ap-southeast-1
S3_BUCKET=
S3_URL=
S3_ENDPOINT=
S3_USE_PATH_STYLE_ENDPOINT=false

# ULT configs (LibreOffice dsb)
ULT_LEGALIZATION_BASE_URL=https://ult-fkip-unila.site
ULT_DOC_NUMBER_FORMAT_KEY=default
ULT_SOFFICE_PATH=/usr/bin/soffice
ULT_PREVIEW_AS_PDF=false

# Deploy metadata (reference)
DEPLOY_DOMAIN=ult-fkip-unila.site
DEPLOY_VPS_HOST=srv1450038.hstgr.cloud
DEPLOY_VPS_IP=72.62.127.119
```

3. Simpan dengan menekan tombol **`Ctrl + X`**, lalu ketik **`Y`**, dan tekan **`Enter`**.

---

## Fasa 3: Eksekusi dan Konfigurasi Kontena

Kini semua pengaturan telah siap, mari kita jalankan server.

### 1. Bina dan Jalankan Docker
Menjalankan *image* Docker. Proses ini akan mengunduh MariaDB, Nginx, PHP, LibreOffice, dan akan memakan waktu beberapa menit.
```bash
docker compose up -d --build
```

### 2. Masuk ke dalam Kontena Laravel
Setelah Docker menyala (`done`), kita harus masuk ke dalam kontena aplikasi PHP:
```bash
docker exec -it ult_fkip_unila_app sh
```

### 3. Eksekusi Arahan Pra-Peluncuran (Composer & Artisan)
Di dalam layar kontena `ult_fkip_unila_app`, jalankan blok ini berurutan:
```bash
# 1. Unduh library vendor PHP
composer install --no-dev --optimize-autoloader --no-interaction

# 2. Sinkronkan Storage (Public)
php artisan storage:link

# 3. Migrasi Database dan Seed
php artisan migrate --force
# (Tambahkan 'php artisan db:seed --force' jika Anda butuh Dummy Data)
```

### 4. Selaraskan Hak Akses (Permissions)
Berikan izin ke Nginx dan PHP agar bisa membaca dan menulis file penyimpanan dokumen:
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 5. Optimasi Kinerja Aplikasi
Pembersihan memori agar Laravel berjalan sangat cepat di Production:
```bash
php artisan optimize:clear
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Selesai!
Ketik `exit` dan tekan Enter untuk keluar dari mode kontena.
```bash
exit
```

---

Sistem kini telah sedia. Anda dapat langsung mengaksesnya melalui domain yang dituju (`https://ult-fkip-unila.site` - pastikan domain sudah di-point ke IP VPS dan SSL terpasang, misal via reverse proxy Nginx host) atau melalui IP Public VPS.
