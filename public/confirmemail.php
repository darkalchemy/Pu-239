<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
global $cache, $site_config;

$lang = array_merge(load_language('global'), load_language('confirmemail'));
if (!isset($_GET['uid']) or !isset($_GET['key']) or !isset($_GET['email'])) {
    stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_idiot']}");
}
if (!preg_match("/^(?:[\d\w]){32}$/", $_GET['key'])) {
    stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_no_key']}");
}
if (!preg_match("/^(?:\d){1,}$/", $_GET['uid'])) {
    stderr("{$lang['confirmmail_user-error']}", "{$lang['confirmmail_no_id']}");
}
$id = intval($_GET['uid']);
$md5 = $_GET['key'];
$email = urldecode($_GET['email']);
if (!validemail($email)) {
    stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_false_email']}");
}
dbconn();
$res = sql_query('SELECT editsecret FROM users WHERE id =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res);
if (!$row) {
    stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_not_complete']}");
}
$sec = $row['editsecret'];
if (preg_match('/^ *$/s', $sec)) {
    stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_not_complete']}");
}
if ($md5 != md5($sec . $email . $sec)) {
    stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_not_complete']}");
}
sql_query("UPDATE users SET editsecret = '', email = " . sqlesc($email) . ' WHERE id = ' . sqlesc($id) . ' AND editsecret = ' . sqlesc($row['editsecret'])) or sqlerr(__FILE__, __LINE__);
$cache->update_row('user' . $id, [
    'editsecret' => '',
    'email'      => $email,
], $site_config['expires']['user_cache']);
if (!mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
    stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_not_complete']}");
}
header("Refresh: 0; url={$site_config['baseurl']}/usercp.php?action=security&emailch=1");
