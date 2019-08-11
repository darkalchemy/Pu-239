<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Torrent;

require_once __DIR__ . '/../include/bittorrent.php';
global $container;

$time_start = microtime(true);
$fluent = $container->get(Database::class);
$torrents = $fluent->from('torrents')
                   ->select(null)
                   ->select('id')
                   ->select('info_hash')
                   ->select('owner')
                   ->orderBy('id');

$i = 0;
$torrents_class = $container->get(Torrent::class);
foreach ($torrents as $torrent) {
    $torrents_class->delete_by_id($torrent['id']);
    $torrents_class->remove_torrent($torrent['info_hash'], $torrent['id'], $torrent['owner']);
    ++$i;
}
$pdo = $container->get(PDO::class);
$query = 'SET FOREIGN_KEY_CHECKS = 0';
$pdo->exec($query);
$query = 'TRUNCATE `torrents`';
$pdo->exec($query);
$query = 'TRUNCATE `snatched`';
$pdo->exec($query);
$query = 'TRUNCATE `files`';
$pdo->exec($query);
$query = 'SET FOREIGN_KEY_CHECKS = 1';
$pdo->exec($query);

$time_end = microtime(true);
$run_time = $time_end - $time_start;
echo "$i torrents deleted. Run time: $run_time seconds\n";
