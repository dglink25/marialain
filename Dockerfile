FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev unzip zip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Copier projet
COPY . /var/www/html

# Définir DocumentRoot sur public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-scripts

EXPOSE 10000
ENV PORT=10000

# Lancer Apache
CMD ["apache2-foreground"]
