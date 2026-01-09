FROM php:8.3-apache

# ---------------- SYSTEM DEPENDENCIES ----------------
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    nodejs \
    npm

# ---------------- PHP EXTENSIONS ----------------
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# ---------------- APACHE ----------------
RUN a2enmod rewrite headers

# ---------------- COMPOSER ----------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ---------------- COPY PROJECT ----------------
COPY . .

# ---------------- LARAVEL DIRS ----------------
RUN mkdir -p bootstrap/cache \
    storage/framework/{sessions,views,cache} \
    && chmod -R 775 bootstrap storage

# ---------------- PHP DEPS ----------------
RUN composer install --no-dev --optimize-autoloader

# ---------------- FRONTEND BUILD (CRITICAL) ----------------
RUN npm install
RUN npm run build

# ---------------- MIGRATIONS ----------------
RUN php artisan migrate --force || true

# ---------------- PERMISSIONS ----------------
RUN chown -R www-data:www-data /var/www/html

# ---------------- APACHE CONFIG ----------------
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
