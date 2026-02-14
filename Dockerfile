FROM php:8.2-fpm-alpine

RUN apk update && apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    mysql-client \
    nodejs \
    npm \
    autoconf \
    g++ \
    make \
    linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        zip \
        gd \
        mbstring \
        bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make linux-headers \
    && rm -rf /tmp/pear /var/cache/apk/*

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/memory.ini \
    && echo "upload_max_filesize = 100M" >> /usr/local/etc/php/conf.d/memory.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/memory.ini

RUN addgroup -g 1000 laravel && \
    adduser -D -u 1000 -G laravel laravel

WORKDIR /var/www/html

COPY --chown=laravel:laravel . .

RUN composer install --no-interaction --prefer-dist

RUN npm install

RUN mkdir -p storage/framework/{sessions,views,cache} \
    storage/logs \
    bootstrap/cache \
    && chown -R laravel:laravel storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER laravel

EXPOSE 9000

CMD ["php-fpm"]
