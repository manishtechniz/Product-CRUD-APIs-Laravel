#-------------------------------------------------------
# Stage-1: Builder
#-------------------------------------------------------
FROM ubuntu:focal AS builder

RUN apt-get update -y

# Installing apache in non-interactive mode
ARG DEBIAN_FRONTEND=noninteractive

# Installing apache web server
RUN apt-get install apache2 -y

# Installing PHP v 8.2
RUN apt-get -y install software-properties-common && \
    add-apt-repository ppa:ondrej/php && \
    apt-get update && \
    apt-get -y install php8.2

# Installing required PHP extensions
RUN apt-get install -y php8.2-bcmath php8.2-fpm php8.2-xml php8.2-mysql php8.2-zip php8.2-intl php8.2-ldap php8.2-gd php8.2-cli php8.2-bz2 php8.2-curl php8.2-mbstring php8.2-pgsql php8.2-opcache php8.2-soap php8.2-cgi

# Installing MySQL
RUN apt-get update -qq && apt-get install -y mysql-server

# Installing curl & nano
RUN apt-get install -y curl nano

# Installing composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN chmod +x /usr/local/bin/composer

# Set public folder as default folder
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

WORKDIR /temp

COPY ./ByteQuest/composer.json .
COPY ./ByteQuest/composer.lock .

# Install production dependencies (ignores dev dependencies)
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

#-------------------------------------------------------
# Stage-2: Production
#-------------------------------------------------------
FROM builder AS production 

WORKDIR /var/www/html

COPY ./ByteQuest .

# Copy vendor folder from builder stage
COPY --from=builder /temp/vendor /var/www/html/vendor

# Override apache2.conf
ADD apache2.conf /etc/apache2/

RUN chmod 777 -R /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80 3306

# Add bash script
ADD start.sh /

# Give execute permission to bash script
RUN chmod +x /start.sh

CMD ["/usr/bin/bash", "/start.sh"]