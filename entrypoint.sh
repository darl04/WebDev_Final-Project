#!/bin/bash
set -e

# Map MYSQL_URL to DATABASE_URL if DATABASE_URL is not set (common on Railway)
if [ -z "$DATABASE_URL" ] && [ -n "$MYSQL_URL" ]; then
    export DATABASE_URL="$MYSQL_URL"
    echo "Exported DATABASE_URL from MYSQL_URL"
fi

# Ensure JWT keys exist
if [ ! -f config/jwt/private.pem ]; then
    echo "Generating JWT keys..."
    mkdir -p config/jwt
    php bin/console lexik:jwt:generate-keypair --no-interaction || echo "Warning: Failed to generate JWT keys"
fi

# Running database tasks
echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true

echo "Loading fixtures..."
php bin/console doctrine:fixtures:load --append --no-interaction || true

# Start PHP-FPM cleanly as a background daemon process
echo "Starting PHP-FPM..."
php-fpm -D

# Start Nginx in the foreground
echo "Starting Nginx on port 8080..."
exec nginx -g "daemon off;"