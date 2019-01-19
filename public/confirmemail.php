<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_users.php';
global $site_config, $fluent, $cache, $session;

$lang = array_merge(load_language('global'), load_language('confirmemail'));
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
    ->where('created_at > DATE_SUB(NOW(), INTERVAL 120 MINUTE)')
    ->fetch();

if (!password_verify($token, $row['token'])) {
    stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_no_id']}");
    die();
}

if ($row['status'] != 'confirmed') {
    stderr("{$lang['confirmmail_user_error']}", 'Your account is not active');
    die();
}

$passed = $fluent->update('users')
    ->set(['email' => $row['new_email']])
    ->where('email = ?', $row['email'])
    ->execute();

if ($passed) {
    $fluent->deleteFrom('tokens')
        ->where('id = ?', $id)
        ->execute();
} else {
    stderr("{$lang['confirmmail_user_error']}", "{$lang['confirmmail_not_complete']}");
}

$cache->update_row('user' . $row['user_id'], [
    'email' => $row['new_email'],
], $site_config['expires']['user_cache']);
$session->set('is-success', "[h2]Your email has been updated to {$row['email']}[/h2]");
header("Refresh: 0; url={$site_config['baseurl']}/usercp.php?action=security");
