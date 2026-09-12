FROM php:8.3-cli-bookworm
COPY --from=composer:2.9.5 /usr/bin/composer /usr/bin/composer
# sqlite3 is deliberately absent from the extension list below. It is already
# compiled into the php:8.3 image (`php -m` lists it), and asking
# docker-php-ext-install to build it again aborts the whole build with
# "Cannot find config.m4" — which is why this image could not be built at all.
# CodeIgniter's SQLite3 database driver still works: the extension is present,
# it simply is not rebuilt here.
#
# mysqli AND pdo_mysql ARE built: the app runs on MySQL, and CodeIgniter's
# default DBDriver is MySQLi while app/Config/Database.php can be switched to
# PDO. Without them the app fails with "could not find driver". Both use the
# bundled mysqlnd, so neither needs an extra system package.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libicu-dev libonig-dev libxml2-dev libsqlite3-dev \
        libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" intl pdo_sqlite mysqli pdo_mysql zip bcmath gd exif pcntl sockets mbstring dom xml \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY composer.json composer.lock ./
ARG COMPOSER_REGISTRY=https://repo.packagist.org
RUN composer config -g repos.packagist composer "$COMPOSER_REGISTRY"
RUN composer install --no-interaction --prefer-dist --no-scripts
COPY . .
COPY docker-entrypoint.sh /usr/local/bin/entrypoint
# Strip any CR before making the entrypoint executable. .gitattributes already
# forces LF on checkout, but that only helps a fresh clone — this keeps a working
# copy that was checked out before it, or copied off a Windows share, from
# producing "env: 'bash\r': No such file or directory" and exit 127.
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint \
    && chmod +x /usr/local/bin/entrypoint && chmod -R 777 writable
EXPOSE 80
ENTRYPOINT ["entrypoint"]
