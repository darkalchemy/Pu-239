<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'define.php';
require_once VENDOR_DIR . 'autoload.php';
$dotenv = new Dotenv\Dotenv(realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'));
$dotenv->load();
require_once INCL_DIR . 'database.php';
$cache = new CACHE();

error_reporting(E_ALL);
$finished = $plist = $corupptthis = '';
$agent = $_SERVER['HTTP_USER_AGENT'];
$detectedclient = $_SERVER['HTTP_USER_AGENT'];
$site_config['cache'] = ROOT_DIR . 'cache';
require_once CLASS_DIR . 'class_bt_options.php';
$site_config['pic_base_url'] = './pic/';
require_once CACHE_DIR . 'class_config.php';

const REQUIRED_PHP = 70100, REQUIRED_PHP_VERSION = '7.1.0';
if (version_compare(PHP_VERSION, REQUIRED_PHP_VERSION, '>=')) {
    date_default_timezone_set('UTC');
}
$site_config['announce_interval'] = 60 * 30;
$site_config['min_interval'] = 60 * 15;
$site_config['connectable_check'] = true;

$site_config['ann_sql_error_log'] = SQLERROR_LOGS_DIR . 'ann_sql_err_' . date('M_D_Y') . '.log';
$site_config['ann_sql_log'] = SQLERROR_LOGS_DIR . 'ann_sql_query_' . date('M_D_Y') . '.log';

$site_config['crazy_hour'] = false; //== false for XBT
$site_config['happy_hour'] = false; //== false for XBT
$site_config['ratio_free'] = false;

$site_config['baseurl'] = '#baseurl';
$site_config['expires']['user_passkey'] = 3600 * 8; // 8 hours
$site_config['expires']['contribution'] = 3 * 86400; // 3 * 86400 3 days
$site_config['expires']['happyhour'] = 43200; // 43200 1/2 day
$site_config['expires']['sitepot'] = 86400; // 86400 1 day
$site_config['expires']['torrent_announce'] = 86400; // 86400 1 day
$site_config['expires']['torrent_details'] = 30 * 86400; // = 30 days
