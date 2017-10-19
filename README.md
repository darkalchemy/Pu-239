# Pu-239

There is a temporary site at http://pu-239.pw:8080 where you can view the code in action.

```
Running on:
Linux Aspire-XC-603G 4.10.0-27-generic #30~16.04.2-Ubuntu SMP Thu Jun 29 16:07:46 UTC 2017 x86_64 x86_64 x86_64 GNU/Linux
4 x Intel(R) Pentium(R) CPU  J2900  @ 2.41GHz
MemTotal:        7989868 kB
MemFree:          190100 kB
MemAvailable:    2103024 kB
```

This is a fork of U-232 V4.

PHP 7.0+ is required, PHP 7.1 recommended.

Be aware that the users table is deleted every few days. This is still a WIP and many pages may not be functional in there current location. Do not use the xbt install, as it's update has not been started and is likely broken.

```
get the files
git clone https://github.com/darkalchemy/Pu-239.git

set ownership
chown -R www-data:www-data Pu-239

goto website and complete install

delete /install folder once directed to

create your first user and login

goto admin and create your bot/system user

goto admin cleanup and activate/deactivate scripts, they are initially set to yesterday midnight

keeping AJAX Chat open after first installing will allow the cleanup to catchup
```


credits:

All Credit goes to the original code creators.
