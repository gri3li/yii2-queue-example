FROM php:8.3-fpm-alpine3.20 AS main
# install pgsql driver
RUN apk --no-cache add postgresql-dev
RUN docker-php-ext-install pdo_pgsql


FROM main AS deploy
# install composer
RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer


FROM main AS worker
# install supervisor
RUN apk --no-cache add supervisor
