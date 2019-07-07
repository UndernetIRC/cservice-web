FROM alpine:3.10
MAINTAINER ratler@undernet.org

ENV PYTHONUNBUFFERED 1

RUN apk --no-cache update && apk --no-cache upgrade && \
    apk --no-cache add \
    bash \
    busybox-extras \
    apache2 \
    php7-apache2 \
    curl \
    ca-certificates \
    openssl \
    openssh \
    git \
    php7 \
    python3 \
    tzdata

RUN apk --no-cache add \
    php7-phar \
    php7-json \
    php7-iconv \
    php7-openssl \
    php7-xdebug \
    php7-mcrypt \
    php7-mbstring \
    php7-soap \
    php7-gmp \
    php7-pdo_odbc \
    php7-dom \
    php7-pdo \
    php7-zip \
    php7-sqlite3 \
    php7-pgsql \
    php7-pdo_pgsql \
    php7-bcmath \
    php7-gd \
    php7-odbc \
    php7-gettext \
    php7-xml \
    php7-xmlreader \
    php7-xmlwriter \
    php7-tokenizer \
    php7-xmlrpc \
    php7-bz2 \
    php7-pdo_dblib \
    php7-curl \
    php7-ctype \
    php7-session \
    php7-exif \
    php7-intl \
    php7-fileinfo \
    php7-apcu \
    php7-simplexml

# Composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# Setup apache
RUN for m in rewrite_module session_module session_cookie_module ession_crypto_module deflate_module; do \
    sed -i "/^#LoadModule $m/s;#;;" /etc/apache2/httpd.conf; done
RUN sed -i "s;^#DocumentRoot.*;DocumentRoot /app/docs/gnuworld;" /etc/apache2/httpd.conf && \
    sed -i "s;/var/www/localhost/htdocs;/app/docs/gnuworld;" /etc/apache2/httpd.conf && \
    printf "\n<Directory /app>\n\tRequire all granted\n</Directory>\n" >> /etc/apache2/httpd.conf

COPY docker-entrypoint.sh /usr/local/bin/

WORKDIR /app
ENTRYPOINT ["docker-entrypoint.sh"]
EXPOSE 5000

CMD ["httpd", "-D", "FOREGROUND"]
