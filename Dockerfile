# Dùng image PHP 8.2 + Apache
FROM php:8.2-apache

# Cài extension MySQL (mysqli, pdo_mysql)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy toàn bộ mã nguồn vào thư mục web của Apache
COPY . /var/www/html/

# Phân quyền cho Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Mở cổng 80 để Render truy cập
EXPOSE 80
