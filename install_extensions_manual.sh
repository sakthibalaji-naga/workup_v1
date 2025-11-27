# Alternative Solution - Manual PHP Extension Installation Script
# Run this inside the PHP container to install missing extensions

# Access the container:
# docker exec -it workup_php bash

# Then run these commands inside the container:

# Install IMAP extension
docker-php-ext-configure imap --with-kerberos --with-imap-ssl
docker-php-ext-install imap

# Install GD extension  
docker-php-ext-configure gd --with-freetype --with-jpeg
docker-php-ext-install gd

# Install Zip extension
docker-php-ext-install zip

# Restart PHP-FPM
killall -USR2 php-fpm

# Verify extensions are loaded
php -m | grep -E '(imap|gd|zip)'
