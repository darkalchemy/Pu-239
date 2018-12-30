<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';

$time_start = microtime(true);
global $site_config, $fluent, $torrent_stuffs;

$torrents = $fluent->from('torrents')
    ->select(null)
    ->select('id')
    ->select('info_hash')
    ->select('owner')
    ->orderBy('id');

$i = 0;
foreach ($torrents as $torrent) {
    $torrent_stuffs->delete_by_id($torrent['id']);
    $torrent_stuffs->remove_torrent($torrent['info_hash'], $torrent['id'], $torrent['owner']);
    ++$i;
}

$time_end = microtime(true);
$run_time = $time_end - $time_start;
echo "$i torrents deleted. Run time: $run_time seconds\n";
