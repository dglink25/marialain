# Base PHP + Apache
FROM php:8.2-apache

# Installer les extensions PHP nécessaires à Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Copier le projet
COPY . /var/www/html

# Installer Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Installer les dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Permissions correctes
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exposer le port
EXPOSE 10000

# Lancer Laravel
CMD php artisan serve --host 0.0.0.0 --port 10000
