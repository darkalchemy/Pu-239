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
[Composer](https://getcomposer.org/download/) is required. Version ^1.8.0.  
[NPM](https://nodejs.org/en/download/package-manager/) is required. Version ^6.5.0.  
This code explicitly sets the php default timezone to 'UTC'. Further down, you will set MySQL default timezone to the same.  
A working site with this code is at [Pu-239](https://pu-239.pw/)   

A simple bash script to install everything required to host Pu-239 is [here](https://github.com/darkalchemy/Pu-239-Installer) and can be used to jumpstart the installation process.  

A simple php script to upload to Pu-239 is [here](https://github.com/darkalchemy/Pu-239-Uploader).

A quick site intro video is available [here](https://www.youtube.com/watch?v=LyWp1dBs4cw&feature=youtu.be).

If you like this project, please consider supporting me on [Patreon](https://www.patreon.com/user?u=15795177) 

#### Please log in as a non-privileged user, NOT root, to install this.  
### Prior to install:
```
# required apps
jpegoptim, optipng, pngquant, gifsicle, imagemagick

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
Adminer is included, only user #1 has access, others can be added, by id
```
### To Install:
```
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
innodb_file_format = Barracuda # Percona removed this in 8.0, MariaDB deprecated this in 10.2 and removed in 10.3
innodb_large_prefix = 1 # Percona removed this in 8.0, MariaDB deprecated this in 10.2 and removed in 10.3
innodb_file_per_table = 1

# add/modify this in [mysqld] to increase payload capacity
max_allowed_packet = 64M ## this may need to be increased as your user count increases

# add/modify this in [mysqld] to stop autoincrement on insert ignore(optional)
innodb_autoinc_lock_mode = 0

# set timezone to UTC to match PHP
default_time_zone='+00:00'

# to enable fulltext searches of 3 character words
innodb_ft_min_token_size=3

# restart mysql for changes to take effect
sudo service mysql restart

# create database
CREATE DATABASE Pu-239;

# set ownership
sudo chown -R www-data:www-data ../Pu-239

# install
php bin/install.php install

# set permissions and create necessary files
[sudo] php bin/set_perms.php
php bin/uglify.php 

# goto admin cleanup and activate/deactivate scripts, they are initially enabled and set to last midnight

# add cron job to root cron for running cleanup, please change path as needed
sudo crontab -e

### No logging
# runs cron_controller.php every minute, if not already running, as user www-data
* * * * * su www-data -s /bin/bash -c "/usr/bin/php /var/www/Pu-239/include/cron_controller.php" >/dev/null 2>&1

# this can take several minutes to run, especially the first time, so we run it separate
# runs images_update.php every 30 minutes, if not already running, as user www-data
*/30 * * * * su www-data -s /bin/bash -c "/usr/bin/php /var/www/Pu-239/include/images_update.php" >/dev/null 2>&1

### logging
# runs cron_controller.php every minute, if not already running, as user www-data
* * * * * su www-data -s /bin/bash -c "/usr/bin/php /var/www/Pu-239/include/cron_controller.php" >> /var/log/nginx/cron_`date +\%Y\%m\%d`.log 2>&1

# this can take several minutes to run, especially the first time, so we run it separate
# runs images_update.php every 30 minutes, if not already running, as user www-data
*/30 * * * * su www-data -s /bin/bash -c "/usr/bin/php /var/www/Pu-239/include/images_update.php" >> /var/log/nginx/images_`date +\%Y\%m\%d`.log 2>&1

# import additional tables
php bin/import_tables.php
```

### To Update:
```
# get the files
# how you do this step will depend how you did it initially, I personally use rsync to overwrite files from git to my webpath, then remove the install folder
cd Pu-239
git pull

# check to see if there are any database updates, from the staff panel

# update dependancies:
composer install
composer dump-autoload -o
npm install
[sudo] php bin/set_perms.php
php bin/uglify.php

# update additional tables          
php bin/import_tables.php

# occasionally you may need to remove bad images
php bin/validate_images.php
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
php bin/uglify.php
```

### Adding, removing changing classes   
Make any changes, then run php bin/uglify.php to concatenate, minify and gzip the files for use.

### Cache Engines  
couchbase, apcu, memcached, redis or file. file is set as default set in .env. In order to use any cache engin besides 'file', you must first install the appropriate driver and php extensions.

### Image Proxy:  
An image proxy for hot linked images is built in, enabled by default, disable/enable in staff panel => site settings, this allows for browser image caching and images with http when site is https

### Notes: 
If sudo is necessary to run uglify.php without errors, then you have the permissions set incorrectly. See the wiki for a brief example.

### Credits:  
All Credit goes to the original code creators of U-232, tbdev, etc. Without them, this would not be possible.

### Patrons
Nico
