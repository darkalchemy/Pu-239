<?php

declare(strict_types = 1);

use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
$user = check_user_status();
global $container, $site_config;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sid = isset($_GET['id']) ? (int) $_GET['id'] : 1;
    if ($sid > 0 && $sid != $user['stylesheet']) {
        $set = [
            'stylesheet' => $sid,
        ];
        $users_class = $container->get(User::class);
        $users_class->update($set, $user['id']);
    }
}

$returnto = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $site_config['paths']['baseurl'];
header("Location: $returnto");
