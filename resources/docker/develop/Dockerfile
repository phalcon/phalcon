# syntax=docker/dockerfile:1
ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-fpm AS base

# This part installs the required php extensions to compile phalcon.
# Additional extensions that are mostly used are installed here too.

ARG PHP_VERSION=8.4
ARG UID=1000
ARG GID=1000
ARG USER=phalcon
ARG GROUP=phalcon

# PIE installs the PECL extensions. It is only available during this step.
RUN --mount=type=bind,from=ghcr.io/php/pie:1-bin,source=/pie,target=/usr/local/bin/pie <<EOT
set -eux
# Add user and group
groupadd -g "${GID}" "${GROUP}"
useradd -l -m -u "${UID}" -g "${GID}" "${USER}"
usermod -s /bin/bash "${USER}"
install -d -m 0770 -o "${USER}" -g "${GROUP}" /app
# Install needed packages
apt update -y
apt install -yq --no-install-recommends \
    apt-utils \
    gettext \
    git \
    libgmp-dev \
    libicu-dev \
    libmagickwand-dev \
    libmemcached-dev \
    libonig-dev \
    libpng-dev \
    libpq-dev \
    libtool \
    libwebp-dev \
    libxpm-dev \
    libyaml-dev \
    libzip-dev \
    locales \
    nano \
    sudo \
    unzip \
    wget \
    zip
sed -i -e 's/# de_DE.UTF-8 UTF-8/de_DE.UTF-8 UTF-8/' /etc/locale.gen
sed -i -e 's/# el_GR.UTF-8 UTF-8/el_GR.UTF-8 UTF-8/' /etc/locale.gen
sed -i -e 's/# en_US.UTF-8 UTF-8/en_US.UTF-8 UTF-8/' /etc/locale.gen
sed -i -e 's/# es_ES.UTF-8 UTF-8/es_ES.UTF-8 UTF-8/' /etc/locale.gen
sed -i -e 's/# ru_RU.UTF-8 UTF-8/ru_RU.UTF-8 UTF-8/' /etc/locale.gen
dpkg-reconfigure --frontend=noninteractive locales
update-locale LANG=en_US.UTF-8
# Install base extensions
# (PIE also enables each extension)
if php -r 'exit(PHP_VERSION_ID < 80600 ? 0 : 1);'; then
    # Packagist has only a release candidate of igbinary for PIE
    pie install --no-cache "igbinary/igbinary:^3.2@RC"
    pie install --no-cache msgpack/msgpack-php
    pie install --no-cache apcu/apcu
    pie install --no-cache pecl/yaml
    pie install --no-cache imagick/imagick
    pie install --no-cache php-memcached/php-memcached
    pie install --no-cache phpredis/phpredis
    pie install --no-cache xdebug/xdebug
else
    # The releases do not compile on PHP 8.6 yet. Use the development
    # branches until there are releases that support PHP 8.6.
    pie install --no-cache igbinary/igbinary:dev-master
    pie install --no-cache msgpack/msgpack-php:dev-master
    pie install --no-cache apcu/apcu:dev-master
    pie install --no-cache pecl/yaml:dev-php7
    pie install --no-cache imagick/imagick:dev-master
    pie install --no-cache php-memcached/php-memcached:dev-master
    pie install --no-cache phpredis/phpredis:dev-develop
    # Only an alpha of Xdebug supports PHP 8.6
    pie install --no-cache "xdebug/xdebug:^3.6@alpha"
fi
# Configure GD
docker-php-ext-configure gd --with-freetype \
    --with-jpeg=/usr/include/ \
    --with-xpm \
    --with-webp \
    --enable-gd
# Install extensions
docker-php-ext-install \
    gd \
    gettext \
    gmp \
    intl \
    pcntl \
    pdo_mysql \
    pdo_pgsql \
    zip
# The PHP 8.6 image does not have mbstring (the older images have it for PECL)
if ! php -m | grep -qx mbstring; then
    docker-php-ext-install mbstring
fi
# Copy ini file
cp -p /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
# Set correct pid file location and permissions
install -d -m 0770 -o "${USER}" -g "${GROUP}" /run/php
# Cleanup
apt-get autoremove --purge -y
apt-get autoclean -y
apt-get clean -y
rm -rf /tmp/* /var/tmp/*
find /var/cache/apt/archives /var/lib/apt/lists -not -name lock -type f -delete
find /var/cache -type f -delete
find /var/log -type f -delete
EOT

# hadolint ignore=DL3022
COPY --from=composer/composer:2 --chown=${USER}:${GROUP} --chmod=0770 /usr/bin/composer /usr/bin/composer
COPY resources/docker/develop/.bashrc /home/${USER}/.bashrc
COPY resources/docker/develop/extra.ini /usr/local/etc/php/conf.d/

# User/Group globals
ENV LANG=en_US.UTF-8 \
    LANGUAGE=en_US.UTF-8 \
    LC_ALL=en_US.UTF-8

SHELL [ "/bin/bash", "--rcfile", "/home/${USER}/.bashrc", "-o", "pipefail", "-c" ]

WORKDIR /app

USER ${USER}
