<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
global $CURUSER, $site_config, $fluent;

if (!$CURUSER) {
    get_template();
}

$lang = array_merge(load_language('global'), load_language('confirm'));
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$token = isset($_GET['token']) ? $_GET['token'] : '';
if (empty($id)) {
    stderr("{$lang['confirm_user_error']}", "{$lang['confirm_invalid_id']}");
}
if (!preg_match("/^(?:[\d\w]){60}$/", $token)) {
    stderr("{$lang['confirm_user_error']}", "{$lang['confirm_invalid_key']}");
}

$row = $fluent->from('tokens')
    ->select('users.status')
    ->select('users.id AS user_id')
    ->innerJoin('users ON users.email = tokens.email')
    ->where('tokens.id = ?', $id)
    ->where('created_at > DATE_SUB(NOW(), INTERVAL 60 MINUTE)')
    ->fetch();

if (!password_verify($token, $row['token'])) {
    stderr("{$lang['confirm_user_error']}", "{$lang['confirm_invalid_id']}");
    die();
}

if ($row['status'] != 'pending') {
    header("Refresh: 0; url={$site_config['baseurl']}/ok.php?type=confirmed");
    die();
}
$passed = $fluent->update('users')
    ->set(['status' => 'confirmed'])
    ->where('email = ?', $row['email'])
    ->where('status = ?', 'pending')
    ->execute();

$fluent->deleteFrom('tokens')
    ->where('id = ?', $id)
    ->execute();

if (!$passed) {
    stderr("{$lang['confirm_user_error']}", "{$lang['confirm_cannot_confirm']}");
}

setSessionVar('userID', $row['user_id']);
header("Refresh: 0; url={$site_config['baseurl']}/ok.php?type=confirm");
