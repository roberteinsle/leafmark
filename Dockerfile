# PHP-Version: immer der neueste stabile Branch (Stand 2026-10-22: 8.5 → 8.5.12)
# Steht an drei Stellen, im selben Commit ändern: Dockerfile, composer.json, .devcontainer/devcontainer.json
ARG PHP_VERSION=8.5

# --- Stufe 1: Frontend-Assets (Vite) ---
FROM node:22-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# --- Stufe 2: Composer (gleiche PHP-Version wie Runtime) ---
FROM serversideup/php:${PHP_VERSION}-cli-alpine AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction
COPY . .
RUN composer dump-autoload --optimize --no-dev

# --- Stufe 3: Runtime ---
FROM serversideup/php:${PHP_VERSION}-fpm-nginx-alpine

# Kurzer Commit-Hash für die Versionsanzeige im Footer (wird von der GitHub Action gesetzt)
ARG GIT_SHA=unknown

ENV AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_MIGRATION=true \
    PHP_OPCACHE_ENABLE=1 \
    PHP_UPLOAD_MAX_FILE_SIZE=25M \
    PHP_POST_MAX_SIZE=30M \
    PHP_MEMORY_LIMIT=256M \
    NGINX_CLIENT_MAX_BODY_SIZE=30M

USER root
RUN install-php-extensions intl gd zip bcmath exif pdo_pgsql
USER www-data

WORKDIR /var/www/html
COPY --chown=www-data:www-data --from=vendor /app /var/www/html
COPY --chown=www-data:www-data --from=assets /app/public/build /var/www/html/public/build
RUN echo "${GIT_SHA}" > /var/www/html/VERSION_HASH
