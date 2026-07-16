# Stage 1: Install Composer Dependencies
FROM webdevops/php-nginx:8.3-alpine AS composer-builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-dev

# Stage 2: Build Frontend Assets
FROM node:22-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
# Copy vendor directory from Stage 1 so Vite can resolve Flux UI CSS
COPY --from=composer-builder /app/vendor ./vendor
RUN npm run build

# Stage 3: Final Production Image
FROM webdevops/php-nginx:8.3-alpine
WORKDIR /app

COPY ./dockerizer/php.ini /opt/docker/etc/php/php.ini
COPY ./dockerizer/vhost.conf /opt/docker/etc/nginx/vhost.conf

# Copy vendor folders and app source files
COPY --from=composer-builder /app/vendor ./vendor
COPY . .

# Copy compiled assets from Stage 2
COPY --from=node-builder /app/public/build ./public/build

# Copy entrypoint script
# COPY ./dockerizer/entrypoint.sh /entrypoint.sh
# RUN chmod +x /entrypoint.sh

# Setup file permissions
RUN chown -R application:application /app \
    && chmod -R 755 /app \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# ENTRYPOINT ["/entrypoint.sh"]
# CMD ["supervisord"]