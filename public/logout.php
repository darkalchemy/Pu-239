<?php

declare(strict_types = 1);

use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
global $container, $site_config;

$user = $container->get(User::class);
$user->logout();
header("Location: {$site_config['paths']['baseurl']}/login.php");
