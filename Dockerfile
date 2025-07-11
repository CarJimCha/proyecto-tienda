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
ENV APP_ENV=prod
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Solución al error de importmap
RUN php bin/console importmap:install

# 8. Asigna permisos a Apache
RUN chown -R www-data:www-data /var/www/html

# 9. Instala Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# 10. Expone el puerto de Apache
EXPOSE 80

# 11. Configura Apache para Symfony
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2enmod rewrite \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Comando de inicio
CMD ["apache2-foreground"]
