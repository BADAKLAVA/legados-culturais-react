FROM node:22-alpine AS frontend-build

WORKDIR /app/frontend

COPY frontend/package*.json ./
RUN npm install

COPY frontend/ ./
RUN npm run build

FROM php:8.2-cli

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pgsql pdo_pgsql && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY backend/ /app/backend/
RUN composer install --working-dir=/app/backend --no-dev --optimize-autoloader

COPY --from=frontend-build /frontend/dist/ /app/

EXPOSE 10000

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} -t /app"]
