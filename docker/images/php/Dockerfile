FROM lavoweb/php-8.3


RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

#COPY bin/docker-entrypoint.sh /docker-entrypoint.sh
#RUN ["chmod", "+x", "/docker-entrypoint.sh"]
#ENTRYPOINT ["/docker-entrypoint.sh"]

WORKDIR /var/www/
