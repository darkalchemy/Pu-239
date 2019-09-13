# Pu-239 v0.7

######I am using Ubuntu 18.04 LTS, PHP 7.3, Percona MySQL 8.0, nginx 1.14.2 for developing this code. You may need to adjust the instructions below to fit you current server setup. 

### Goals:
1. Update to PHP 7.3 - default settings
2. Error free with MySQL 8.0 strict mode - default settings - Mostly Done
3. Remove merged bootstrap
4. Update jquery
5. Update all javascript files to remove jquery dependency
6. Merge, mininify and gzip css/js files to reduce size and requests(not as important if http2 is enabled)
7. Replace manual concat/gzip of css/js file with uglifyjs
8. Optimize all images for web
9. Remove js from head and relocate to body - Mostly done
10. Remove Simple Captcha
11. Fully responsive and mobile ready
12. Drag and Drop Image Upload
13. Allow use of unix sockets for all local server connections
14. Proper validation of user input - In progress
15. Replace mysql with PDO/FluentPDO - In progress

This is a fork of U-232 V4.  
PHP 7.2+ is required.  
MySQL 5.6 is required. MySQL 8.0 recommended.  
[Composer](https://getcomposer.org/download/) is required. Version ^1.8.6.  
[NPM/NPX](https://nodejs.org/en/download/package-manager/) is required and comes with nodejs. Version ^6.11.3.  
This code explicitly sets the php default timezone to 'UTC'. Further down, you will set MySQL default timezone to the same. It is very important that PHP and MySQL be set to the same time, else your site will display incorrect times to your users.  
A simple bash script to install everything required to host Pu-239 is [here](https://github.com/darkalchemy/Pu-239-Installer) and can be used to jumpstart the installation process. (Not tested recently)     
A simple php script to upload to Pu-239 is [here](https://github.com/darkalchemy/Pu-239-Uploader).  (Not tested recently)  
A quick site intro video is available [here](https://www.youtube.com/watch?v=LyWp1dBs4cw&feature=youtu.be). (Outdated)  
If you like this project, please consider supporting me on [Patreon](https://www.patreon.com/user?u=15795177)  
There is a demo site available at [Pu-239](https://pu-239.pw:59595). It's a bit slow, but it's all I can do. :)  

##### Please log in as a non-privileged user, NOT root, to install this. Please read this entire document before installing.  
#### Prior to install:
```
# required apps
jpegoptim, optipng, pngquant, gifsicle, imagemagick

# required php extensions
php-gd, php-xml, php-json, php-mbstring, php-mysqli, php-zip, php-simplexml, php-curl, php-exif, php-bz2, php-imagick, php-common, php-readline

# cache repositories(optional)
redis, php-redis
memchached, php-memcached
APCu
couchbase(untested)
file(FlySystem)

# data storage
MySQL, MariaDB or Percona MySQL
Adminer is included, only user #1 has access, others can be added, by id
```
#### To Install:
```
# get the files
git clone https://github.com/darkalchemy/Pu-239.git

# move into the install folder
cd Pu-239

# install dependancies
composer install -a
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
max_allowed_packet = 128M ## this may need to be increased as your user count increases

# add/modify this in [mysqld] to stop autoincrement on insert ignore(optional)
innodb_autoinc_lock_mode = 3

# set timezone to UTC to match PHP
default_time_zone='+00:00'

# to enable fulltext searches of 3 character words
innodb_ft_min_token_size = 3

# restart mysql for changes to take effect
sudo service mysql restart

# create database
CREATE DATABASE Pu-239;

# set ownership
sudo chown -R $USER:www-data ../Pu-239

# install
php bin/install.php install

# set permissions and create necessary files
sudo php bin/set_perms.php
php bin/uglify.php 

# goto admin cleanup and activate/deactivate scripts, they are initially enabled and set to last midnight

# add cron job to root cron for running cleanup, please change path as needed
sudo crontab -e

# runs jobby.php every minute, if not already running
* * * * * cd /var/www/Pu-239/bin/ && /usr/bin/php jobby.php 1>> /dev/null 2>&1

# import additional tables
php bin/import_tables.php
```

#### To Update:
```
# get the files
# how you do this step will depend how you did it initially, I personally run in a git repository
cd Pu-239
git pull

# compare /config/config.php with /config/config_example.php for changes
# check CHANGELOG for anything that neds to be done first
# check to see if there are any database updates, from the staff panel or php bin/update_db.php

# update dependancies:
composer install (production mode add: --no-dev)
npm install
sudo rm -rf /dev/shm/php-di
sudo php bin/set_perms.php
php bin/uglify.php
sudo rm -rf /dev/shm/php-di

# update additional tables, if desired          
php bin/import_tables.php

# occasionally you may need to remove bad images
php bin/validate_images.php
```

#### Making Changes
After updating composer, npm, changing anything inside the config folder, changing anything inside the staffpanel, you must delete the php-di cache. If you have set PRODUCTION = true.    
```sudo rm -rf /dev/shm/php-di```
 
#### API's 
Fanart.tv API provides posters, backgrounds and banners and needs an api key.  
TMDb API allows upcoming movies and posters and needs an api key.  
Google API allows up to 1000 api hits instead of 100 per day, api key is optional.  
IMDb API allow movies and tv lookup, no key needed.  
TVMaze allows tv lookup and posters, no key needed.  
API keys are set in the Staff Panel -> Site Settings.  

#### Making Changes to css/js files  
Make any edits or changes to the files in templates and scripts folder, then to concatenate, minify and gzip the files for use, run:  
```php bin/uglify.php```

#### Production
Production creates minified javascript and css files when running uglify.php.  
After changing the setting 'production' you will need to run ```php bin/uglify.php``` to concatenate, minify and gzip the files for use.  
```config/define.php define('PRODUCTION', false);```


#### Cache Engines  
memory, couchbase, apcu, memcached, redis or file. 'memory' is set as the default and is set in the config.php file. memory cache is only for testing and is not a real cache as it expires at the end of the request. Trivia can not run while using the memory cache. In order to use any cache engine besides 'file' and 'memory', you must first install the appropriate driver and php extensions.

#### Image Proxy:  
An image proxy for hot linked images is built in and enabled by default, disable/enable in config/main.php. This allows for browser image caching and images with http when site is https.  
```$site_config['site']['image_proxy'] = true;```

#### Notes: 
If sudo is necessary to run uglify.php without errors, then you have the permissions set incorrectly. See the wiki for a brief example.

#### Credits:  
All Credit goes to the original code creators of U-232, tbdev, etc. Without them, this would not be possible.

#### Patrons
Nico, Ben9, superlarsen, suiziide
