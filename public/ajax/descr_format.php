<?php

declare(strict_types = 1);

use Pu239\Torrent;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_bbcode.php';
check_user_status();
header('content-type: application/json');
global $container;

$tid = (int) $_POST['tid'];
if (!empty($tid)) {
    $torrents_class = $container->get(Torrent::class);
    $descr = $torrents_class->format_descr($tid);
    if (!empty($descr)) {
        echo json_encode(['descr' => $descr]);
        die();
    }
}
echo json_encode([
    'fail' => 'invalid',
]);
die();
