<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_tvmaze.php';
require_once INCL_DIR . 'function_get_images.php';
check_user_status();
global $session, $torrent_stuffs;

extract($_POST);

header('content-type: application/json');
if (empty($csrf) || !$session->validateToken($csrf)) {
    echo json_encode(['fail' => 'csrf']);
    die();
}

preg_match('/S(\d+)E(\d+)/i', $name, $match);
$episode = !empty($match[2]) ? $match[2] : 0;
$season = !empty($match[1]) ? $match[1] : 0;
$torrent = $torrent_stuffs->get('torrent_details_' . $tid);
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
