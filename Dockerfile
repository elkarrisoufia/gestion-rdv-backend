FROM php:8.2-fpm

# 1. Installation des dépendances Linux et des extensions PHP requises
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev nginx \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Récupération de Composer officiel
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Définition du dossier de travail et copie du projet
WORKDIR /var/www
COPY . .

# 4. Installation des packages sans optimisations agressives pour éviter le blocage
RUN composer install --no-dev --no-interaction --no-scripts --ignore-platform-reqs

# 5. Droits d'accès pour Laravel
RUN chown -R www-data:www-data /var/www && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

# 6. Démarrage et mise en cache automatique
CMD php artisan migrate --force && php artisan config:cache && php artisan view:cache && php artisan serve --host=0.0.0.0 --port=8000