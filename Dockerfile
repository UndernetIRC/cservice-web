FROM alpine:3.17
MAINTAINER ratler@undernet.org

ENV PYTHONUNBUFFERED 1

RUN apk --no-cache update && apk --no-cache upgrade && \
    apk --no-cache --repository=https://dl-cdn.alpinelinux.org/alpine/edge/community add \
    bash \
    busybox-extras \
    apache2 \
    php82-apache2 \
    curl \
    ca-certificates \
    openssl \
    openssh \
    git \
    php82 \
    python3 \
    tzdata

RUN apk --no-cache --repository=https://dl-cdn.alpinelinux.org/alpine/edge/community add \
    php82-phar \
    php82-json \
    php82-iconv \
    php82-openssl \
    php82-xdebug \
    php82-mbstring \
    php82-soap \
    php82-gmp \
    php82-pdo_odbc \
    php82-dom \
    php82-pdo \
    php82-zip \
    php82-sqlite3 \
    php82-pgsql \
    php82-pdo_pgsql \
    php82-bcmath \
    php82-gd \
    php82-odbc \
    php82-gettext \
    php82-xml \
    php82-xmlreader \
    php82-xmlwriter \
    php82-tokenizer \
    php82-bz2 \
    php82-pdo_dblib \
    php82-curl \
    php82-ctype \
    php82-session \
    php82-exif \
    php82-intl \
    php82-fileinfo \
    php82-apcu \
    php82-simplexml \
    composer

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
