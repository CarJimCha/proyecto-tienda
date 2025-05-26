FROM php:8.1-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    zip \
    nginx \
    supervisor \
    && docker-php-ext-install intl mbstring zip pdo pdo_mysql

# Instalar Composer desde imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Establecer variable PATH para composer (por si acaso)
ENV PATH="/usr/local/bin:$PATH"

# Configuración de Nginx y Supervisor
COPY .docker/nginx.conf /etc/nginx/sites-available/default
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Directorio de trabajo
WORKDIR /app

# Copiar el código fuente
COPY . .

# Instalar dependencias PHP (sin scripts para evitar errores)
RUN composer diagnose

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts \
    --prefer-dist \
    --no-interaction \
    --ignore-platform-reqs

# Limpiar caché Symfony
RUN php bin/console cache:clear --env=prod --no-warmup --verbose

# Exponer el puerto 80 (HTTP)
EXPOSE 80

# Iniciar Supervisor que levanta Nginx + PHP-FPM
CMD ["/usr/bin/supervisord"]
