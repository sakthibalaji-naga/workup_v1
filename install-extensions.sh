#!/bin/bash

echo "Installing PHP Extensions for CodeIgniter..."
echo "=============================================="

# Update package lists
apt-get update

# Install dependencies for GD
echo "Installing GD dependencies..."
apt-get install -y libpng-dev libjpeg62-turbo-dev libfreetype6-dev

# Install dependencies for IMAP
echo "Installing IMAP dependencies..."
apt-get install -y libc-client-dev libkrb5-dev

# Install dependencies for Zip
echo "Installing Zip dependencies..."
apt-get install -y libzip-dev

# Configure and install GD
echo "Installing GD extension..."
docker-php-ext-configure gd --with-freetype --with-jpeg
docker-php-ext-install gd

# Configure and install IMAP
echo "Installing IMAP extension..."
docker-php-ext-configure imap --with-kerberos --with-imap-ssl
docker-php-ext-install imap

# Install Zip
echo "Installing Zip extension..."
docker-php-ext-install zip

# Install MySQL extensions if not already installed
echo "Installing MySQL extensions..."
docker-php-ext-install pdo_mysql mysqli

# Restart PHP-FPM
echo "Restarting PHP-FPM..."
killall -USR2 php-fpm || true

echo ""
echo "=============================================="
echo "Installation complete! Installed extensions:"
echo "=============================================="
php -m | grep -E '(imap|gd|zip|pdo_mysql|mysqli)'

echo ""
echo "Done! Your PHP container now has all required extensions."
