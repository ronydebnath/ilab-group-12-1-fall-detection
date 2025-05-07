#!/bin/bash

# # Function to detect OS
# detect_os() {
#     case "$(uname -s)" in
#         Darwin*)    echo "macos";;
#         Linux*)     echo "linux";;
#         *)          echo "unknown";;
#     esac
# }

# # Function to install PHP and Composer on macOS
# install_macos_deps() {
#     echo "Installing PHP and Composer on macOS..."
#     # Check if Homebrew is installed
#     if ! command -v brew &> /dev/null; then
#         echo "Installing Homebrew..."
#         /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
#     fi
    
#     # Install PHP 8.2
#     brew install php@8.2
    
#     # Install Composer
#     if ! command -v composer &> /dev/null; then
#         echo "Installing Composer..."
#         php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
#         php composer-setup.php --install-dir=/usr/local/bin --filename=composer
#         php -r "unlink('composer-setup.php');"
#     fi
# }

# # Function to install PHP and Composer on Linux
# install_linux_deps() {
#     echo "Installing PHP and Composer on Linux..."
#     # Add PHP repository
#     sudo add-apt-repository ppa:ondrej/php -y
#     sudo apt-get update
    
#     # Install PHP 8.2 and required extensions
#     sudo apt-get install -y php8.2 php8.2-fpm php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath
    
#     # Install Composer
#     if ! command -v composer &> /dev/null; then
#         echo "Installing Composer..."
#         php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
#         sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
#         php -r "unlink('composer-setup.php');"
#     fi
# }

# # Main setup script
# OS=$(detect_os)

# # Install dependencies based on OS
# case $OS in
#     "macos")
#         install_macos_deps
#         ;;
#     "linux")
#         install_linux_deps
#         ;;
#     *)
#         echo "Unsupported operating system"
#         exit 1
#         ;;
# esac

# Set proper permissions
mkdir -p storage bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Start the containers if not running
if ! docker-compose ps | grep -q "backend-app.*running"; then
    echo "Starting containers..."
    docker-compose up -d
    # Wait for container to be ready
    sleep 10
fi

# Ensure vendor directory exists and has proper permissions
docker-compose exec backend-app bash -c "mkdir -p /var/www/vendor && chmod -R 755 /var/www/vendor"

# Run composer install inside container
docker-compose exec backend-app composer install --no-scripts --no-autoloader --no-interaction --prefer-dist

# Generate autoload files
docker-compose exec backend-app composer dump-autoload --optimize

# Create .env file from .env.example if it doesn't exist
docker-compose exec backend-app bash -c "if [ ! -f .env ]; then cp .env.example .env; fi"

# Generate application key if not exists
docker-compose exec backend-app php artisan key:generate

# Run migrations
docker-compose exec backend-app php artisan migrate:fresh

# Create admin user
docker-compose exec backend-app php artisan app:create-admin-user

# Seed database
docker-compose exec backend-app php artisan db:seed