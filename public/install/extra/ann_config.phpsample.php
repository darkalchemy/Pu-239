<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'define.php';
require_once VENDOR_DIR . 'autoload.php';
$dotenv = new Dotenv\Dotenv(dirname(__FILE__, 2) . DIRECTORY_SEPARATOR);
$dotenv->load();

$finished = $plist = $corupptthis = '';
$agent = $_SERVER['HTTP_USER_AGENT'];
$detectedclient = $_SERVER['HTTP_USER_AGENT'];
$site_config['Cache'] = ROOT_DIR . 'Cache';
require_once CLASS_DIR . 'class_bt_options.php';
$site_config['baseurl'] = get_scheme() . '://' . $_SERVER['HTTP_HOST'];
$site_config['pic_baseurl'] = $site_config['baseurl'] . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
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

$site_config['crazy_hour'] = false;
$site_config['happy_hour'] = false;
$site_config['ratio_free'] = false;

$site_config['expires']['user_passkey'] = 3600 * 8;
$site_config['expires']['contribution'] = 3 * 86400;
$site_config['expires']['happyhour'] = 43200;
$site_config['expires']['sitepot'] = 86400;
$site_config['expires']['torrent_announce'] = 86400;
$site_config['expires']['torrent_details'] = 2591999;
$site_config['expires']['user_cache'] = 2591999;

require_once INCL_DIR . 'database.php';
$cache = new Cache();

/**
 * @return mixed
 */
function get_scheme()
{
    if (isset($_SERVER['REQUEST_SCHEME'])) {
        return $_SERVER['REQUEST_SCHEME'];
    }
    return null;
}
