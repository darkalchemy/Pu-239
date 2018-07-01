<?php

/**
This file was created on Jul 01 2018 17:02:40.
User Class Config.
**/

define('UC_USER', 0);
define('UC_MIN', 0);
define('UC_POWER_USER', 1);
define('UC_UPLOADER', 2);
define('UC_ENCODER', 3);
define('UC_TRUSTEE', 4);
define('UC_VIP', 5);
define('UC_MODERATOR', 6);
define('UC_STAFF', 6);
define('UC_ADMINISTRATOR', 7);
define('UC_SYSOP', 8);
define('UC_MAX', 8);



$class_names = [
  UC_USER => 'USER',
  UC_POWER_USER => 'POWER USER',
  UC_UPLOADER => 'UPLOADER',
  UC_ENCODER => 'ENCODER',
  UC_TRUSTEE => 'TRUSTEE',
  UC_VIP => 'VIP',
  UC_MODERATOR => 'MODERATOR',
  UC_ADMINISTRATOR => 'ADMINISTRATOR',
  UC_SYSOP => 'SYSOP'
];


$class_colors = [
  UC_USER => 'ffff00',
  UC_POWER_USER => 'f9a200',
  UC_UPLOADER => 'f75c02',
  UC_ENCODER => '00FFFF',
  UC_TRUSTEE => '008080',
  UC_VIP => '9400D3',
  UC_MODERATOR => '008000',
  UC_ADMINISTRATOR => '3399ff',
  UC_SYSOP => 'fe2e2e'
];


$class_images = [
  UC_USER => $site_config['pic_baseurl'] . 'class/user.gif',
  UC_POWER_USER => $site_config['pic_baseurl'] . 'class/power.gif',
  UC_UPLOADER => $site_config['pic_baseurl'] . 'class/uploader.gif',
  UC_ENCODER => $site_config['pic_baseurl'] . 'class/power.gif',
  UC_TRUSTEE => $site_config['pic_baseurl'] . 'class/power.gif',
  UC_VIP => $site_config['pic_baseurl'] . 'class/vip.gif',
  UC_MODERATOR => $site_config['pic_baseurl'] . 'class/moderator.gif',
  UC_ADMINISTRATOR => $site_config['pic_baseurl'] . 'class/administrator.gif',
  UC_SYSOP => $site_config['pic_baseurl'] . 'class/sysop.gif'
];