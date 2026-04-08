FROM php:8.2-apache

# Cài đặt extension cho phép kết nối MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Bật alias module config cho apache (định hướng route api.php)
RUN a2enmod rewrite

# Thiết lập thư mục làm việc mặc định
WORKDIR /var/www/html
