# 1. Base: PHP 8.1 con Apache
FROM php:8.1-apache

# 2. Instala herramientas y extensiones necesarias
RUN apt-get update && apt-get install -y \
    git zip unzip libicu-dev libonig-dev libzip-dev libpq-dev \
    wkhtmltopdf xfonts-75dpi xfonts-base \
    && docker-php-ext-install intl pdo pdo_pgsql zip opcache \
    && rm -rf /var/lib/apt/lists/*

# 3. Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4. Directorio de trabajo
WORKDIR /var/www/html

# 5. Copia ficheros composer
COPY composer.json composer.lock ./

# 7. Copia el resto del código
COPY . .

# 6. Permitir Composer como root y luego instalar dependencias
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 8. Asigna permisos a Apache
RUN chown -R www-data:www-data /var/www/html

# 9. Instala Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# 10. Expone el puerto de Apache
EXPOSE 80

# 11. Comando por defecto
CMD ["apache2-foreground"]
