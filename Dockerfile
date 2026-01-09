# =====================================================
# 1️⃣ FRONTEND BUILD STAGE (Node + Vite)
# =====================================================
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build


# =====================================================
# 2️⃣ BACKEND STAGE (PHP + Apache)
# =====================================================
FROM php:8.3-apache

# ---------------- SYSTEM DEPENDENCIES ----------------
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd

# ---------------- APACHE ----------------
RUN a2enmod rewrite headers

# ---------------- COMPOSER ----------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ---------------- COPY BACKEND CODE ----------------
COPY . .

# ---------------- COPY BUILT FRONTEND ----------------
COPY --from=frontend /app/public/build /var/www/html/public/build

# ---------------- LARAVEL DIRECTORIES ----------------
RUN mkdir -p bootstrap/cache \
    storage/framework/{sessions,views,cache} \
    && chmod -R 775 bootstrap storage

# ---------------- COMPOSER INSTALL ----------------
RUN composer install --no-dev --optimize-autoloader

# ---------------- PERMISSIONS ----------------
RUN chown -R www-data:www-data /var/www/html

# ---------------- APACHE CONFIG ----------------
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
