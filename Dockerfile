FROM php:8.3-cli

# Prevent Laravel production confirmation prompts
ENV APP_ENV=production
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    intl \
    zip \
    gd \
    pdo \
    pdo_mysql \
    bcmath \
    opcache

# Set working directory
WORKDIR /app

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application
COPY . .

# Create .env BEFORE composer install (needed for artisan scripts)
RUN cp .env.example .env

# Create required directories
RUN mkdir -p storage/framework/{sessions,views,cache,testing} \
    storage/logs \
    bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Install PHP dependencies (skip post-install artisan scripts)
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-scripts

# Generate autoload files without running scripts
RUN composer dump-autoload --optimize --no-scripts

# Make entrypoint executable
RUN chmod +x docker-entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["bash", "docker-entrypoint.sh"]
