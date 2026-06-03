# ==========================================
# ÉTAPE 1 : Le Build avec l'image officielle de Composer
# ==========================================
FROM composer:latest AS builder

WORKDIR /app

# On copie uniquement les fichiers nécessaires aux dépendances
COPY composer.json composer.lock* ./

# On installe les dépendances à l'abri dans l'image Composer
RUN COMPOSER_MEMORY_LIMIT=-1 composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --ignore-platform-reqs

# ==========================================
# ÉTAPE 2 : L'image finale de production PHP
# ==========================================
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev \
    libxml2-dev libzip-dev nginx \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

# On copie tout le code source du projet
COPY . .

# Magie : On récupère le dossier vendor généré proprement à l'étape 1
COPY --from=builder /app/vendor /var/www/vendor

# Fix des permissions pour Laravel
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

# Commande de démarrage finale
CMD php artisan migrate --force && \
    php artisan config:cache && \
    php artisan view:cache && \
    php artisan serve --host=0.0.0.0 --port=8000