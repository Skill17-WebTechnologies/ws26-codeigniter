FROM php:8.3-cli-bookworm

# Composer pinned to 2.9.5
COPY --from=composer:2.9.5 /usr/bin/composer /usr/bin/composer

# System libs + PHP extensions commonly required by the PHP frameworks
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libicu-dev libonig-dev libxml2-dev \
        libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" \
        intl pdo_mysql mysqli zip bcmath gd exif pcntl sockets mbstring dom xml \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . .

RUN composer install --no-interaction --prefer-dist

EXPOSE 8080
# Serve the CodeIgniter4 welcome app
CMD ["php", "spark", "serve", "--host", "0.0.0.0", "--port", "8080"]
