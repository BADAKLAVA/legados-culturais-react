# Etapa 1: compilar o React com Vite
FROM node:22-alpine AS frontend-build

WORKDIR /app/frontend

COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci

COPY frontend/ ./
RUN npm run build


# Etapa 2: executar o PHP
FROM php:8.2-cli AS production

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copiar o backend
COPY backend/ /app/backend/

# Instalar o Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar dependências PHP, se houver composer.json
RUN if [ -f /app/backend/composer.json ]; then \
      composer install --working-dir=/app/backend --no-dev --optimize-autoloader; \
    fi

# Copiar os arquivos compilados do React
COPY --from=frontend-build /app/frontend/dist/ /app/public/

EXPOSE 10000

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} -t /app/public /app/backend/router.php"]
