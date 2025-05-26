FROM php:8.1-fpm

# Instala dependencias necesarias y extensiones PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia solo los archivos para composer para aprovechar cache de Docker
COPY composer.json composer.lock ./

# Instala dependencias PHP con scripts para assets y cache
RUN composer install --no-dev --optimize-autoloader

# Copia el resto del proyecto
COPY . .

# Establece permisos para cache y logs (ajusta según usuario si es necesario)
RUN chown -R www-data:www-data var/cache var/log

# Limpia cache de Symfony y ejecuta migraciones
RUN php bin/console cache:clear --env=prod --no-debug \
    && php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

CMD ["php-fpm"]
