<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
global $CURUSER;
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
dbconn();
$res = sql_query('SELECT passhash, editsecret, status FROM users WHERE id =' . sqlesc($id));
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
sql_query("UPDATE users SET status='confirmed', editsecret='' WHERE id=" . sqlesc($id) . " AND status='pending'");
$mc1->begin_transaction('MyUser_' . $id);
$mc1->update_row(false, [
    'status' => 'confirmed',
]);
$mc1->commit_transaction($site_config['expires']['curuser']);
$mc1->begin_transaction('user' . $id);
$mc1->update_row(false, [
    'status' => 'confirmed',
]);
$mc1->commit_transaction($site_config['expires']['user_cache']);
if (!mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
    stderr("{$lang['confirm_user_error']}", "{$lang['confirm_cannot_confirm']}");
}
header("Refresh: 0; url={$site_config['baseurl']}/ok.php?type=confirm");
