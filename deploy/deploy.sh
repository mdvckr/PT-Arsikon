#!/bin/bash
# ==============================================================================
# PT Arsikon — Production Deployment Script
# ==============================================================================
# Jalankan setiap kali ada update code dari Git
# Usage: bash deploy.sh
# ==============================================================================

set -euo pipefail

APP_DIR="/var/www/arsikon"
BRANCH="main"

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║  PT Arsikon — Deploying to Production                       ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

cd "$APP_DIR"

# 1. Maintenance mode
echo "▸ Activating maintenance mode..."
php artisan down --retry=60

# 2. Pull latest code
echo "▸ Pulling latest code from $BRANCH..."
git pull origin "$BRANCH"

# 3. Install/update PHP dependencies (no dev)
echo "▸ Installing Composer dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 4. Install/update NPM dependencies & build
echo "▸ Building frontend assets..."
npm ci --production=false
npm run build

# 5. Run database migrations
echo "▸ Running database migrations..."
php artisan migrate --force

# 6. Cache config, routes, views, events
echo "▸ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Restart queue workers
echo "▸ Restarting queue workers..."
php artisan queue:restart

# 8. Clear old caches
echo "▸ Clearing old caches..."
php artisan cache:clear

# 9. Set correct permissions
echo "▸ Setting file permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 10. Restart PHP-FPM (reload OPcache)
echo "▸ Restarting PHP-FPM..."
systemctl restart php8.1-fpm

# 11. Bring app back online
echo "▸ Bringing application online..."
php artisan up

echo ""
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║  ✅ Deployment selesai!                                      ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""
echo "  Cek status: php artisan about"
echo "  Cek queue:  supervisorctl status arsikon-worker:*"
echo ""
