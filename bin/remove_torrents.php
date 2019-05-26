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
$torrent_stuffs = $container->get(Torrent::class);
foreach ($torrents as $torrent) {
    $torrent_stuffs->delete_by_id($torrent['id']);
    $torrent_stuffs->remove_torrent($torrent['info_hash'], $torrent['id'], $torrent['owner']);
    ++$i;
}

$time_end = microtime(true);
$run_time = $time_end - $time_start;
echo "$i torrents deleted. Run time: $run_time seconds\n";
