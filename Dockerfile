FROM php:8.2-fpm

# 1. Installation des dépendances système et extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev \
    libxml2-dev libzip-dev nginx \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# 3. Copie des fichiers du projet
COPY . .

# 4. Installation des dépendances Laravel sans les packages de dev
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 5. Gestion stricte des permissions pour éviter l'erreur de cache
# On donne la propriété des fichiers à l'utilisateur www-data (le serveur web)
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

# 6. Commande de démarrage sécurisée
# Note : Les migrations se font généralement via l'interface de l'hébergeur, 
# mais si tu dois les laisser ici, on retire au moins le db:seed automatique.
CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan serve --host=0.0.0.0 --port=8000