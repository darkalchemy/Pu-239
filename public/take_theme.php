<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
check_user_status();
global $site_config, $CURUSER;
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sid = isset($_GET['id']) ? (int) $_GET['id'] : 1;
    if ($sid > 0 && $sid != $CURUSER['stylesheet']) {
        $set = [
            'stylesheet' => $sid,
        ];
        $user_stuffs->update($set, $CURUSER['id']);
    }
}

$returnto = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $site_config['paths']['baseurl'];
header("Location: $returnto");
