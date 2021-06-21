FROM ubuntu:20.04

ENV DB_USERNAME=db_user
ENV DB_PASSWORD=db_password
ENV DB_USERNAME_2=db_user_2
ENV DB_PASSWORD_2=db_password_2

# ------------------------------------------------------------------
# Use BASH, not SH
# ------------------------------------------------------------------
SHELL ["/bin/bash", "-c"]

# ------------------------------------------------------------------
# Update Package Cache and Set Timezone
# ------------------------------------------------------------------
RUN apt-get update

RUN apt-get install -y tzdata
ENV TZ "America/New_York"
RUN echo "America/New_York" > /etc/timezone \
    && dpkg-reconfigure --frontend noninteractive tzdata

# ------------------------------------------------------------------
# Install Packages and Dependencies
# ------------------------------------------------------------------
RUN DEBIAN_FRONTEND='noninteractive' apt-get install -y --no-install-recommends \
    # -- General Utilities --
    git \
    nano \
    # -- Apache --
    apache2 \
    libapache2-mod-php \
    # -- Maria DB --
    mariadb-server \
    # -- PHP | 20.04 repos point to 7.4 --
    php \
    php-gd \
    php-xml \
    php-zip \
    php-mysql \
    php-cli \
    php-curl \
    php-mbstring \
    # -- Node --
    nodejs \
    npm

# ------------------------------------------------------------------
# Install Composer Globally
# https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md
# ------------------------------------------------------------------
WORKDIR /
COPY ./docker/install-composer.sh install-composer.sh
RUN chmod +x /install-composer.sh \
    && /install-composer.sh \
    && mv composer.phar /usr/local/bin/composer

# ------------------------------------------------------------------
# Make Public-Facing Web Directory
# ------------------------------------------------------------------
RUN mkdir /var/www/ccplus
COPY ./docker/ccplus.conf /etc/apache2/sites-available/ccplus.conf

# ------------------------------------------------------------------
# STEP 1: Enable mod_rewrite
# ------------------------------------------------------------------
RUN a2enmod rewrite \
    && a2dissite 000-default \
    && a2ensite ccplus \
    && service apache2 restart

# ------------------------------------------------------------------
# STEP 2: Application Clone
# ------------------------------------------------------------------
WORKDIR /usr/local
RUN git clone https://github.com/palcilibraries/CC-Plus.git

# ------------------------------------------------------------------
# Step 3: Setup the Environment
# ------------------------------------------------------------------
# Create CC-Plus Reports Directory
RUN mkdir /usr/local/stats_reports \
    && chown root:www-data /usr/local/stats_reports \
    && chmod g+rw /usr/local/stats_reports

# ------------------------------------------------------------------
# Step 4: Install the application
# ------------------------------------------------------------------
COPY . /usr/local/CC-Plus/
# Allow Reverse Proxies
COPY ./docker/TrustProxies.php /usr/local/CC-Plus/app/Http/Middleware/TrustProxies.php
# Set exit code to 0 for automated build
COPY ./docker/ConsortiumCommand.php /usr/local/CC-Plus/app/Console/Commands/ConsortiumCommand.php

WORKDIR /usr/local/CC-Plus/app/Console
RUN cp Kernel.php.example-single ./Kernel.php

WORKDIR /usr/local/CC-Plus
RUN composer install \
    && npm install \
    && php artisan key:generate \
    && npm run prod \
    && chown -R root:www-data storage \
    && chmod -R g+rw storage \
    && chown root:www-data bootstrap/cache \
    && chmod g+rw bootstrap/cache

# ------------------------------------------------------------------
# Step 5: Update the Webserver Directory
# ------------------------------------------------------------------
WORKDIR /usr/local/CC-Plus/public
RUN mv index.php.example ./index.php \
    && cp -r /usr/local/CC-Plus/public/. /var/www/ccplus/ \
    && chown -R root:www-data /var/www/ccplus

# ------------------------------------------------------------------
# Step 6: Setup Initial Databases
# ------------------------------------------------------------------
## Warning: Grants ALL on ALL.
WORKDIR /
COPY ./.env .env
COPY ./docker/db_create.sh db_create.sh
RUN chmod +x /db_create.sh \
    && /db_create.sh

# ------------------------------------------------------------------
# Step 7: Migrate Initial Database Tables
# ------------------------------------------------------------------
WORKDIR /usr/local/CC-Plus
RUN service mysql start \
    && php artisan migrate:fresh --database=globaldb --path=database/migrations/global \
    && php artisan migrate:fresh --database=con_template --path=database/migrations/con_template

# ------------------------------------------------------------------
# Step 8: Seed Tables
# ------------------------------------------------------------------
RUN service mysql start \
    && php artisan db:seed

# ------------------------------------------------------------------
# Step 9: Add a Consortium
# ------------------------------------------------------------------
WORKDIR /usr/local/CC-Plus
COPY ./docker/create_consortium.sh ./create_consortium.sh
RUN chmod +x create_consortium.sh \
    && ./create_consortium.sh

# ------------------------------------------------------------------
# RUN
# ------------------------------------------------------------------
WORKDIR /
COPY ./docker/start.sh ./start.sh
RUN chmod +x start.sh
EXPOSE 80
CMD ["./start.sh"]
