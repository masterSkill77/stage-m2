FROM php:8.4-cli-alpine

# Extensions PHP utilisées par Laravel et les SDK de paiement, sans garder les outils de compilation
RUN apk add --no-cache icu-libs libzip \
    && apk add --no-cache --virtual .build-deps icu-dev libzip-dev \
    && docker-php-ext-install pdo_mysql bcmath intl zip \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

EXPOSE 8000
