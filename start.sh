#!/bin/bash

/etc/init.d/mysql start

# Create Database
mysql -vv -se "CREATE DATABASE bytequest;"

# Create new user
# mysql -vv -se "CREATE USER newuser@localhost IDENTIFIED BY '';"
# mysql -vv -se "GRANT ALL PRIVILEGES ON *.* TO 'newuser'@'localhost';"

# Change root password for using default user for laravel without creating new user
mysql <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
FLUSH PRIVILEGES;
EOF

# Enable rewrite module
a2enmod rewrite

# Run database migrations and other Artisan commands
php /var/www/html/artisan migrate:fresh --seed
php /var/www/html/artisan cache:clear
php /var/www/html/artisan config:cache
php /var/www/html/artisan optimize:clear

# Run apache server in foreground
apachectl -D FOREGROUND