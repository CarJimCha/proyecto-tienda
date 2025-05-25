# Usa PHP 8.1 con FPM
FROM php:8.1-fpm

# Instala dependencias del sistema necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install intl mbstring zip pdo pdo_mysql

# Copia Composer desde la imagen oficial de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Establece directorio de trabajo
WORKDIR /app

# Copia todo el código al contenedor
COPY . .

# Ejecuta composer install con verbose y sin scripts para depurar
RUN composer clear-cache && \
    composer install --no-dev --optimize-autoloader --no-scripts --verbose --no-interaction --prefer-dist

# Limpia cache de Symfony en entorno prod y con verbose
RUN php bin/console cache:clear --env=prod --no-warmup --verbose

# Expone el puerto 9000 para PHP-FPM
EXPOSE 9000

# Comando por defecto para arrancar PHP-FPM
CMD ["php-fpm"]
