#!/bin/bash

if [ "$1" == "httpd" ]; then
  if [ ! -z "$PHP_SHORT_OPEN_TAG" ]; then
    sed -i "s/\;\?\\s\?short_open_tag = .*/short_open_tag = $PHP_SHORT_OPEN_TAG/" /etc/php7/php.ini
    echo "Set PHP short_open_tag = $PHP_SHORT_OPEN_TAG..."
  fi
  if [ ! -z "$PHP_POST_MAX_SIZE" ]; then
    sed -i "s/\;\?\\s\?post_max_size = .*/post_max_size = $PHP_POST_MAX_SIZE/" /etc/php7/php.ini
    echo "Set PHP post_max_size = $PHP_POST_MAX_SIZE..."
  fi
  if [ ! -z "$APACHE_ERRORLOG" ]; then
    sed -i "s;^ErrorLog.*;ErrorLog $APACHE_ERRORLOG;" /etc/apache2/httpd.conf
    echo "Set Apache ErrorLog to $APACHE_ERRORLOG..."
  fi

  # Setup default config for cservice website
  if [ ! -f "/app/php_includes/cmaster.inc" ]; then
    cp "/app/php_includes/cmaster.inc.dist" "/app/php_includes/cmaster.inc"
  fi
  if [ ! -f "/app/php_include/config.inc.dist" ]; then
    sed "/^die/d" /app/php_includes/config.inc.dist > /app/php_includes/config.inc
    sed -i '/STD_THEME/s;default;unetnew;' /app/php_includes/config.inc
  fi
  if [ ! -f "/app/php_includes/blackhole.inc" ]; then
    cp "/app/php_includes/blackhole.inc.dist" "/app/php_includes/blackhole.inc"
  fi
fi

exec "$@"
