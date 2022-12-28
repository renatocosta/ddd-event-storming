FROM php:8.0.0-fpm

ARG ENV=development

# enable ffi and install librdkafka
ARG LIBRDKAFKA_VERSION=1.5.x
ENV LIBRDKAFKA_VERSION=$LIBRDKAFKA_VERSION

RUN set -e; \
    apt-get update; \
    apt-get install -y --no-install-recommends git zip unzip gdb libffi-dev libssl-dev; \
    docker-php-ext-configure ffi; \
    docker-php-ext-install -j$(nproc) ffi pcntl; \
    git clone --branch "${LIBRDKAFKA_VERSION}" --depth 1 https://github.com/edenhill/librdkafka.git /tmp/librdkafka; \
    cd /tmp/librdkafka; \
    ./configure; \
    make; \
    make install; \
    ldconfig; \
    apt-get autoremove -y; \
    rm -rf /var/lib/apt/lists/*; \
    rm -rf /tmp/*;


# install rdkafka
ARG RDKAFKA_EXT_VERSION=5.x
RUN git clone --branch "$RDKAFKA_EXT_VERSION" --depth 1 https://github.com/arnaud-lb/php-rdkafka.git /tmp/php-rdkafka; \
    cd /tmp/php-rdkafka; \
    phpize; \
    ./configure; \
    make; \
    make install; \
    rm -rf /tmp/*;

RUN echo 'extension=rdkafka.so' > /usr/local/etc/php/conf.d/docker-php-ext-rdkafka.ini

# composer
ENV COMPOSER_HOME /tmp
ENV COMPOSER_ALLOW_SUPERUSER 1
COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY ./ /var/www/
WORKDIR /var/www/Common/Framework

RUN docker-php-ext-install pdo_mysql; \
    php artisan event:cache; \
    php artisan route:cache; \
    php artisan view:cache; \
    php artisan config:cache; \
    COMPOSER_AUTH='{"github-oauth": {"github.com": "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"}}' composer install --no-dev --prefer-dist;

RUN chown -R 33:33 storage/; \
    chmod o+w storage/ -R;

RUN if [ "$ENV" = "development" ]; then pecl install xdebug-stable; docker-php-ext-enable xdebug; fi

RUN mv "$PHP_INI_DIR/php.ini-${ENV}" "$PHP_INI_DIR/php.ini" && \
    sed -i "s/expose_php = On/expose_php = Off/" "$PHP_INI_DIR/php.ini" && \
    sed -i "s/file_uploads = On/file_uploads = Off/" "$PHP_INI_DIR/php.ini" && \
    sed -i "s/allow_url_include = On/allow_url_include = Off/" "$PHP_INI_DIR/php.ini" && \
    sed -i "s/max_input_time = 60/max_input_time = 30/" "$PHP_INI_DIR/php.ini" && \
    sed -i "s/memory_limit = 128M/memory_limit = 40M/" "$PHP_INI_DIR/php.ini" && \
    sed -i "s/disable_functions =/disable_functions =exec,passthru,shell_exec,system,popen,parse_ini_file,show_source/" "$PHP_INI_DIR/php.ini" && \
    sed -i "s/;cgi.force_redirect = 1/cgi.force_redirect = 1/" "$PHP_INI_DIR/php.ini"