
# Etapa 1: construir o frontend React
FROM node:22-alpine AS frontend-build

WORKDIR /app/frontend

COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci

COPY frontend/ ./
RUN npm run build


# Etapa 2: preparar o backend PHP
FROM php:8.2-cli AS production

# Extensões necessárias para conectar ao PostgreSQL/Neon

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install \
        pdo_pgsql \
        zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copiar o backend
COPY backend/ /app/backend/

# Instalar o Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar dependências PHP, incluindo vlucas/phpdotenv,
# caso o backend tenha composer.json e composer.lock
RUN composer install \
    --working-dir=/app/backend \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# Copiar o React compilado para a raiz pública
COPY --from=frontend-build /app/frontend/dist/ /app/

# Porta utilizada pelo Render
EXPOSE 10000

# Iniciar o servidor PHP na porta fornecida pelo Render
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} -t /app /app/backend/router.php"]
