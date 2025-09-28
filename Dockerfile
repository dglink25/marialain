# Image PHP avec Apache
FROM php:8.2-apache

# Installer extensions PHP nécessaires pour Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev unzip zip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Copier le projet Laravel
COPY . /var/www/html

# Définir DocumentRoot sur le dossier public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Créer les dossiers nécessaires et donner les permissions
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Installer Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Installer les dépendances Laravel sans exécuter les scripts
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Exposer le port utilisé par Render
EXPOSE 10000
ENV PORT=10000

# Lancer Apache
CMD ["apache2-foreground"]
