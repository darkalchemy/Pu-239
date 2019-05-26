<?php

declare(strict_types = 1);

use Pu239\Torrent;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_bbcode.php';
extract($_POST);
header('content-type: application/json');
global $container;

$tid = is_numeric($tid) ? (int) $tid : '';
if (!empty($tid)) {
    $torrent_stuffs = $container->get(Torrent::class);
    $descr = $torrent_stuffs->format_descr($tid);
    if (!empty($descr)) {
        echo json_encode(['descr' => $descr]);
        die();
    }
}
echo json_encode([
    'fail' => 'invalid',
]);
die();
