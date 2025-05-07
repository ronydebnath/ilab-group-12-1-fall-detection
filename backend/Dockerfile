FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd intl zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first
COPY composer.json composer.lock* ./

# Install dependencies
RUN composer install --no-scripts --no-autoloader --no-interaction --prefer-dist

# Copy the rest of the application
COPY . .

# Generate autoload files and run post-install scripts
RUN composer dump-autoload --optimize && \
    composer run-script post-autoload-dump && \
    composer run-script post-root-package-install && \
    composer run-script post-create-project-cmd

# Set permissions
RUN chown -R www-data:www-data /var/www && \
    chmod -R 755 /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 755 /var/www/vendor

# Expose port 9000
EXPOSE 9000

CMD ["php-fpm"] 