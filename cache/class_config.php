<?php

/**
 * This file was created on Jul 08 2018 12:08:57.
 * User Class Config.
 **/
define('UC_USER', 0);
define('UC_MIN', 0);
define('UC_POWER_USER', 1);
define('UC_VIP', 2);
define('UC_UPLOADER', 3);
define('UC_MODERATOR', 4);
define('UC_STAFF', 4);
define('UC_ADMINISTRATOR', 5);
define('UC_SYSOP', 6);
define('UC_MAX', 6);

$class_names = [
  UC_USER => 'USER',
  UC_POWER_USER => 'POWER USER',
  UC_VIP => 'VIP',
  UC_UPLOADER => 'UPLOADER',
  UC_MODERATOR => 'MODERATOR',
  UC_ADMINISTRATOR => 'ADMINISTRATOR',
  UC_SYSOP => 'SYSOP',
];

$class_colors = [
  UC_USER => '8e35ef',
  UC_POWER_USER => 'f9a200',
  UC_VIP => '009f00',
  UC_UPLOADER => '0000ff',
  UC_MODERATOR => 'fe2e2e',
  UC_ADMINISTRATOR => 'b000b0',
  UC_SYSOP => '61df00',
];

$class_images = [
  UC_USER => $site_config['pic_baseurl'] . 'class/user.gif',
  UC_POWER_USER => $site_config['pic_baseurl'] . 'class/power.gif',
  UC_VIP => $site_config['pic_baseurl'] . 'class/vip.gif',
  UC_UPLOADER => $site_config['pic_baseurl'] . 'class/uploader.gif',
  UC_MODERATOR => $site_config['pic_baseurl'] . 'class/moderator.gif',
  UC_ADMINISTRATOR => $site_config['pic_baseurl'] . 'class/administrator.gif',
  UC_SYSOP => $site_config['pic_baseurl'] . 'class/sysop.gif',
];
