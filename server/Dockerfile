FROM php:8.2-apache

# cài đặt thư viện và extension pdo, pdo_mysql, zip
RUN apt-get update && apt-get install -y libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Bật alias module config cho apache (định hướng route api.php)
RUN a2enmod rewrite

# cài đặt cấu hình php cho upload file lớn
RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

# Thiết lập thư mục làm việc mặc định
WORKDIR /var/www/html
