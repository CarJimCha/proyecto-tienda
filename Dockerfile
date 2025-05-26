# Usa una imagen oficial de PHP con extensiones necesarias
FROM php:8.1-fpm

# Instala dependencias del sistema
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev libxrender1 libfontconfig1 xfonts-75dpi xfonts-base wget \
    && docker-php-ext-install pdo pdo_pgsql zip \
   wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox_0.12.6-1.buster_amd64.deb \
   && dpkg -i wkhtmltox_0.12.6-1.buster_amd64.deb || apt-get install -f -y \
   && rm wkhtmltox_0.12.6-1.buster_amd64.deb \


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
