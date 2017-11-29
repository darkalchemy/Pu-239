<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
dbconn();
global $CURUSER, $site_config, $cache;

if (!$CURUSER) {
    get_template();
}
$lang = array_merge(load_language('global'), load_language('confirm'));
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$md5 = isset($_GET['secret']) ? $_GET['secret'] : '';
if (!is_valid_id($id)) {
    stderr("{$lang['confirm_user_error']}", "{$lang['confirm_invalid_id']}");
}
if (!preg_match("/^(?:[\d\w]){32}$/", $md5)) {
    stderr("{$lang['confirm_user_error']}", "{$lang['confirm_invalid_key']}");
}
$res = sql_query('SELECT passhash, editsecret, status FROM users WHERE id =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res);
if (!$row) {
    stderr("{$lang['confirm_user_error']}", "{$lang['confirm_invalid_id']}");
}
if ($row['status'] != 'pending') {
    header("Refresh: 0; url={$site_config['baseurl']}/ok.php?type=confirmed");
    exit();
}
$sec = $row['editsecret'];
if ($md5 != $sec) {
    stderr("{$lang['confirm_user_error']}", "{$lang['confirm_cannot_confirm']}");
}
sql_query("UPDATE users SET status = 'confirmed', editsecret = '' WHERE id = " . sqlesc($id) . " AND status = 'pending'") or sqlerr(__FILE__, __LINE__);
$cache->update_row('MyUser_' . $id, [
    'status' => 'confirmed',
], $site_config['expires']['curuser']);
$cache->update_row('user' . $id, [
    'status' => 'confirmed',
], $site_config['expires']['user_cache']);
if (!mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
    stderr("{$lang['confirm_user_error']}", "{$lang['confirm_cannot_confirm']}");
}
header("Refresh: 0; url={$site_config['baseurl']}/ok.php?type=confirm");
