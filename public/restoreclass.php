<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
global $CURUSER, $site_config, $fluent, $user_stuffs;

$set = [
    'override_class' => 255,
];
$user_stuffs->update($set, $CURUSER['id']);
$fluent->deleteFrom('ajax_chat_online')
    ->where('userID = ?', $CURUSER['id'])
    ->execute();

header("Location: {$site_config['baseurl']}/index.php");
die();
