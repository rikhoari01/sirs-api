# Base image with PHP and Nginx
FROM php:8.2-fpm-alpine

# Install dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    libpng-dev \
    libxml2-dev \
    oniguruma-dev \
    curl \
    zip \
    unzip \
    git

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set workdir
WORKDIR /var/www

# Copy project
COPY . .

# Install Laravel dependencies
RUN composer install

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Configure Nginx
RUN mkdir -p /run/nginx
COPY .docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Supervisor configuration to run Nginx + PHP-FPM
COPY .docker/supervisor/supervisord.conf /etc/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
