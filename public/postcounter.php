<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Session;
use Pu239\Usersachiev;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
check_user_status();
global $container, $CURUSER, $site_config;

$fluent = $container->get(Database::class);
$count = $fluent->from('posts')
    ->select(null)
    ->select('COUNT(id) AS count')
    ->where('user_id = ?', $CURUSER['id'])
    ->fetch('count');
$achieve = $container->get(Usersachiev::class);
$update = [
    'forumposts' => $count,
];
$achieve->update($update, $CURUSER['id']);
$session = $container->get(Session::class);
$session->set('is-success', "Your forum posts count has been updated! [{$count}]");
header("Location: {$site_config['paths']['baseurl']}/achievementhistory.php?id={$CURUSER['id']}");
