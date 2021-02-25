# Pu-239 v0.7.0

##### I am no longer developing this code. But, if there are enough people willing to contribute their time and energy to help with this source, I would be willing to get active again.  

![GitHub commits since tagged version](https://img.shields.io/github/commits-since/darkalchemy/Pu-239/0.7.0)
[![GitHub license](https://img.shields.io/github/license/darkalchemy/Pu-239.svg)](https://github.com/darkalchemy/Pu-239/blob/master/LICENSE)
[![Commitizen friendly](https://img.shields.io/badge/commitizen-friendly-brightgreen.svg)](http://commitizen.github.io/cz-cli/)

This is a torrent tracker written in PHP. Also included is a realtime chat(AJAX Chat), Private Messaging System, Message Boards(Forums), Arcade, Lottery and Casino.  
The primary goal of this project is to give the site owner a means to create a community around sharing torrents, with the hopes of encouraging and engaging the userbase to participate in the discussion.  

## Table of Contents
* [Goals](#goals)
* [Before Installing](#prior-to-install)
* [Installing](#to-install)
* [Updating](#to-update)
* [User Roles](#user-roles)
* [Making Changes](#making-changes)
* [API's](#apis)
* [Production Mode](#production-mode)
* [Cache Engines](#cache-engines)  
* [Image Proxy](#image-proxy)  
* [CLI Scripts](#cli-scripts)
* [Notes](#notes) 
* [Translations](#translations)
* [Credits](#credits)  
* [Patrons](#patrons)
* [Wiki](https://github.com/darkalchemy/Pu-239/wiki)

##### I am using Ubuntu 20.04 LTS, PHP 7.4, Percona MySQL 8.0, nginx 1.18.0 for developing this code. You may need to adjust the instructions below to fit you current server setup. 

### Goals:
1. Update to PHP 7.4 - default settings
2. Error free with MySQL 8.0 strict mode - default settings - Mostly Done
3. Remove merged bootstrap
4. Update jquery
5. Update all javascript files to remove jquery dependency
6. Merge, minify and gzip css/js files to reduce size and requests(not as important if http2 is enabled)
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
PHP 7.3+ is required.  
MySQL 5.6 is required. MySQL 8.0 recommended.  
[Composer](https://getcomposer.org/download/) is required. Version ^2.0.8.  
[NPM/NPX](https://nodejs.org/en/download/package-manager/) is required and comes with nodejs. Version ^6.14.10.  
This code explicitly sets the php default timezone to 'UTC'. Further down, you will set MySQL default timezone to the same. It is very important that PHP and MySQL be set to the same time, else your site will display incorrect times to your users.  
A simple bash script to install everything required to host Pu-239 is [here](https://github.com/darkalchemy/Pu-239-Installer) and can be used to jump start the installation process.     
A simple php script to upload to Pu-239 is [here](https://github.com/darkalchemy/Pu-239-Uploader).  
A quick site intro video is available [here](https://www.youtube.com/watch?v=LyWp1dBs4cw&feature=youtu.be). (Outdated)  
If you like this project, please consider supporting me on [Patreon](https://www.patreon.com/user?u=15795177)  
  
#### Prior to install:
##### Please log in as a non-privileged user, NOT root, to install this. Please read this entire document before installing.
```
# required apps
jpegoptim, optipng, pngquant, gifsicle, imagemagick

# required php extensions
php-gd, php-xml, php-json, php-mbstring, php-mysqli, php-zip, php-simplexml, php-curl, php-exif, php-bz2, php-imagick, php-common, php-readline, php-gettext, php-intl

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

# install dependencies
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
innodb_autoinc_lock_mode = 0

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

# compare config/config.php with config/config_example.php for changes
# check CHANGELOG for anything that needs to be done first
# check to see if there are any database updates, from the staff panel or php bin/update_db.php
# personally, I just run 'php bin/update_db.php complete' and it runs all of the queries, until complete or 1 fails
php bin/update_db.php complete

# update dependencies:
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

#### User Roles:
 * Coder : Has access to the site, very similar to that of a Sysop
 * Forum Mod : Can moderate forum posts
 * Torrent Mod : Can moderate torrents and their descriptions
 * Internal : Required to post to the Cooker
 * Uploader : Required to upload to the site

#### Making Changes:
After updating composer, npm, changing anything inside the config or app folder, changing anything inside the staffpanel, you must delete the php-di cache. If you have set PRODUCTION = true.    
```sudo rm -rf /dev/shm/php-di```

#### Making Changes to css/js files:
Make any edits or changes to the files in templates and scripts folder, then to concatenate, minify and gzip the files for use, run:  
```php bin/uglify.php```
 
#### API's:
Fanart.tv API provides posters, backgrounds and banners. A [Project API Key](https://fanart.tv/get-an-api-key) is required.  
TMDb API allows upcoming movies and posters. An [API Key](https://developers.themoviedb.org/3/getting-started/introduction) is required.  
Google API allows up to 1000 api hits instead of 100 per day. An [API Key](https://cloud.google.com/docs/authentication/api-keys?visit_id=637040083471322830-1883548699&rd=1) is optional.  
IMDb API allow movies and tv lookup. No API Key necessary.  
TVMaze allows tv lookup and posters. No API Key necessary.  
API keys are set in the Staff Panel -> Site Settings.  

#### Production Mode:
Production creates minified javascript and css files when running uglify.php.  
After changing the setting 'PRODUCTION', you will need to run ```php bin/uglify.php``` to concatenate, minify and gzip the files for use.  
```config/define.php define('PRODUCTION', false);```  
This also creates a cache for php-di, significantly improving its performance.

#### Cache Engines:
memory, couchbase, apcu, memcached, redis or file. 'memory' is set as the default and is set in the config.php file. memory cache is only for testing and is not a real cache as it expires at the end of the request. Trivia will not run while using the memory cache. In order to use any cache engine besides 'file' and 'memory', you must first install the appropriate driver and php extensions.

#### Image Proxy:
An image proxy for hot linked images is built in and enabled by default, disable/enable in Staff Panel -> Site Settings. This allows for browser image caching and keeps from breaking https security with http images.  
```$site_config['site']['image_proxy'] = true;```

#### CLI Scripts:
  * clear_cache.php : clears the entire cache that is currently in use
  * import_tables.php : can import any table listed as an argument or imports trivia and tvmaze by default
  * install.php : installs/re-installs the site
  * jobby.php : runs all of the sites cleanup scripts through cron
  * optimize_resize_images.php : creates an optimized version and multiple sizes of each image in the images table, this is done automatically during cleanup
  * remove_altered_images.php : removes every image that is not in the images table
  * remove_torrents.php : removes all torrents, truncates tables and removes all traces of all torrents
  * set_perms.php : ensures all files have correct the user:owner and permissions set, also removes the DI_CACHE_DIR directory
  * uglify.php : generates the needed js/css files seen in public/js and public/css, also removes the DI_CACHE_DIR directory
  * update_db.php : updates the database to the current schema 
  * usersfix.php : adds users to userblocks and usersachiev tables, usually not needed
  * validate_images.php : verifies the images in public/images/proxy/ are valid images, removes those that may be invalid
  * localize.sh : create and update locale files

#### Notes: 
If sudo is necessary to run uglify.php without errors, then you have the permissions set incorrectly. See the wiki for a brief example.

#### IP Addresses
With the exception of the peers table, this project does not store the IP address of any user in the database. In addition, the users IP is not stored by default, the site administrator must manually change this setting to store IP addresses using memcached.  
In time, the peers table will also be replaced with memcached, so that no IP will stored in the database.  

#### Translations:
This project uses gettext to manage text strings. Unfortunately, it is not yet 100%. There are still quite a few hard coded strings left here and there.  
If you would like to see a specific translation or assist with a current translation, please join us at [Transifex](https://www.transifex.com/pu-239/).  
[Transifex](https://www.transifex.com/pu-239/) was kind enough to provide this project with a free open source license.

#### Credits:  
All Credit goes to the original code creators of U-232, tbdev, etc. Without them, this would not be possible.

#### Patrons:
Nico, Ben9, superlarsen, suiziide, RememberForgottenHits
