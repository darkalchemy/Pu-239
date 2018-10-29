<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'function_imdb.php';
require_once INCL_DIR . 'function_get_images.php';
require_once INCL_DIR . 'share_images.php';
check_user_status();
global $session;

extract($_POST);

header('content-type: application/json');
if (!$session->validateToken($csrf)) {
    echo json_encode(['fail' => 'csrf']);
    die();
}

$imdb = '';
if (!empty($url)) {
    preg_match('/(tt[\d]{7})/i', $url, $imdb);
    $imdb = !empty($imdb[1]) ? $imdb[1] : false;
}

if (!empty($imdb)) {
    $tid = !empty($tid) ? $tid : false;
    $banner = $background = $poster = null;
    $movie_info = get_imdb_info($imdb, true, false, $tid);
    if (!empty($tid)) {
        $poster = get_image_by_id('movie', $tid, $imdb, 'movieposter');
        $banner = get_image_by_id('movie', $tid, $imdb, 'moviebanner');
        $background = get_image_by_id('movie', $tid, $imdb, 'moviebackground');

        if (empty($poster)) {
            $poster = get_image_by_id('tmdb_id', $tid, $imdb, 'movieposter');
        }
        if (empty($banner)) {
            $banner = get_image_by_id('tmdb_id', $tid, $imdb, 'moviebanner');
        }
        if (empty($background)) {
            $background = get_image_by_id('tmdb_id', $tid, $imdb, 'moviebackground');
        }
    }
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
    if (empty($poster)) {
        $poster = find_images($imdb);
    }

    if (!empty($movie_info)) {
        $output = json_encode([
            'content' => $movie_info[0],
            'poster1' => $movie_info[1],
            'poster2' => $poster,
            'banner' => $banner,
            'background' => $background,
        ]);
        echo $output;
        die();
    }
}
echo json_encode([
    'fail' => 'invalid',
]);
die();
