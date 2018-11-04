<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_imdb.php';
require_once INCL_DIR . 'function_get_images.php';
require_once INCL_DIR . 'share_images.php';
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
    $banner = $background = null;
    $poster = !empty($image) ? $image : get_image_by_id('movie', $tid, $imdb, 'movieposter');
    if (empty($poster)) {
        $poster = get_image_by_id('tmdb_id', $tid, $imdb, 'movieposter');
    }
    if (empty($poster)) {
        $poster = find_images($imdb);
    }
    $movie_info = get_imdb_info($imdb, true, false, $tid, $poster);

    if (!empty($movie_info)) {
        $output = json_encode([
            'content' => $movie_info[0],
        ]);
        echo $output;
        die();
    }
}
echo json_encode([
    'fail' => 'invalid',
]);
die();
