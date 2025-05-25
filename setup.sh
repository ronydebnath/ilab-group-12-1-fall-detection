#!/bin/bash

# Function to validate domain name
validate_domain() {
    local domain=$1
    if [[ ! $domain =~ ^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$ ]]; then
        return 1
    fi
    return 0
}

# Function to validate IP address
validate_ip() {
    local ip=$1
    if [[ ! $ip =~ ^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$ ]]; then
        return 1
    fi
    return 0
}

# Function to update nginx configuration
update_nginx_config() {
    local domain=$1
    local config_file="backend/docker/nginx/conf.d/app.conf"
    local example_file="backend/docker/nginx/conf.d/app.conf.example"

    if [ -f "$example_file" ]; then
        cp "$example_file" "$config_file"
        # Replace server_name in nginx config
        sed -i.bak "s/server_name .*/server_name $domain;/" "$config_file"
        rm -f "${config_file}.bak"
        echo "Nginx configuration updated for domain: $domain"
    else
        echo "Error: nginx example config file not found!"
        exit 1
    fi
}

# Function to update hosts file
update_hosts_file() {
    local domain=$1
    local ip=$2

    if ! grep -q "$domain" /etc/hosts; then
        echo "$ip $domain" | sudo tee -a /etc/hosts > /dev/null
        echo "Added domain entry to /etc/hosts"
    else
        echo "Domain entry already exists in /etc/hosts"
    fi
}

# Ask user about deployment environment
while true; do
    read -p "Are you deploying the application in local or production environment? (local/production): " deployment_env
    deployment_env=$(echo "$deployment_env" | tr '[:upper:]' '[:lower:]')
    if [ "$deployment_env" = "local" ] || [ "$deployment_env" = "production" ]; then
        break
    else
        echo "Invalid input. Please enter 'local' or 'production'"
    fi
done

# Set domain and IP based on environment
if [ "$deployment_env" = "production" ]; then
    # Get domain and IP details for production
    while true; do
        read -p "Please enter your domain name (e.g., example.com): " domain_name
        if validate_domain "$domain_name"; then
            break
        else
            echo "Invalid domain name format. Please enter a valid domain (e.g., example.com)"
        fi
    done

    while true; do
        read -p "Please enter the server IP address: " ip_address
        if validate_ip "$ip_address"; then
            break
        else
            echo "Invalid IP address format. Please enter a valid IP (e.g., 192.168.1.1)"
        fi
    done

    # Set production environment variables
    export APP_ENV=production
    export APP_DEBUG=false
    export APP_URL="https://$domain_name"
    
    # Update nginx and hosts configuration
    update_nginx_config "$domain_name"
    update_hosts_file "$domain_name" "$ip_address"
else
    # Local development settings
    domain_name="fall-detection-backend.test"
    ip_address="127.0.0.1"
    
    # Set local environment variables
    export APP_ENV=local
    export APP_DEBUG=true
    export APP_URL="http://$domain_name"
    
    # Update nginx and hosts configuration
    update_nginx_config "$domain_name"
    update_hosts_file "$domain_name" "$ip_address"
fi

# Function to detect OS
detect_os() {
    case "$(uname -s)" in
        Darwin*)    echo "macos";;
        Linux*)     echo "linux";;
        *)          echo "unknown";;
    esac
}

# Function to install PHP and Composer on macOS
install_macos_deps() {
    echo "Installing PHP and Composer on macOS..."
    # Check if Homebrew is installed
    if ! command -v brew &> /dev/null; then
        echo "Installing Homebrew..."
        /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
    fi
    
    # Install PHP 8.2
    brew install php@8.2
    
    # Install Composer
    if ! command -v composer &> /dev/null; then
        echo "Installing Composer..."
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        php composer-setup.php --install-dir=/usr/local/bin --filename=composer
        php -r "unlink('composer-setup.php');"
    fi
}

# Function to install PHP and Composer on Linux
install_linux_deps() {
    echo "Installing PHP and Composer on Linux..."
    # Add PHP repository
    sudo add-apt-repository ppa:ondrej/php -y
    sudo apt-get update
    
    # Install PHP 8.2 and required extensions
    sudo apt-get install -y php8.2 php8.2-fpm php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath
    
    # Install Composer
    if ! command -v composer &> /dev/null; then
        echo "Installing Composer..."
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
        php -r "unlink('composer-setup.php');"
    fi
}

# Main setup script
OS=$(detect_os)

# Function to check if Docker is installed
check_docker() {
    if ! command -v docker &> /dev/null || ! command -v docker-compose &> /dev/null; then
        return 1
    fi
    return 0
}

# Function to detect Mac processor type
detect_mac_processor() {
    if [[ $(uname -m) == 'arm64' ]]; then
        echo "apple_silicon"
    else
        echo "intel"
    fi
}

# Check and install Docker if needed
if ! check_docker; then
    echo "Docker is not installed. Installing Docker..."
    case $OS in
        "macos")
            install_docker_macos
            ;;
        "linux")
            install_docker_linux
            ;;
        *)
            echo "Unsupported operating system for Docker installation"
            exit 1
            ;;
    esac
fi

# Function to install Docker Desktop on macOS
install_docker_macos() {
    local processor=$(detect_mac_processor)
    echo "Installing Docker Desktop for Mac ($processor)..."
    
    if [ "$processor" == "apple_silicon" ]; then
        # Download Docker Desktop for Apple Silicon
        curl -L "https://desktop.docker.com/mac/main/arm64/Docker.dmg" -o ~/Downloads/Docker.dmg
    else
        # Download Docker Desktop for Intel
        curl -L "https://desktop.docker.com/mac/main/amd64/Docker.dmg" -o ~/Downloads/Docker.dmg
    fi
    
    # Mount the DMG
    hdiutil attach ~/Downloads/Docker.dmg
    
    # Copy Docker.app to Applications
    cp -R "/Volumes/Docker/Docker.app" /Applications/
    
    # Unmount the DMG
    hdiutil detach "/Volumes/Docker"
    
    # Clean up
    rm ~/Downloads/Docker.dmg
    
    echo "Docker Desktop has been installed. Please start Docker Desktop from your Applications folder."
    echo "After starting Docker Desktop, run this script again."
    exit 0
}

# Function to install Docker on Linux
install_docker_linux() {
    echo "Installing Docker on Linux..."
    
    # Remove old versions if they exist
    sudo apt-get remove docker docker-engine docker.io containerd runc
    
    # Update package index
    sudo apt-get update
    
    # Install prerequisites
    sudo apt-get install -y \
        apt-transport-https \
        ca-certificates \
        curl \
        gnupg \
        lsb-release
    
    # Add Docker's official GPG key
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
    
    # Set up the stable repository
    echo \
      "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu \
      $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
    
    # Install Docker Engine
    sudo apt-get update
    sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
    
    # Add current user to docker group
    sudo usermod -aG docker $USER
    
    echo "Docker has been installed. Please log out and log back in for the group changes to take effect."
    echo "After logging back in, run this script again."
    exit 0
}

# Function to setup mobile app
setup_mobile_app() {
    echo "Setting up mobile app..."
    
    # Install NVM if not installed
    if [ ! -d "$HOME/.nvm" ]; then
        echo "Installing NVM..."
        curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
        
        # Load NVM
        export NVM_DIR="$HOME/.nvm"
        [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
        [ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
    else
        # Load NVM if already installed
        export NVM_DIR="$HOME/.nvm"
        [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
        [ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
    fi

    # Install and use Node.js 18
    echo "Installing Node.js 18..."
    nvm install 18
    nvm use 18
    nvm alias default 18

    # Install Expo CLI globally if not installed
    if ! command -v expo &> /dev/null; then
        echo "Installing Expo CLI..."
        npm install -g expo-cli
    fi

    # Create mobile app directory if it doesn't exist
    if [ ! -d "mobile-app" ]; then
        echo "Creating mobile app directory..."
        mkdir -p mobile-app
    fi

    # Add NVM to shell profile if not already added
    if ! grep -q "NVM_DIR" ~/.zshrc 2>/dev/null; then
        echo "Adding NVM configuration to shell profile..."
        echo 'export NVM_DIR="$HOME/.nvm"' >> ~/.zshrc
        echo '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"' >> ~/.zshrc
        echo '[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"' >> ~/.zshrc
    fi
}

# Setup mobile app
setup_mobile_app

# Start the containers if not running
if ! docker-compose ps | grep -q "backend-app.*running"; then
    echo "Starting containers..."
    docker-compose up -d
    # Wait for container to be ready
    sleep 10
fi

# Ensure vendor directory exists and has proper permissions
docker-compose exec backend-app bash -c "mkdir -p /var/www/vendor && chmod -R 755 /var/www/vendor && chmod -R 777 storage bootstrap/cache"

# Run composer install inside container
docker-compose exec backend-app composer install --no-scripts --no-autoloader --no-interaction --prefer-dist

# Generate autoload files
docker-compose exec backend-app composer dump-autoload --optimize

# Add storage link if not exists
docker-compose exec backend-app php artisan storage:link

# Create .env file from .env.example if it doesn't exist
docker-compose exec backend-app bash -c "if [ ! -f .env ]; then cp .env.example .env; fi"

# Update .env file with environment-specific settings
docker-compose exec backend-app bash -c "sed -i 's|APP_ENV=.*|APP_ENV=$APP_ENV|' .env && \
    sed -i 's|APP_DEBUG=.*|APP_DEBUG=$APP_DEBUG|' .env && \
    sed -i 's|APP_URL=.*|APP_URL=$APP_URL|' .env"

# Generate application key if not exists
docker-compose exec backend-app php artisan key:generate

# Run migrations
docker-compose exec backend-app php artisan migrate:fresh

# Create admin user
docker-compose exec backend-app php artisan app:create-admin-user

# Seed database
docker-compose exec backend-app php artisan db:seed

echo ""
echo "Setup completed successfully!"
echo "The backend application is now available at: $APP_URL"
echo "The mobile app is now available at: http://localhost:19002"
echo ""
echo "Admin credentials:"
echo "Email: admin@fall-detection.com"
echo "Password: password"
echo "" 