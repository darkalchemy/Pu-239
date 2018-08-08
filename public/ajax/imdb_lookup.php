<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'function_imdb.php';
require_once INCL_DIR . 'function_get_images.php';
check_user_status();
global $CURUSER, $site_config, $fluent, $cache;

extract($_POST);

header('content-type: application/json');
if (!$session->validateToken($csrf)) {
    echo json_encode(['fail' => 'csrf']);
    die();
}

if (!empty($url)) {
    preg_match('/^https?\:\/\/(.*?)imdb\.com\/title\/(tt[\d]{7})/i', $url, $imdb);
    $imdb = !empty($imdb[2]) ? $imdb[2] : '';
    $movie_info = get_imdb_info($imdb);
    $poster = get_image_by_id('movie', null, $imdb, 'movieposter', null, false);
    $banner = get_image_by_id('movie', null, $imdb, 'moviebanner', null, false);
    $background = get_image_by_id('movie', null, $imdb, 'moviebackground', null, false);
    if (!empty($movie_info[1])) {
        url_proxy($movie_info[1], true, 150);
        url_proxy($movie_info[1], true, 150, null, 10);
    }
    if (!empty($poster)) {
        url_proxy($poster, true, 150);
        url_proxy($poster, true, 150, null, 10);
    }
    if (!empty($banner)) {
        url_proxy($banner, true, 1000, 185);
    }
    if (!empty($background)) {
        url_proxy($background, true);
    }

    if (!empty($movie_info)) {
        echo json_encode([
            'content' => $movie_info[0],
            'poster1' => $movie_info[1],
            'poster2' => $poster,
            'banner' => $banner,
            'background' => $background,
        ]);
        die();
    } else {
        echo json_encode([
            'fail' => 'invalid',
        ]);
        die();
    }
}
