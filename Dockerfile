 # 1. Base: PHP 8.1 con Apache
FROM php:8.1-apache

# 2. Instala herramientas y extensiones necesarias
RUN apt-get update && apt-get install -y \
    git zip unzip libicu-dev libonig-dev libzip-dev libpq-dev \
    wkhtmltopdf xfonts-75dpi xfonts-base \
    && docker-php-ext-install intl pdo pdo_pgsql zip opcache \
    && rm -rf /var/lib/apt/lists/*

# 3. Instala Composer (gestor de dependencias de PHP)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4. Directorio de trabajo
WORKDIR /var/www/html

# 5. Copia los ficheros de definición de dependencias primero
COPY composer.json composer.lock ./

# 7. Copia el resto de tu aplicación
COPY . .

# Instalar Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# 6. Instala las dependencias sin los paquetes de desarrollo

# Antes del composer install
RUN useradd -m symfony && chown -R symfony:symfony /app

# Cambia de usuario
USER symfony

# Ejecuta Composer sin plugins bloqueados
ENV APP_ENV=prod
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Si necesitas volver a root:
USER root
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/vendor

# 8. Genera los assets (si usas Webpack Encore)
# RUN yarn install && yarn encore production

# 9. Ejecuta las migraciones (opcionalmente en tiempo de build)
# RUN php bin/console doctrine:migrations:migrate --no-interaction --no-script

# 10. Exponer el puerto que utilizará PHP-FPM
EXPOSE 80

# 11. Comando por defecto al iniciar el contenedor
# CMD ["apache2-foreground"]
