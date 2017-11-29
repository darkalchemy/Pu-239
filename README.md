# Pu-239

## Goals:
1. Updated to PHP7 - default settings
2. Error free with MySQL 5.7 strict mode - default settings
3. Remove merged bootstrap
4. Update jquery
5. Update all javascript files
6. Merge, mininify and gzip css/js files to reduce the number of requests
6. Optimize all images for web
7. Remove js from head and relocate to body
8. Fully responsive and mobile ready

There is a temporary site at http://pu-239.pw where you can view the code [master branch] in action.

There is a temporary site at http://dev.pu-239.pw where you can view the code [dev branch] in action.

This is a fork of U-232 V4.

PHP 7.0+ is required, PHP 7.1 recommended.

This is still a WIP and many pages may not be functional in there current location. Do not use the xbt install, as it's update has not been started and is likely broken.

```
get the files
git clone https://github.com/darkalchemy/Pu-239.git

set ownership
chown -R www-data:www-data Pu-239

install dependancies
cd Pu-239
composer install

set webroot to path Pu-239/public

goto website and complete install

delete public/install folder once directed to

create your first user and login

goto admin and create your bot/system user

goto admin cleanup and activate/deactivate scripts, they are initially set to yesterday midnight

keeping AJAX Chat open after first installing will allow the cleanup to catchup
```


credits:

All Credit goes to the original code creators.

