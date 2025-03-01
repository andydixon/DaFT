# 1. Base Image with PHP and Nginx
FROM php:8.1-fpm-alpine AS base

# 2. Install required dependencies and extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    bash \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mbstring \
    && rm -rf /var/cache/apk/*

# 3. Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# 4. Copy Application Files and clean-up unneeded files for production
COPY . /var/www/html
RUN rm -rf /var/www/html/.git /var/www/html/docker /var/www/html/.github /var/www/html/src/Federaliser/tests

# 5. Set Working Directory
WORKDIR /var/www/html

# 6. Install Dependencies with Composer
RUN composer install --no-dev --optimize-autoloader

# 7. Change Ownership and Permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# 8. Nginx and Supervisor Configuration
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 9. Expose Port
EXPOSE 80

# 10. Start Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]