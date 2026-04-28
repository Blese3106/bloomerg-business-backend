FROM php:8.2-fpm

# Installer dépendances système
RUN apt-get update && apt-get install -y \
    git curl zip unzip nginx supervisor \
    libpng-dev libonig-dev libxml2-dev libpq-dev \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer extensions PHP
RUN docker-php-ext-install \
    pdo pdo_pgsql pgsql \
    mbstring exif pcntl bcmath gd zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copier fichiers composer (optimisation cache)
COPY composer.json composer.lock ./

# Installer dépendances Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copier tout le projet
COPY . .

# Permissions Laravel
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

# Config Nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Config Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Script de démarrage
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]