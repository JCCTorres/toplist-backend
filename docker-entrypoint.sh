#!/bin/bash

echo "=== TopList Backend Starting ==="

# Ensure .env exists (Railway env vars override these)
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

echo "=== Running Migrations ==="
php artisan migrate --force --no-interaction 2>&1 || echo "WARNING: Migrations failed, continuing..."

echo "=== Importing Property Data ==="
# Import properties from JSON files (idempotent - updates if exists)
if [ -f "toplist.properties.json" ]; then
    echo "Importing Bookerville properties..."
    php artisan bookerville:import toplist.properties.json --no-interaction 2>&1 || echo "WARNING: Properties import failed"
else
    echo "WARNING: toplist.properties.json not found, skipping property import"
fi

if [ -f "toplist.client_properties.json" ]; then
    echo "Importing client properties..."
    php artisan client-properties:import toplist.client_properties.json --no-interaction 2>&1 || echo "WARNING: Client properties import failed"
else
    echo "WARNING: toplist.client_properties.json not found, skipping client property import"
fi

echo "=== Clearing Stale Caches ==="
php artisan route:clear --no-interaction 2>&1 || echo "WARNING: Route clear failed"
php artisan config:clear --no-interaction 2>&1 || echo "WARNING: Config clear failed"
php artisan view:clear --no-interaction 2>&1 || echo "WARNING: View clear failed"

echo "=== Caching Config ==="
php artisan config:cache --no-interaction 2>&1 || echo "WARNING: Config cache failed"
php artisan route:cache --no-interaction 2>&1 || echo "WARNING: Route cache failed"
php artisan view:cache --no-interaction 2>&1 || echo "WARNING: View cache failed"

echo "=== Starting Server on port ${PORT:-8080} ==="
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080} --no-interaction
