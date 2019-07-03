<?php

declare(strict_types = 1);

use Pu239\Torrent;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_tvmaze.php';
require_once INCL_DIR . 'function_get_images.php';
check_user_status();
$tvmazeid = !empty($_POST['tvmazeid']) ? (int) strip_tags($_POST['tvmazeid']) : 0;
$tid = !empty($_POST['tid']) ? (int) strip_tags($_POST['tid']) : 0;
$name = !empty($_POST['name']) ? htmlsafechars($_POST['name']) : null;
header('content-type: application/json');

global $container;

preg_match('/S(\d+)E(\d+)/i', $name, $match);
$episode = !empty($match[2]) ? (int) $match[2] : 0;
$season = !empty($match[1]) ? (int) $match[1] : 0;
$torrents_class = $container->get(Torrent::class);
$poster = $torrents_class->get_items(['poster'], $tid);
if (empty($poster)) {
    $poster = get_image_by_id('tv', (string) $tvmazeid, 'poster', $season);
}
$poster = empty($poster) ? '' : $poster;
$tvmaze_data = tvmaze($tvmazeid, $tid, $season, $episode, $poster);
if (!empty($tvmaze_data)) {
    echo json_encode(['content' => $tvmaze_data]);
    die();
}
echo json_encode(['fail' => 'invalid']);
die();
