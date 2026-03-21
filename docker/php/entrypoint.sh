#!/bin/sh
set -e

# Ensure all required storage directories exist
mkdir -p /var/www/storage/framework/cache/data
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/logs
mkdir -p /var/www/storage/app/public
mkdir -p /var/www/bootstrap/cache

# Fix ownership and permissions so www-data (PHP-FPM) can write
# chown may fail in rootless Docker environments, so we fall back to world-writable chmod
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || \
    chmod -R 777 /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Remove stale bootstrap cache files that may have been committed to the repo.
# They will be regenerated at runtime by artisan commands.
# Without this, outdated service/package discovery caches can cause Laravel to
# fail on startup (class not found, provider mismatch), crashing PHP-FPM workers.
rm -f /var/www/bootstrap/cache/config.php
rm -f /var/www/bootstrap/cache/routes-v7.php
rm -f /var/www/bootstrap/cache/services.php
rm -f /var/www/bootstrap/cache/packages.php
rm -f /var/www/bootstrap/cache/events.php

# Validate PHP-FPM configuration before starting (exits non-zero and prints errors on failure)
php-fpm -t

exec "$@"
