# =============================================================================
# Multi-Stage Dockerfile for Laravel CRM Makin
# Otimizado para produção com segurança e performance
# =============================================================================

# -----------------------------------------------------------------------------
# Stage 1: Dependências PHP (Composer)
# -----------------------------------------------------------------------------
FROM composer:2.7 AS composer-deps

WORKDIR /app

# Copiar apenas arquivos de dependências para cache eficiente
COPY composer.json composer.lock ./

# Instalar dependências de produção (sem dev)
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --optimize-autoloader \
    && composer clear-cache

# -----------------------------------------------------------------------------
# Stage 2: Dependências PHP com dev (para testes)
# -----------------------------------------------------------------------------
FROM composer:2.7 AS composer-deps-dev

WORKDIR /app

COPY composer.json composer.lock ./

# Instalar todas as dependências incluindo dev
RUN composer install \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    && composer clear-cache

# -----------------------------------------------------------------------------
# Stage 3: Dependências Node.js (Assets)
# -----------------------------------------------------------------------------
FROM node:20-alpine AS node-deps

WORKDIR /app

# Copiar package files
COPY package*.json ./

# Instalar dependências Node
RUN npm ci --only=production && npm cache clean --force

# -----------------------------------------------------------------------------
# Stage 4: Build Assets (Production)
# -----------------------------------------------------------------------------
FROM node:20-alpine AS assets-build

WORKDIR /app

# Copiar dependências
COPY package*.json ./
RUN npm ci

# Copiar código fonte necessário para build
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

# Build assets
RUN npm run build

# -----------------------------------------------------------------------------
# Stage 5: Runtime Base (PHP-FPM)
# -----------------------------------------------------------------------------
FROM php:8.2-fpm-alpine AS runtime-base

# Instalar dependências de sistema necessárias
RUN apk add --no-cache \
    bash \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    postgresql-dev \
    oniguruma-dev \
    mysql-client \
    redis \
    supervisor \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        zip \
        gd \
        mbstring \
        bcmath \
        opcache \
    && pecl install redis-6.0.2 \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS \
    && rm -rf /tmp/pear

# Configurações PHP otimizadas
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# -----------------------------------------------------------------------------
# Stage 6: Development
# -----------------------------------------------------------------------------
FROM runtime-base AS development

# Instalar Node.js e npm
RUN apk add --no-cache nodejs npm

# Instalar Xdebug para debugging
RUN apk add --no-cache $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del $PHPIZE_DEPS linux-headers

COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Criar usuário não-root
RUN addgroup -g 1000 laravel && \
    adduser -D -u 1000 -G laravel laravel

WORKDIR /var/www/html

# Copiar o composer para uso no desenvolvimento
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copiar dependências
COPY --from=composer-deps-dev --chown=laravel:laravel /app/vendor ./vendor

# Copiar código
COPY --chown=laravel:laravel . .

# Gerar autoload
RUN composer dump-autoload --optimize

# Permissões
RUN chown -R laravel:laravel \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

USER laravel

EXPOSE 9000

CMD ["php-fpm"]

# -----------------------------------------------------------------------------
# Stage 7: Production
# -----------------------------------------------------------------------------
FROM runtime-base AS production

# Criar usuário não-root com UID/GID específicos
RUN addgroup -g 1000 laravel && \
    adduser -D -u 1000 -G laravel laravel

WORKDIR /var/www/html

# Copiar dependências de produção
COPY --from=composer-deps --chown=laravel:laravel /app/vendor ./vendor

# Copiar assets buildados
COPY --from=assets-build --chown=laravel:laravel /app/public ./public

# Copiar o composer temporariamente
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copiar código da aplicação
COPY --chown=laravel:laravel . .

# Gerar autoload otimizado
RUN composer dump-autoload --optimize --classmap-authoritative \
    && rm -f /usr/bin/composer

# Criar diretórios necessários e configurar permissões
RUN mkdir -p storage/framework/{sessions,views,cache} \
    storage/logs \
    bootstrap/cache \
    && chown -R laravel:laravel storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Health check script
COPY --chown=laravel:laravel docker/health-check.sh /usr/local/bin/health-check.sh
RUN chmod +x /usr/local/bin/health-check.sh

# Configuração do Supervisor para queue workers
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

USER laravel

EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD ["/usr/local/bin/health-check.sh"]

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# -----------------------------------------------------------------------------
# Stage 8: Queue Worker (Production)
# -----------------------------------------------------------------------------
FROM production AS queue-worker

USER root

# Configuração específica para workers
COPY docker/supervisor/queue-worker.conf /etc/supervisor/conf.d/queue-worker.conf

USER laravel

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/queue-worker.conf"]
