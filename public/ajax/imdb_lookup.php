<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'function_imdb.php';
check_user_status();
global $CURUSER, $site_config, $fluent, $cache;

extract($_POST);

header('content-type: application/json');
if (!$session->validateToken($csrf)) {
    echo json_encode(['content' => 'csrf']);
    die();
}

if (!empty($url)) {
    preg_match('/^https?\:\/\/(.*?)imdb\.com\/title\/(tt[\d]{7})/i', $url, $imdb);
    $imdb = !empty($imdb[2]) ? $imdb[2] : '';
    $movie_info = get_imdb_info($imdb);
    if (!empty($movie_info)) {
        echo json_encode([
            'content' => $movie_info[0],
            'poster' => $movie_info[1],
        ]);
        die();
    }
}
