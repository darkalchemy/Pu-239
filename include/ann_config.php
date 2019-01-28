<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'define.php';
require_once CONFIG_DIR . 'site.php';
require_once INCL_DIR . 'function_common.php';
require_once CONFIG_DIR . 'main.php';
require_once VENDOR_DIR . 'autoload.php';

$dotenv = new Dotenv\Dotenv(ROOT_DIR);
$dotenv->load();

$cache = new Pu239\Cache();
$fluent = new Pu239\Database();
$session = new Pu239\Session();
require_once INCL_DIR . 'site_settings.php';
$ip_stuffs = new Pu239\IP();
$peer_stuffs = new Pu239\Peer();
$event_stuffs = new Pu239\Event();
$snatched_stuffs = new Pu239\Snatched();
$torrent_stuffs = new Pu239\Torrent();
$user_stuffs = new Pu239\User();

require_once CLASS_DIR . 'class_bt_options.php';
require_once CACHE_DIR . 'class_config.php';
require_once INCL_DIR . 'database.php';

$agent = $_SERVER['HTTP_USER_AGENT'];
