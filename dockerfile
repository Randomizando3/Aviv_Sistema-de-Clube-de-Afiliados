FROM php:8.2-cli

# Dependências do sistema
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev pkg-config libssl-dev default-mysql-client git unzip \
 && docker-php-ext-install pdo_mysql \
 && pecl install xdebug || true \
 && docker-php-ext-enable pdo_mysql

# Adiciona o Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/app
