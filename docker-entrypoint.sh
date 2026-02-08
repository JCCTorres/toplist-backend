#!/bin/bash

echo "=== TopList Backend Starting ==="

# Ensure .env exists (Railway env vars override these)
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

echo "=== Running Migrations ==="
php artisan migrate --force --no-interaction 2>&1 || echo "WARNING: Migrations failed, continuing..."

echo "=== Caching Config ==="
php artisan config:cache --no-interaction 2>&1 || echo "WARNING: Config cache failed"
php artisan route:cache --no-interaction 2>&1 || echo "WARNING: Route cache failed"
php artisan view:cache --no-interaction 2>&1 || echo "WARNING: View cache failed"

echo "=== Starting Server on port ${PORT:-8080} ==="
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080} --no-interaction
