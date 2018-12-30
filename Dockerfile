FROM wordpress:5-php7.2

RUN apt-get update && apt-get install -y less wget subversion mysql-client ssmtp mailutils cron vim
RUN apt-get install -y libyaml-dev && pecl install yaml-2.0.2
RUN echo "extension=yaml.so" > /usr/local/etc/php/conf.d/docker-php-ext-yaml.ini

RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp

RUN wget http://cs.sensiolabs.org/download/php-cs-fixer-v2.phar -O php-cs-fixer && \
    chmod a+x php-cs-fixer && \
    mv php-cs-fixer /usr/local/bin/php-cs-fixer

COPY devenv/config/wp-config.php /var/www/html/wp-config.php
COPY devenv/config/php.ini /usr/local/etc/php/conf.d/php.ini
COPY src/post-types.yml /var/www/html/post-types.yml
COPY devenv/config/ssmtp.conf /etc/ssmtp/ssmtp.conf
