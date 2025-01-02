FROM php:8.2-fpm

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    nodejs \
    npm \
    && docker-php-ext-install zip pdo pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configurar el directorio de trabajo
WORKDIR /app

# Exponer el puerto
EXPOSE 80
