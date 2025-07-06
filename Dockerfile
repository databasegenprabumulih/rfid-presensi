# Gunakan PHP + Apache image resmi
FROM php:8.2-apache

# Salin semua file website ke folder web server Apache di Docker
COPY . /var/www/html/

# Aktifkan ekstensi mysqli (agar bisa koneksi ke MySQL)
RUN docker-php-ext-install mysqli

# Set zona waktu
RUN echo "date.timezone=Asia/Jakarta" > /usr/local/etc/php/conf.d/timezone.ini
