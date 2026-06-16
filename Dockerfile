FROM php:8.2-fpm

# Memasang package yang diperlukan
RUN apt-get update && apt-get install -y git curl zip unzip

# Memasang Composer
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

# Menetapkan direktori kerja
WORKDIR /var/www/html

# Menyalin fail projek
COPY . .

# Membuka port 9000 untuk PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
