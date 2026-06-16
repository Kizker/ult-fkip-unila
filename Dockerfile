FROM php:8.4-fpm

# Memasang package yang diperlukan (termasuk LibreOffice untuk export dokumen)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libreoffice \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev

# Memasang ekstensi PHP yang dibutuhkan Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Memasang Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Menetapkan direktori kerja
WORKDIR /var/www/html

# Menyalin fail projek
COPY . .

# Membuka port 9000 untuk PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
