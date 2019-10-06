<?php

declare(strict_types = 1);

use Pu239\Session;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
$user = check_user_status();
global $container, $site_config;

$fluent = $container->get(\Pu239\Database::class);
$invitedcount = $fluent->from('users')
                       ->select(null)
                       ->select('COUNT(id) AS count')
                       ->where('status = 0')
                       ->where('invitedby = ?', $user['id'])
                       ->fetch('count');

$usersachiev = $container->get(\Pu239\Usersachiev::class);
$update = [
    'invited' => $invitedcount,
];
$usersachiev->update($update, $user['id']);
$session = $container->get(Session::class);
$session->set('is-success', _fe('Your invited count has been updated! [{0}]', $invitedcount));
header("Location: {$site_config['paths']['baseurl']}/achievementhistory.php?id={$user['id']}");
