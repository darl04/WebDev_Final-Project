#!/bin/bash
set -e

# Railway (and similar platforms) inject PORT; default to 8080 for local Docker.
PORT="${PORT:-8080}"
export PORT

# Map MYSQL_URL to DATABASE_URL if DATABASE_URL is not set (common on Railway)
if [ -z "$DATABASE_URL" ] && [ -n "$MYSQL_URL" ]; then
    export DATABASE_URL="$MYSQL_URL"
    echo "Exported DATABASE_URL from MYSQL_URL"
fi

# Apply the platform port to nginx before startup
sed -i "s/listen 8080 default_server;/listen ${PORT} default_server;/" /etc/nginx/conf.d/symfony.conf
sed -i "s/listen \[::\]:8080 default_server;/listen [::]:${PORT} default_server;/" /etc/nginx/conf.d/symfony.conf

# Ensure JWT keys exist
if [ ! -f config/jwt/private.pem ]; then
    echo "Generating JWT keys..."
    mkdir -p config/jwt
    php bin/console lexik:jwt:generate-keypair --no-interaction || echo "Warning: Failed to generate JWT keys"
fi

# Running database tasks
echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || echo "Warning: migrations did not complete."

echo "Ensuring AUTO_INCREMENT on stock-related tables..."
php bin/console app:db:fix-auto-increment --no-interaction || echo "Warning: AUTO_INCREMENT repair did not complete."

# Do not load demo fixtures in production (Railway)
if [ "${APP_ENV:-prod}" != "prod" ]; then
    echo "Loading fixtures..."
    php bin/console doctrine:fixtures:load --append --no-interaction || true
fi

# Start PHP-FPM cleanly as a background daemon process
echo "Starting PHP-FPM..."
php-fpm -D

# Start Nginx in the foreground
echo "Starting Nginx on port ${PORT}..."
exec nginx -g "daemon off;"
