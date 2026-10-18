#!/bin/bash
# ==============================================================================
# UangPas Lapas - Automated Kanwil Cloud Hosting Deployment Script
# Target: Ubuntu 22.04 / 24.04 LTS VPS or Cloud Dedicated Server
# ==============================================================================

set -e

echo "=========================================================="
echo "  MEMULAI INSTALASI SERVER PUSAT KANWIL UANGPAS LAPAS     "
echo "=========================================================="

# 1. Update OS Packages
echo "[1/6] Memperbarui paket sistem operasi..."
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git unzip zip software-properties-common ufw nginx supervisor

# 2. Install PHP 8.3 & Extensions
echo "[2/6] Memasang PHP 8.3 dan ekstensi pendukung..."
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-intl php8.3-gd

# 3. Install MySQL 8
echo "[3/6] Memasang Database MySQL Server..."
sudo apt install -y mysql-server
sudo systemctl enable mysql
sudo systemctl start mysql

# 4. Install Composer
echo "[4/6] Memasang Composer..."
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 5. Clone & Setup Application Directory
echo "[5/6] Menyiapkan direktori aplikasi web..."
WEB_DIR="/var/www/uangpas-kanwil"

if [ ! -d "$WEB_DIR" ]; then
    sudo mkdir -p $WEB_DIR
fi

sudo chown -R $USER:www-data $WEB_DIR
sudo chmod -R 775 $WEB_DIR

echo "Menyalin file aplikasi Laravel ke $WEB_DIR..."
# cp -r . $WEB_DIR/

cd $WEB_DIR

# 6. Database Creation & Migration
echo "[6/6] Konfigurasi Database dan Migrasi..."
DB_NAME="uangpas_kanwil"
DB_USER="uangpas_user"
DB_PASS="UangPasSecret2026!#"

sudo mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
sudo mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

echo "=========================================================="
echo "  INSTALASI SERVER PUSAT KANWIL BERHASIL SELESAI!         "
echo "=========================================================="
echo "Langkah selanjutnya:"
echo "1. Salin file UangPasWeb ke $WEB_DIR"
echo "2. Jalankan: cd $WEB_DIR && composer install --optimize-autoloader --no-dev"
echo "3. Salin .env dan atur DB_DATABASE=${DB_NAME}, DB_USERNAME=${DB_USER}, DB_PASSWORD=${DB_PASS}"
echo "4. Jalankan: php artisan key:generate && php artisan migrate --seed"
echo "5. Jalankan: sudo certbot --nginx -d kanwil.lapas.go.id (untuk SSL HTTPS)"
echo "=========================================================="
