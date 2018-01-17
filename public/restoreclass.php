<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache;

sql_query("UPDATE users SET override_class = '255' WHERE id = " . sqlesc($CURUSER['id']));
$cache->update_row('user' . $CURUSER['id'], [
    'override_class' => 255,
], $site_config['expires']['user_cache']);
header("Location: {$site_config['baseurl']}/index.php");
die();
