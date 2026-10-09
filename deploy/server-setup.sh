#!/bin/bash
# ==============================================================================
# PT Arsikon Cipta Karya — Production Server Setup Script
# ==============================================================================
# Jalankan script ini di VPS baru dengan Ubuntu 22.04/24.04 LTS
# Sebagai root: sudo bash server-setup.sh
# ==============================================================================

set -euo pipefail

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║  PT Arsikon Cipta Karya — Server Setup Script               ║"
echo "╚══════════════════════════════════════════════════════════════╝"

# --- 1. System Update ---
echo ""
echo "▸ [1/8] Memperbarui sistem..."
apt update && apt upgrade -y

# --- 2. Install Nginx ---
echo ""
echo "▸ [2/8] Menginstall Nginx..."
apt install -y nginx
systemctl enable nginx
systemctl start nginx

# --- 3. Install PHP 8.1 + Extensions ---
echo ""
echo "▸ [3/8] Menginstall PHP 8.1 + extensions..."
apt install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y \
    php8.1-fpm \
    php8.1-cli \
    php8.1-common \
    php8.1-mysql \
    php8.1-pgsql \
    php8.1-zip \
    php8.1-gd \
    php8.1-mbstring \
    php8.1-curl \
    php8.1-xml \
    php8.1-bcmath \
    php8.1-intl \
    php8.1-readline \
    php8.1-opcache \
    php8.1-redis \
    php8.1-tokenizer

systemctl enable php8.1-fpm
systemctl start php8.1-fpm

# --- 4. Install MySQL 8.0 ---
echo ""
echo "▸ [4/8] Menginstall MySQL 8.0..."
apt install -y mysql-server
systemctl enable mysql
systemctl start mysql

echo ""
echo "  ⚠ Jalankan 'mysql_secure_installation' setelah script selesai!"
echo "  ⚠ Buat database dan user:"
echo "    CREATE DATABASE pt_arsikon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "    CREATE USER 'arsikon_app'@'localhost' IDENTIFIED BY 'PASSWORD_KUAT';"
echo "    GRANT ALL PRIVILEGES ON pt_arsikon.* TO 'arsikon_app'@'localhost';"
echo "    FLUSH PRIVILEGES;"

# --- 5. Install Redis ---
echo ""
echo "▸ [5/8] Menginstall Redis..."
apt install -y redis-server
systemctl enable redis-server
systemctl start redis-server

# --- 6. Install Composer ---
echo ""
echo "▸ [6/8] Menginstall Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# --- 7. Install Node.js 18 LTS ---
echo ""
echo "▸ [7/8] Menginstall Node.js 18 LTS..."
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

# --- 8. Install Supervisor (untuk Queue Worker) ---
echo ""
echo "▸ [8/8] Menginstall Supervisor..."
apt install -y supervisor
systemctl enable supervisor
systemctl start supervisor

# --- 9. Install Certbot (SSL Let's Encrypt) ---
echo ""
echo "▸ [Bonus] Menginstall Certbot untuk SSL..."
apt install -y certbot python3-certbot-nginx

echo ""
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║  ✅ Instalasi software selesai!                              ║"
echo "╠══════════════════════════════════════════════════════════════╣"
echo "║  Langkah selanjutnya:                                       ║"
echo "║  1. mysql_secure_installation                               ║"
echo "║  2. Buat database & user MySQL                              ║"
echo "║  3. Setup Nginx vhost (lihat arsikon-nginx.conf)            ║"
echo "║  4. Setup Supervisor (lihat arsikon-worker.conf)            ║"
echo "║  5. Deploy aplikasi Laravel                                 ║"
echo "║  6. certbot --nginx -d arsikon.co.id                       ║"
echo "╚══════════════════════════════════════════════════════════════╝"
