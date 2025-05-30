 # 1. Base: PHP 8.1 con FPM
FROM php:8.1-fpm

# 2. Instala herramientas y extensiones necesarias
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache \
    && rm -rf /var/lib/apt/lists/*

# 3. Instala Composer (gestor de dependencias de PHP)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4. Directorio de trabajo
WORKDIR /app

# 5. Copia los ficheros de definición de dependencias primero
COPY composer.json composer.lock ./

# 7. Copia el resto de tu aplicación
COPY . .

# Instalar Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony


# 6. Instala las dependencias sin los paquetes de desarrollo
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 8. Genera los assets (si usas Webpack Encore)
# RUN yarn install && yarn encore production

# 9. Ejecuta las migraciones (opcionalmente en tiempo de build)
RUN php bin/console doctrine:migrations:migrate --no-interaction --no-script

# 10. Exponer el puerto que utilizará PHP-FPM
EXPOSE 9000

# 11. Comando por defecto al iniciar el contenedor
CMD ["php-fpm"]
