# Etapa base PHP-FPM
FROM php:8.1-fpm

# Instala dependencias para PHP y sistema
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

# Configura directorio de trabajo
WORKDIR /var/www/html

# Copia el código fuente al contenedor
COPY . .

# Instala dependencias PHP sin dev para producción y optimiza autoload
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Limpia cache Symfony y aplica migraciones
RUN php bin/console cache:clear --env=prod --no-debug && \
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Copia configuración Nginx y supervisor
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Exponer el puerto 80 para Render
EXPOSE 80

# Ejecuta supervisord para levantar PHP-FPM y Nginx
CMD ["/usr/bin/supervisord", "-n"]
