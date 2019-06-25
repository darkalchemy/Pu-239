<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Userblock;
use Pu239\Usersachiev;

require_once __DIR__ . '/../include/bittorrent.php';

global $container;

$fluent = $container->get(Database::class);
$users = $fluent->from('users')
    ->select(null)
    ->select('id')
    ->fetchAll();

$achieve = $container->get(Usersachiev::class);
$userblock = $container->get(Userblock::class);
foreach ($users as $user) {
    $achieve->add(['userid' => $user['id']]);
    $userblock->add(['userid' => $user['id']]);
}
