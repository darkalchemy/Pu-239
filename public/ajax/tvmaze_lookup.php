<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'function_tvmaze.php';
require_once INCL_DIR . 'function_get_images.php';
check_user_status();
global $cache, $session;

extract($_POST);

header('content-type: application/json');
if (!$session->validateToken($csrf)) {
    echo json_encode(['fail' => 'csrf']);
    die();
}

preg_match('/S(\d+)E(\d+)/i', $name, $match);
$episode = !empty($match[2]) ? $match[2] : 0;
$season = !empty($match[1]) ? $match[1] : 0;
$torrent = $cache->get('torrent_details_' . $tid);
$poster = !empty($torrent['poster']) ? $torrent['poster'] : '';

$tvmaze_data = tvmaze($tvmazeid, $tid, $season, $episode, $poster);
$ids = get_show_id($name);
if (!empty($ids['thetvdb_id'])) {
    $torrent = $cache->get('torrent_details_' . $tid);
    $poster = $banner = $background = '';
    if (empty($torrent['poster'])) {
        $poster = get_image_by_id('tv', $tid, $ids['thetvdb_id'], 'poster', $season, true);
    }
    if (empty($torrent['banner'])) {
        $banner = get_image_by_id('tv', $tid, $ids['thetvdb_id'], 'banner', $season, true);
    }
    if (empty($torrent['background'])) {
        $background = get_image_by_id('tv', $tid, $ids['thetvdb_id'], 'background', $season, true);
    }
}

if (!empty($tvmaze_data)) {
    echo json_encode([
        'content' => $tvmaze_data,
        'poster' => $poster,
        'banner' => $banner,
        'background' => $background,
    ]);
    die();
}

echo json_encode(['content' => 'Lookup Failed.']);
die();
