<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
check_user_status();
global $CURUSER, $site_config, $user_stuffs;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sid = isset($_GET['id']) ? (int) $_GET['id'] : 1;
    if ($sid > 0 && $sid != $CURUSER['stylesheet']) {
        $set = [
            'stylesheet' => $sid,
        ];
        $user_stuffs->update($set, $CURUSER['id']);
    }
}

$returnto = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $site_config['baseurl'];
header("Location: $returnto");
