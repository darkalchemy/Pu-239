<?php

require_once __DIR__ . '/../include/bittorrent.php';
check_user_status();
global $CURUSER, $session, $fluent;

$fluent->deleteFrom('ajax_chat_online')
    ->where('userID = ?', $CURUSER['id'])
    ->execute();
$session->destroy();
