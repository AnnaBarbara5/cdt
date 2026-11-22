# Estágio 1: CSS (Tailwind)
FROM node:22-alpine AS assets
WORKDIR /build
COPY package.json package-lock.json* ./
RUN npm ci --ignore-scripts 2>/dev/null || npm install
COPY CSS/input.css CSS/
RUN npx --yes @tailwindcss/cli -i CSS/input.css -o CSS/app.css --minify

# Estágio 2: PHP + Apache
FROM php:8.2-apache-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev unzip git curl \
    && docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .
COPY --from=assets /build/CSS/app.css CSS/app.css

RUN chown -R www-data:www-data /var/www/html \
    && mkdir -p /var/www/html/data \
    && chown www-data:www-data /var/www/html/data

COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/cdt.ini

ENV APP_ENV=production
EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD curl -fsS http://127.0.0.1/index.html || exit 1