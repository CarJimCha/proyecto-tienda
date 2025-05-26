# Etapa base PHP-FPM + nginx + supervisor
FROM php:8.1-fpm

# Instala dependencias para PHP, sistema, nginx y supervisor
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    nginx \
    supervisor \
    curl \
    zip \
    && docker-php-ext-install pdo pdo_pgsql zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www/html

# Copia todo el código fuente
COPY . .

# Instala dependencias PHP SIN ejecutar scripts automáticos (para evitar errores con bin/console)
RUN composer install --no-dev --optimize-autoloader

# Ejecuta manualmente los scripts que necesitan bin/console
RUN php bin/console assets:install public \
    && php bin/console cache:clear --env=prod --no-debug \
    && php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Copia configuración nginx y supervisor
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Ajusta permisos (opcional, según cómo sea tu app)
RUN chown -R www-data:www-data var/cache var/log public

# Expone el puerto HTTP
EXPOSE 80

# Ejecuta supervisord para manejar php-fpm y nginx juntos
CMD ["/usr/bin/supervisord", "-n"]
