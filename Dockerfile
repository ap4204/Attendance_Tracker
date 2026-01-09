FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (PostgreSQL, NOT MySQL)
RUN docker-php-ext-install \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

# Enable Apache modules
RUN a2enmod rewrite headers

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Create required Laravel directories
RUN mkdir -p bootstrap/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    && chmod -R 775 bootstrap storage

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Run migrations (safe for Neon)
RUN php artisan migrate --force || true

# Set ownership for Apache
RUN chown -R www-data:www-data /var/www/html

# Apache config
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
