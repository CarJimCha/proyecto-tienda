# Usa una imagen oficial de PHP con extensiones necesarias
FROM php:8.1-fpm

# Instala dependencias del sistema
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev wkhtmltopdf wkhtmltoimage \
    && docker-php-ext-install pdo pdo_pgsql zip

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia el proyecto
WORKDIR /var/www/html
COPY . .

# Da permisos a var y vendor
RUN mkdir -p var && chmod -R 777 var

# Instala dependencias de PHP
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Usa Symfony CLI para producción si quieres (opcional)
# RUN curl -sS https://get.symfony.com/cli/installer | bash && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Configura el entrypoint
CMD ["php-fpm"]
