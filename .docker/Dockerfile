FROM php:8.1-fpm

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libonig-dev libzip-dev zip nginx supervisor \
    && docker-php-ext-install intl mbstring zip pdo pdo_mysql

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configuración de Nginx y supervisor
COPY .docker/nginx.conf /etc/nginx/sites-available/default
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Establecer el directorio de trabajo
WORKDIR /app

# Copiar la app
COPY . .

# Instalar dependencias PHP sin dev
RUN composer install --no-dev --optimize-autoloader --no-scripts --prefer-dist --no-interaction

# Limpiar caché Symfony
RUN php bin/console cache:clear --env=prod --no-warmup --verbose

# Exponer el puerto 80 (para HTTP)
EXPOSE 80

# Comando para iniciar tanto PHP-FPM como Nginx
CMD ["/usr/bin/supervisord"]
