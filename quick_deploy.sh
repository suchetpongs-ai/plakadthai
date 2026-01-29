#!/bin/bash
#===============================================================================
# Quick Deploy Script - Run this on VPS
# Just copy-paste this entire script into your VPS terminal
#===============================================================================

cd /root

# Download the deployment script
cat > deploy_discuz.sh << 'SCRIPT_END'
#!/bin/bash
set -e
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

DOMAIN="plakadthai.com"
WEB_ROOT="/var/www/${DOMAIN}"
DB_NAME="discuz_plakadthai"
DB_USER="discuz_user"
DB_PASS=$(openssl rand -base64 16)

echo -e "${GREEN}Discuz! X3.5 Thai - Adding to existing server${NC}"

# Pre-flight
if [ -d "${WEB_ROOT}" ]; then
    echo -e "${RED}${WEB_ROOT} exists! Remove? (y/n)${NC}"
    read choice
    [ "$choice" = "y" ] && rm -rf ${WEB_ROOT} || exit 1
fi

PHP_VERSION=$(php -v 2>/dev/null | head -1 | grep -oP 'PHP \K[0-9]+\.[0-9]+' || echo "8.2")
echo -e "${GREEN}Using PHP ${PHP_VERSION}${NC}"

# Install dependencies
echo -e "${YELLOW}[1/6] Dependencies...${NC}"
apt update
apt install -y php${PHP_VERSION}-gd php${PHP_VERSION}-mbstring php${PHP_VERSION}-xml php${PHP_VERSION}-zip php${PHP_VERSION}-intl php${PHP_VERSION}-bcmath unzip git 2>/dev/null || true

# Database
echo -e "${YELLOW}[2/6] Database...${NC}"
mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Download Discuz
echo -e "${YELLOW}[3/6] Downloading Discuz...${NC}"
mkdir -p ${WEB_ROOT}
cd /tmp && rm -rf discuz-x35-th 2>/dev/null
git clone https://github.com/jaideejung007/discuz-x35-th.git
cp -r discuz-x35-th/upload/* ${WEB_ROOT}/
rm -rf discuz-x35-th

# Permissions
echo -e "${YELLOW}[4/6] Permissions...${NC}"
chown -R www-data:www-data ${WEB_ROOT}
chmod -R 755 ${WEB_ROOT}
chmod -R 777 ${WEB_ROOT}/config ${WEB_ROOT}/data ${WEB_ROOT}/uc_client/data ${WEB_ROOT}/uc_server/data

# Nginx
echo -e "${YELLOW}[5/6] Nginx...${NC}"
PHP_SOCKET=$(find /var/run/php/ -name "php*-fpm.sock" 2>/dev/null | head -1)
cat > /etc/nginx/sites-available/${DOMAIN} << NGINX
server {
    listen 80;
    server_name ${DOMAIN} www.${DOMAIN};
    root ${WEB_ROOT};
    index index.php index.html;
    client_max_body_size 50M;
    
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location /data { deny all; }
    location /config { deny all; }
    location /uc_server/data { deny all; }
    location ~ \.php\$ {
        fastcgi_pass unix:${PHP_SOCKET};
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    }
    location ~ /\. { deny all; }
}
NGINX

ln -sf /etc/nginx/sites-available/${DOMAIN} /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx

# Done
echo -e "\n${GREEN}========================================${NC}"
echo -e "${GREEN}DONE! Database credentials:${NC}"
echo -e "  Database: ${DB_NAME}"
echo -e "  User: ${DB_USER}"
echo -e "  Password: ${DB_PASS}"
echo -e "${GREEN}========================================${NC}"
echo -e "Next: http://${DOMAIN}/install/"
echo "${DB_NAME} | ${DB_USER} | ${DB_PASS}" > /root/discuz_creds.txt
SCRIPT_END

chmod +x deploy_discuz.sh
echo "Script ready! Run: ./deploy_discuz.sh"
