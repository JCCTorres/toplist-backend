#!/bin/bash

echo "=== TopList Backend Starting ==="

# Ensure .env exists (Railway env vars override these)
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Quick database connectivity check (5 second timeout)
echo "=== Checking Database Connectivity ==="
DB_AVAILABLE=false
if php -r "
    \$host = getenv('DB_HOST') ?: '127.0.0.1';
    \$port = getenv('DB_PORT') ?: '3306';
    \$timeout = 5;
    \$sock = @fsockopen(\$host, \$port, \$errno, \$errstr, \$timeout);
    if (\$sock) { fclose(\$sock); exit(0); } else { exit(1); }
" 2>/dev/null; then
    DB_AVAILABLE=true
    echo "Database is reachable"
else
    echo "WARNING: Database is NOT reachable — skipping migrations and imports"
fi

if [ "$DB_AVAILABLE" = true ]; then
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
else
    echo "=== Skipping Migrations and Imports (no DB) ==="
fi

echo "=== Clearing Stale Caches ==="
php artisan route:clear --no-interaction 2>&1 || echo "WARNING: Route clear failed"
php artisan config:clear --no-interaction 2>&1 || echo "WARNING: Config clear failed"
php artisan view:clear --no-interaction 2>&1 || echo "WARNING: View clear failed"

echo "=== Caching Config ==="
php artisan config:cache --no-interaction 2>&1 || echo "WARNING: Config cache failed"
php artisan route:cache --no-interaction 2>&1 || echo "WARNING: Route cache failed"
php artisan view:cache --no-interaction 2>&1 || echo "WARNING: View cache failed"

# Background sync: fetch rates from Bookerville API and warm cache
# Backgrounded so the server starts immediately for Railway health checks
if [ "$DB_AVAILABLE" = true ]; then
    echo "=== Starting Bookerville Sync & Cache Warm (background) ==="
    php artisan cache:clear --no-interaction 2>&1 || true
    (php artisan bookerville:sync-and-warm --no-interaction 2>&1 || echo "WARNING: Sync failed, falling back to live rate fetching") &
fi

echo "=== Starting Server on port ${PORT:-8080} ==="
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080} --no-interaction
