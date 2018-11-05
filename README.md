# Pu-239 v0.5

## Goals:
1. Updated to PHP 7.2 - default settings - Done
2. Error free with MySQL 5.7 strict mode - default settings - Mostly Done
3. Remove merged bootstrap - Done
4. Update jquery - Done
5. Update all javascript files to remove jquery dependency
6. Merge, mininify and gzip css/js files to reduce size and requests(not as important if http2 is enabled) - Done
7. Replace manual concat/gzip of css/js file with webpack
8. Optimize all images for web - Done
9. Remove js from head and relocate to body
10. Replace Simple Captcha with reCAPTCHA V3 - Done
11. Fully responsive and mobile ready
12. Drag and Drop Image Upload - Done
13. Use unix sockets for all local server connections - Done

This is a fork of U-232 V4.

PHP 7.2+ is required.

MySQL 5.6 is required. MySQL 5.7 recommended.

Composer is required. Version ^1.7.2.

NPM is required. Version ^6.4.1.

This code explicitly sets the php default timezone to 'UTC'. Further down, you will set MySQL default timezone to the same.

A working site with this code is at [Pu-239](https://pu-239.pw/)

### To Install:
```
# required apps
jpegoptim, optipng, pngquant, gifsicle

# required php extensions
php-gd, php-xml, php-json, php-mbstring, php-mysqli, php-zip, php-simplexml, php-curl, php-exif, php-bz2, php-imagick, php-common

# cache repositories(optional)
redis, php-redis
memchached, php-memcached
APCu
couchbase(untested)
file(FlySystem)

# data storage
MySQL or MariaDB or Percona MySQL

# get the files
git clone https://github.com/darkalchemy/Pu-239.git

# move into the install folder
cd Pu-239

# install dependancies
composer install
npm install

# set webroot to path Pu-239/public
an example Nginx Configuration: https://github.com/darkalchemy/Pu-239/wiki/NGINX-Config
 
# add charset to [mysqld] section of mysql.cnf
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# add/modify these in [mysqld] to increase max size for index(required)
innodb_file_format = Barracuda # MariaDB deprecated this in 10.2 and removed in 10.3
innodb_large_prefix = 1 # MariaDB deprecated this in 10.2 and removed in 10.3
innodb_file_per_table = 1

# add/modify this in [mysqld] to increase payload capacity
max_allowed_packet = 64M ## this may need to be increased as your user count increases

# add/modify this in [mysqld] to stop autoincrement on insert ignore(optional)
innodb_autoinc_lock_mode = 0

# set timezone to UTC to match PHP
default_time_zone='+00:00'

# restart mysql for changes to take effect
sudo service mysql restart

# create database
CREATE DATABASE Pu-239;

# set ownership
sudo chown -R www-data:www-data ../Pu-239

# goto website and complete install, all fields must be completed and each field includes an example and tooltip explanation when hovered

# set permissions and create necessary files
[sudo] php bin/set_perms.php
[sudo] php bin/uglify.php

# delete public/install folder once directed to
sudo rm -r public/install/

# create your first user and login

# goto admin and create your bot/system user

# goto admin cleanup and activate/deactivate scripts, they are initially set to last midnight

# add cron job to root cron for running cleanup, please change path as needed
sudo crontab -e

# runs cron_controller.php every minute, if not already running, as user www-data
* * * * * su www-data -s /bin/bash -c "/usr/bin/php /var/www/Pu-239/include/cron_controller.php" >/dev/null 2>&1

# this can take several minutes to run, especially the first time, so we run it separate
# runs images_update.php every 30 minutes, if not already running, as user www-data
*/30 * * * * su www-data -s /bin/bash -c "/usr/bin/php /var/www/Pu-239/include/images_update.php" >/dev/null 2>&1

# import trivia questions if desired
mysql database < database/trivia.php.sql

# import tvmaze ids
mysql database < database/tvmaze.php.sql

# import image urls
mysql database < database/images.php.sql
```

### To Update:
```
# get the files
# how you do this step will depend how you did it initially, I personally use rsync to overwrite files from git to my webpath, then remove the install folder
cd Pu-239
git pull

# update dependancies:
composer install
composer dump-autoload -o
npm install
[sudo] php bin/set_perms.php
[sudo] php bin/uglify.php

# update trivia questions if desired
mysql database < database/trivia.php.sql

# update tvmaze ids 
mysql database < database/tvmaze.php.sql

# update image urls 
mysql database < database/images.php.sql

# update database:
goto admin/upgrade_database to check/update the database
```

### API's

reCAPTCHA V3 needs both the site key and secret key set in .env.

Fanart.tv needs api key set in .env.

TMDb API key allows upcoming movies and many images.

OMDb API key allows movies and tv lookup.

Google API key allows up to 1000 api hits instead of 100 per day, set in .env.

IMDb no key needed, allow movies and tv lookup.

TVMaze no key needed, allows tv lookup.


### Making Changes to css/js files

Make any edits or changes to the files in templates and scripts folder, then to concatenate, minify and gzip the files for use, run:
```
[sudo] php bin/uglify.php
```

### Adding, removing changing classes

Make any changes, then run [sudo] php bin/uglify.php to concatenate, minify and gzip the files for use.

### Cache Engines

couchbase, apcu, memcached, redis or file. file is set as default set in .env. In order to use any cache engin besides 'file', you must first install the appropriate driver and php extensions.


### Image Proxy:

An image proxy for hot linked images is built in, enabled by default, disable/enable in staff panel => site settings, this allows for browser image caching and images with http when site is https


### Credits:

All Credit goes to the original code creators of U-232, tbdev, etc. Without them, this would not be possible.
