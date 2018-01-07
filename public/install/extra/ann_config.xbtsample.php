<?php
error_reporting(E_ALL);

$finished = $plist = $corupptthis = '';
$agent = $_SERVER['HTTP_USER_AGENT'];
$detectedclient = $_SERVER['HTTP_USER_AGENT'];
$site_config['cache'] = ROOT_DIR.'cache';
require_once CLASS_DIR.'class_cache.php';
require_once CLASS_DIR.'class_bt_options.php';
$site_config['pic_base_url'] = './pic/';
require_once CACHE_DIR.'class_config.php';
require_once CACHE_DIR.'hit_and_run_settings.php';
if (version_compare(PHP_VERSION, '5.1.0RC1', '>=')) {
    date_default_timezone_set('Europe/London');
}
$mc1 = new CACHE();
$site_config['announce_interval'] = 60 * 30;
$site_config['min_interval'] = 60 * 15;
$site_config['connectable_check'] = 1;

$site_config['ann_sql_error_log'] = 'sqlerr_logs/ann_sql_err_'.date('M_D_Y').'.log';
$site_config['ann_sql_log'] = 'sqlerr_logs/ann_sql_query_'.date('M_D_Y').'.log';
$site_config['crazy_hour'] = false; //== Off for XBT
$site_config['happy_hour'] = false; //== Off for XBT
$site_config['ratio_free'] = false;
// DB setup
$site_config['baseurl'] = '#baseurl';
$site_config['mysql_host'] = '#mysql_host';
$site_config['mysql_user'] = '#mysql_user';
$site_config['mysql_pass'] = '#mysql_pass';
$site_config['mysql_db'] = '#mysql_db';
$site_config['expires']['user_passkey'] = 3600 * 8; // 8 hours
$site_config['expires']['contribution'] = 3 * 86400; // 3 * 86400 3 days
$site_config['expires']['happyhour'] = 43200; // 43200 1/2 day
$site_config['expires']['sitepot'] = 86400; // 86400 1 day
$site_config['expires']['torrent_announce'] = 86400; // 86400 1 day
$site_config['expires']['torrent_details'] = 30 * 86400; // = 30 days
