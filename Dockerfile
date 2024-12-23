FROM alpine:3.21
MAINTAINER ratler@undernet.org

ENV PYTHONUNBUFFERED 1

RUN apk --no-cache update && apk --no-cache upgrade && \
    apk --no-cache --repository=https://dl-cdn.alpinelinux.org/alpine/edge/community add \
    bash \
    busybox-extras \
    apache2 \
    php84-apache2 \
    curl \
    ca-certificates \
    openssl \
    openssh \
    git \
    php84 \
    python3 \
    tzdata

RUN apk --no-cache --repository=https://dl-cdn.alpinelinux.org/alpine/edge/community add \
    php84-phar \
    php84-json \
    php84-iconv \
    php84-openssl \
    php84-xdebug \
    php84-mbstring \
    php84-soap \
    php84-gmp \
    php84-pdo_odbc \
    php84-dom \
    php84-pdo \
    php84-zip \
    php84-sqlite3 \
    php84-pgsql \
    php84-pdo_pgsql \
    php84-bcmath \
    php84-gd \
    php84-odbc \
    php84-gettext \
    php84-xml \
    php84-xmlreader \
    php84-xmlwriter \
    php84-tokenizer \
    php84-bz2 \
    php84-pdo_dblib \
    php84-curl \
    php84-ctype \
    php84-session \
    php84-exif \
    php84-intl \
    php84-fileinfo \
    php84-apcu \
    php84-simplexml \
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
