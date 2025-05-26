FROM php:8.1-fpm

# Instala herramientas necesarias y extensiones PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala Composer (si no está instalado en la imagen base)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

CMD ["php-fpm"]
