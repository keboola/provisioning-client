FROM php:7.1

ENV DEBIAN_FRONTEND noninteractive
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_PROCESS_TIMEOUT 3600
ARG COMPOSER_FLAGS="--prefer-dist --no-interaction"

RUN apt-get update \
  && apt-get install unzip git unixodbc-dev libpq-dev -y

RUN echo "memory_limit = -1" >> /usr/local/etc/php/php.ini


RUN docker-php-ext-install pdo_pgsql

# https://github.com/docker-library/php/issues/103
RUN set -x \
    && docker-php-source extract \
    && cd /usr/src/php/ext/odbc \
    && phpize \
    && sed -ri 's@^ *test +"\$PHP_.*" *= *"no" *&& *PHP_.*=yes *$@#&@g' configure \
    && ./configure --with-unixODBC=shared,/usr \
    && docker-php-ext-install odbc pdo_mysql \
    && docker-php-source delete

## install snowflake drivers
ADD https://sfc-repo.snowflakecomputing.com/odbc/linux/2.16.10/snowflake_linux_x8664_odbc-2.16.10.tgz snowflake_linux_x8664_odbc-2.16.10.tgz
RUN tar -xvzf snowflake_linux_x8664_odbc-2.16.10.tgz \
  && mv snowflake_odbc /usr/bin/
COPY ./docker/snowflake/simba.snowflake.ini /etc/simba.snowflake.ini
COPY ./docker/snowflake/odbcinst.ini /etc/odbcinst.ini
RUN mkdir -p  /usr/bin/snowflake_odbc/log

ENV SIMBAINI /etc/simba.snowflake.ini
ENV SSL_DIR /usr/bin/snowflake_odbc/SSLCertificates/nssdb
ENV LD_LIBRARY_PATH /usr/bin/snowflake_odbc/lib

# install composer
COPY docker/composer-install.sh /tmp/composer-install.sh
RUN chmod +x /tmp/composer-install.sh
RUN /tmp/composer-install.sh

WORKDIR /code

## deps always cached unless changed
# First copy only composer files
COPY composer.* /code/
# Download dependencies, but don't run scripts or init autoloaders as the app is missing
RUN composer install $COMPOSER_FLAGS --no-scripts --no-autoloader
# copy rest of the app
COPY . /code/
# run normal composer - all deps are cached already
RUN composer install $COMPOSER_FLAGS
