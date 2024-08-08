FROM composer:2.4.2 AS composer
FROM php:8.2-fpm

COPY ./extra.ini /usr/local/etc/php/conf.d/

# User/Group globals
ENV MY_USER="phalcon" \
    MY_GROUP="phalcon" \
    MY_UID="1000" \
    MY_GID="1000" \
    PHP_VERSION="8.2" \
    LANG=en_US.UTF-8 \
    LANGUAGE=en_US.UTF-8 \
    LC_ALL=en_US.UTF-8

# User and Group
RUN set -eux && \
	groupadd -g ${MY_GID} -r ${MY_GROUP} && \
	useradd -u ${MY_UID} -m -s /bin/bash -g ${MY_GROUP} ${MY_USER}

# Update
RUN apt update -y && \
    apt install -y --no-install-recommends \
        apt-utils \
        gettext \
        git \
        libgmp-dev \
        libicu-dev \
        libmagickwand-dev \
        libmemcached-dev \
        libpng-dev \
        libpq-dev \
        libyaml-dev \
        libwebp-dev \
        libxpm-dev \
        libzip-dev \
        locales \
        nano \
        sudo \
        wget \
        zip && \
    rm -rf /var/lib/apt/lists/* /var/cache/apt/archives/*

# PECL Packages
RUN pecl install -o -f redis && \
    pecl install igbinary \
      msgpack \
      apcu \
      yaml \
      imagick \
      memcached \
      xdebug

# Locale
RUN sed -i -e 's/# de_DE.UTF-8 UTF-8/de_DE.UTF-8 UTF-8/' /etc/locale.gen && \
    sed -i -e 's/# el_GR.UTF-8 UTF-8/el_GR.UTF-8 UTF-8/' /etc/locale.gen && \
    sed -i -e 's/# en_US.UTF-8 UTF-8/en_US.UTF-8 UTF-8/' /etc/locale.gen && \
    sed -i -e 's/# es_ES.UTF-8 UTF-8/es_ES.UTF-8 UTF-8/' /etc/locale.gen && \
    sed -i -e 's/# ru_RU.UTF-8 UTF-8/ru_RU.UTF-8 UTF-8/' /etc/locale.gen && \
    dpkg-reconfigure --frontend=noninteractive locales && \
    update-locale LANG=en_US.UTF-8

# Locale Environment
ENV LANG=en_US.UTF-8 \
    LANGUAGE=en_US.UTF-8 \
    LC_ALL=en_US.UTF-8

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype  \
    --with-jpeg=/usr/include/  \
    --with-xpm \
    --with-webp \
    --enable-gd

RUN docker-php-ext-install \
        gd \
        gettext \
        gmp \
        intl \
        pdo_mysql \
        pdo_pgsql \
        zip

# Install PHP extensions
RUN docker-php-ext-enable \
        gettext \
        redis \
        igbinary \
        msgpack \
        apcu \
        imagick \
        yaml \
        memcached \
        xdebug

## Setup the phalcon-module
RUN cd /tmp && \
    git clone https://github.com/niden/phalcon-module && \
    cd phalcon-module/ext && \
    ./install && \
    echo "extension=phalcon" > /usr/local/etc/php/conf.d/phalcon.ini

# Composer
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
# Bash script with helper aliases
COPY ./.bashrc /root/.bashrc

USER $MY_USER

CMD ["php-fpm"]
