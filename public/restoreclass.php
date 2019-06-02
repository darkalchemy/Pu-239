<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
check_user_status();
global $container, $site_config, $CURUSER;
$set = [
    'override_class' => 255,
];
$user_stuffs = $container->get(User::class);
$user_stuffs->update($set, $CURUSER['id']);
$fluent = $container->get(Database::class);
$fluent->deleteFrom('ajax_chat_online')
       ->where('userID = ?', $CURUSER['id'])
       ->execute();

header("Location: {$site_config['paths']['baseurl']}");
die();
