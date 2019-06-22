<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
global $container, $site_config;

$user = $container->get(User::class);
$auth = $container->get(Auth::class);
$user->logout($auth->getUserId(), true);
header("Location: {$site_config['paths']['baseurl']}/login.php");
