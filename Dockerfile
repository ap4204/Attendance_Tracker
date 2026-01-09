FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Enable Apache modules
RUN a2enmod rewrite headers

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project
COPY . .

# 🔑 CREATE REQUIRED LARAVEL DIRECTORIES (CRITICAL)
RUN mkdir -p bootstrap/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/framework/cache \
    && chmod -R 775 bootstrap storage

# Install dependencies
RUN composer install

# Run migrations automatically on deploy
RUN php artisan migrate --force || true


# Set ownership for Apache
RUN chown -R www-data:www-data /var/www/html

# Apache config
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
