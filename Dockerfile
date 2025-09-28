FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev unzip zip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

COPY . /var/www/html

# Permissions avant composer
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Installer Laravel sans scripts
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Générer APP_KEY et exécuter package:discover
#RUN php artisan key:generate --ansi
#RUN php artisan package:discover --ansi

EXPOSE 10000
CMD php artisan serve --host 0.0.0.0 --port 10000
