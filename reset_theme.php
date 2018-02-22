<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
check_user_status();
$lang = load_language('global');
global $CURUSER, $site_config;

$cache = new DarkAlchemy\Pu239\Cache();

$sid = 1;
if ($sid > 0 && $sid != $CURUSER['id']) {
    sql_query('UPDATE users SET stylesheet = ' . sqlesc($sid) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
}
$cache->update_row('user' . $CURUSER['id'], [
    'stylesheet' => $sid,
], $site_config['expires']['user_cache']);
header("Location: {$site_config['baseurl']}/index.php");
