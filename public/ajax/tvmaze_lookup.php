<?php

declare(strict_types = 1);

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_tvmaze.php';
require_once INCL_DIR . 'function_get_images.php';
check_user_status();
$tvmazeid = $tid = 0;
extract($_POST);

header('content-type: application/json');
global $container;

preg_match('/S(\d+)E(\d+)/i', $name, $match);
$episode = !empty($match[2]) ? $match[2] : 0;
$season = !empty($match[1]) ? $match[1] : 0;
$torrent = $torrent_stuffs->get($tid);
$poster = !empty($torrent['poster']) ? $torrent['poster'] : '';
if ($poster) {
    $poster = get_image_by_id('tv', $ids['thetvdb_id'], 'poster', $season, true);
}

$tvmaze_data = tvmaze($tvmazeid, $tid, $season, $episode, $poster);

if (!empty($tvmaze_data)) {
    echo json_encode([
        'content' => $tvmaze_data,
    ]);
    die();
}

echo json_encode(['content' => 'Lookup Failed.']);
die();
