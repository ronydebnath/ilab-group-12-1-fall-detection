#!/bin/bash

# Create new Laravel project if it doesn't exist
if [ ! -f "composer.json" ]; then
    composer create-project laravel/laravel .
fi

# Install Filament
composer require filament/filament:"^3.2"

# Install Filament dependencies
php artisan filament:install --panels

# Create admin user
php artisan make:filament-user

# Set proper permissions
mkdir -p storage bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Generate application key if not exists
if [ ! -f ".env" ]; then
    cp .env.example .env
fi
php artisan key:generate

# Update .env file with database configuration
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=pgsql/' .env
sed -i 's/DB_HOST=127.0.0.1/DB_HOST=backend-db/' .env
sed -i 's/DB_PORT=3306/DB_PORT=5432/' .env
sed -i 's/DB_DATABASE=laravel/DB_DATABASE=fall_detection_backend/' .env
sed -i 's/DB_USERNAME=root/DB_USERNAME=fall_detection_backend_user/' .env
sed -i 's/DB_PASSWORD=/DB_PASSWORD=fall_detection_backend_password/' .env

# Run migrations
php artisan migrate