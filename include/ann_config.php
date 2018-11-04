<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'define.php';
require_once INCL_DIR . 'site_config.php';
require_once VENDOR_DIR . 'autoload.php';

$dotenv = new Dotenv\Dotenv(dirname(__FILE__, 2) . DIRECTORY_SEPARATOR);
$dotenv->load();

require_once INCL_DIR . 'site_settings.php';
require_once CLASS_DIR . 'class_bt_options.php';
require_once CACHE_DIR . 'class_config.php';
require_once INCL_DIR . 'database.php';
$cache = new DarkAlchemy\Pu239\Cache();
$fluent = new DarkAlchemy\Pu239\Database();
$ip_stuffs = new DarkAlchemy\Pu239\IP();
$peer_stuffs = new DarkAlchemy\Pu239\Peer();
$event_stuffs = new DarkAlchemy\Pu239\Event();
$snatched_stuffs = new DarkAlchemy\Pu239\Snatched();
$torrent_stuffs = new DarkAlchemy\Pu239\Torrent();
$user_stuffs = new DarkAlchemy\Pu239\User();

$agent = $_SERVER['HTTP_USER_AGENT'];

$hnr_settings = $cache->get('hnr_settings_');
if ($hnr_settings === false || is_null($hnr_settings)) {
    $sql = $fluent->from('hit_and_run_settings');

    foreach ($sql as $res) {
        $hnr_settings['hnr_config'][$res['name']] = $res['value'];
    }

    $cache->set('hnr_settings_', $hnr_settings, 86400);
}

$site_config = array_merge($site_config, $hnr_settings);
