FROM php:8.2-fpm

RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip cron \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

WORKDIR /var/www/symfony_docker

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
RUN git config --global user.email "faustbtl17@gmail.com" \
    && git config --global user.name "fbotel17"

# Copier le fichier crontab dans le conteneur
COPY crontab /etc/cron.d/simple-cron

# Appliquer les permissions pour le fichier crontab
RUN chmod 0644 /etc/cron.d/simple-cron

# Créer le répertoire de logs pour cron
RUN touch /var/log/cron.log

# Démarrer le service cron en avant-plan
CMD cron && php-fpm