#!/bin/bash
#===============================================================================
# Discuz! X3.5 Thai Deployment Script
# For EXISTING VPS with other websites
# Domain: plakadthai.com
# 
# ⚠️ This script is designed to ADD a new site without affecting existing sites
#===============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DOMAIN="plakadthai.com"
WEB_ROOT="/var/www/${DOMAIN}"
DB_NAME="discuz_plakadthai"
DB_USER="discuz_user"
DB_PASS=$(openssl rand -base64 16)

echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}   Discuz! X3.5 Thai Installation Script${NC}"
echo -e "${GREEN}   Domain: ${DOMAIN}${NC}"
echo -e "${GREEN}   Mode: ADD to existing server${NC}"
echo -e "${GREEN}================================================${NC}"

#===============================================================================
# Pre-flight Checks
#===============================================================================
echo -e "\n${BLUE}[Pre-flight] Checking existing setup...${NC}"

# Check if domain folder already exists
if [ -d "${WEB_ROOT}" ]; then
    echo -e "${RED}ERROR: ${WEB_ROOT} already exists!${NC}"
    read -p "Do you want to remove it and start fresh? (y/n): " remove_existing
    if [ "$remove_existing" = "y" ]; then
        rm -rf ${WEB_ROOT}
        echo -e "${YELLOW}Removed existing folder.${NC}"
    else
        echo -e "${RED}Aborting installation.${NC}"
        exit 1
    fi
fi

# Detect PHP version
PHP_VERSION=$(php -v 2>/dev/null | head -1 | grep -oP 'PHP \K[0-9]+\.[0-9]+' || echo "")
if [ -z "$PHP_VERSION" ]; then
    echo -e "${YELLOW}PHP not detected. Will install PHP 8.3${NC}"
    PHP_VERSION="8.3"
    INSTALL_PHP=true
else
    echo -e "${GREEN}Detected PHP ${PHP_VERSION}${NC}"
    INSTALL_PHP=false
fi

# Check Nginx
if systemctl is-active --quiet nginx; then
    echo -e "${GREEN}Nginx is running ✓${NC}"
else
    echo -e "${RED}Nginx is not running. Starting...${NC}"
    systemctl start nginx
fi

# Check MariaDB/MySQL
if systemctl is-active --quiet mariadb 2>/dev/null || systemctl is-active --quiet mysql 2>/dev/null; then
    echo -e "${GREEN}Database server is running ✓${NC}"
    INSTALL_DB=false
else
    echo -e "${YELLOW}Database not running. Will install MariaDB${NC}"
    INSTALL_DB=true
fi

echo -e "${GREEN}Pre-flight checks passed!${NC}"

#===============================================================================
# 1. Install Missing Dependencies Only
#===============================================================================
echo -e "\n${YELLOW}[1/6] Installing missing dependencies...${NC}"

apt update

# Install PHP if needed
if [ "$INSTALL_PHP" = true ]; then
    apt install -y \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-zip \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-bcmath
else
    # Just ensure required extensions are installed
    apt install -y \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-zip \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-bcmath 2>/dev/null || true
fi

# Install MariaDB if needed
if [ "$INSTALL_DB" = true ]; then
    apt install -y mariadb-server
    systemctl start mariadb
    systemctl enable mariadb
fi

# Install other tools
apt install -y unzip wget git certbot python3-certbot-nginx 2>/dev/null || true

echo -e "${GREEN}Dependencies ready!${NC}"

#===============================================================================
# 2. Create Database
#===============================================================================
echo -e "\n${YELLOW}[2/6] Creating database...${NC}"

# Create database and user
mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo -e "${GREEN}Database '${DB_NAME}' created!${NC}"

#===============================================================================
# 3. Download Discuz! X3.5 Thai
#===============================================================================
echo -e "\n${YELLOW}[3/6] Downloading Discuz! X3.5 Thai...${NC}"

mkdir -p ${WEB_ROOT}
cd /tmp

# Clean up if exists
rm -rf discuz-x35-th 2>/dev/null || true

# Clone from GitHub
git clone https://github.com/jaideejung007/discuz-x35-th.git

# Copy upload folder contents
cp -r discuz-x35-th/upload/* ${WEB_ROOT}/

# Cleanup
rm -rf discuz-x35-th

echo -e "${GREEN}Discuz downloaded to ${WEB_ROOT}${NC}"

#===============================================================================
# 4. Set Permissions
#===============================================================================
echo -e "\n${YELLOW}[4/6] Setting file permissions...${NC}"

chown -R www-data:www-data ${WEB_ROOT}
chmod -R 755 ${WEB_ROOT}

# Writable directories required by Discuz
chmod -R 777 ${WEB_ROOT}/config
chmod -R 777 ${WEB_ROOT}/data
chmod -R 777 ${WEB_ROOT}/uc_client/data
chmod -R 777 ${WEB_ROOT}/uc_server/data

echo -e "${GREEN}Permissions set!${NC}"

#===============================================================================
# 5. Create Nginx Site Config
#===============================================================================
echo -e "\n${YELLOW}[5/6] Creating Nginx configuration...${NC}"

# Detect PHP-FPM socket
PHP_SOCKET=$(find /var/run/php/ -name "php*-fpm.sock" 2>/dev/null | head -1)
if [ -z "$PHP_SOCKET" ]; then
    PHP_SOCKET="/var/run/php/php${PHP_VERSION}-fpm.sock"
fi
echo -e "${BLUE}Using PHP socket: ${PHP_SOCKET}${NC}"

cat > /etc/nginx/sites-available/${DOMAIN} << NGINX_CONF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN} www.${DOMAIN};
    root ${WEB_ROOT};
    index index.php index.html;

    # Logging
    access_log /var/log/nginx/${DOMAIN}.access.log;
    error_log /var/log/nginx/${DOMAIN}.error.log;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript;

    # Max upload size
    client_max_body_size 50M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # Discuz security - deny access to sensitive folders
    location /data {
        deny all;
    }

    location /config {
        deny all;
    }

    location /uc_server/data {
        deny all;
    }

    # PHP handling
    location ~ \.php\$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)\$;
        fastcgi_pass unix:${PHP_SOCKET};
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;
        fastcgi_read_timeout 300;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)\$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
NGINX_CONF

# Enable site (don't remove other sites!)
ln -sf /etc/nginx/sites-available/${DOMAIN} /etc/nginx/sites-enabled/

# Test nginx config
nginx -t

# Reload Nginx (not restart - safer for existing sites)
systemctl reload nginx

echo -e "${GREEN}Nginx configured for ${DOMAIN}!${NC}"

#===============================================================================
# 6. Optional SSL Setup
#===============================================================================
echo -e "\n${YELLOW}[6/6] SSL Setup...${NC}"
echo -e "${BLUE}Make sure DNS for ${DOMAIN} points to this server!${NC}"

read -p "Setup SSL with Let's Encrypt now? (y/n): " setup_ssl

if [ "$setup_ssl" = "y" ] || [ "$setup_ssl" = "Y" ]; then
    certbot --nginx -d ${DOMAIN} -d www.${DOMAIN} --non-interactive --agree-tos --email admin@${DOMAIN} || {
        echo -e "${YELLOW}SSL setup failed. You can try again later with:${NC}"
        echo -e "certbot --nginx -d ${DOMAIN} -d www.${DOMAIN}"
    }
else
    echo -e "${YELLOW}Skipping SSL. Run later with:${NC}"
    echo -e "certbot --nginx -d ${DOMAIN} -d www.${DOMAIN}"
fi

#===============================================================================
# Installation Complete
#===============================================================================
echo -e "\n${GREEN}================================================${NC}"
echo -e "${GREEN}   Installation Complete!${NC}"
echo -e "${GREEN}================================================${NC}"
echo -e ""
echo -e "${YELLOW}Database Information (SAVE THIS!):${NC}"
echo -e "  Database Name: ${DB_NAME}"
echo -e "  Database User: ${DB_USER}"
echo -e "  Database Pass: ${DB_PASS}"
echo -e "  Database Host: localhost"
echo -e ""
echo -e "${YELLOW}Existing Sites Status:${NC}"
ls -la /etc/nginx/sites-enabled/
echo -e ""
echo -e "${YELLOW}Next Steps:${NC}"
echo -e "  1. Point DNS: ${DOMAIN} → $(curl -s ifconfig.me)"
echo -e "  2. Open browser: http://${DOMAIN}/install/"
echo -e "  3. Enter database info above"
echo -e "  4. Create admin account"
echo -e "  5. DELETE install folder: ${RED}rm -rf ${WEB_ROOT}/install${NC}"
echo -e ""

# Save credentials
cat > /root/discuz_${DOMAIN}_credentials.txt << EOF
===========================================
Discuz! X3.5 Thai - ${DOMAIN}
===========================================

Database Name: ${DB_NAME}
Database User: ${DB_USER}
Database Pass: ${DB_PASS}
Database Host: localhost

Web Root: ${WEB_ROOT}
Admin Panel: http://${DOMAIN}/admin.php

Server IP: $(curl -s ifconfig.me 2>/dev/null || echo "N/A")
Installation Date: $(date)
===========================================
EOF

chmod 600 /root/discuz_${DOMAIN}_credentials.txt
echo -e "${GREEN}Credentials saved to: /root/discuz_${DOMAIN}_credentials.txt${NC}"
