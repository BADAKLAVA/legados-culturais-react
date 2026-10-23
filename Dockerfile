
# ==========================================
# ETAPA 1: Compilar o frontend React + Vite
# ==========================================
FROM node:22-alpine AS frontend-build

WORKDIR /app/frontend

COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci

COPY frontend/ ./
RUN npm run build


# ==========================================
# ETAPA 2: Preparar o backend PHP
# ==========================================
FROM php:8.2.35-cli AS production

# Dependências do PostgreSQL, ZIP e Composer
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        libzip-dev \
        unzip \
        zip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copiar o backend PHP
COPY backend/ /app/backend/

# Instalar o Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar dependências PHP, caso exista composer.json
RUN if [ -f /app/backend/composer.json ]; then \
      composer install \
        --working-dir=/app/backend \
        --no-dev \
        --optimize-autoloader; \
    fi

# Copiar o frontend compilado pelo Vite
COPY --from=frontend-build /app/frontend/dist/ /app/public/

# Porta utilizada pelo Render
EXPOSE 10000

# Iniciar o servidor PHP com o router
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} -t /app/public /app/backend/router.php"]
