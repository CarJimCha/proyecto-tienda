FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev libpng-dev libonig-dev libxml2-dev libcurl4-openssl-dev \
    nginx supervisor curl zip \
    && docker-php-ext-install pdo pdo_pgsql zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# Instala dependencias sin scripts para evitar errores al inicio
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Ejecuta manualmente los comandos que necesitan bin/console (ahora que runtime está instalado)
RUN php bin/console cache:clear --env=prod --no-debug && \
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration && \
    php bin/console assets:install public

COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-n"]
