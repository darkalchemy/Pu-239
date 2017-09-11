<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
check_user_status();
$lang = array_merge(load_language('global'));
global $mc1, $site_config;
$sid = 1;
if ($sid > 0 && $sid != $CURUSER['id']) {
    sql_query('UPDATE users SET stylesheet=' . sqlesc($sid) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
}
$mc1->begin_transaction('MyUser_' . $CURUSER['id']);
$mc1->update_row(false, [
    'stylesheet' => $sid,
]);
$mc1->commit_transaction($site_config['expires']['curuser']);
$mc1->begin_transaction('user' . $CURUSER['id']);
$mc1->update_row(false, [
    'stylesheet' => $sid,
]);
$mc1->commit_transaction($site_config['expires']['user_cache']);
header("Location: {$site_config['baseurl']}/index.php");
