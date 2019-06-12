<?php

declare(strict_types = 1);

use Pu239\Image;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_imdb.php';
require_once INCL_DIR . 'function_get_images.php';
$url = htmlsafechars($_POST['url']);
$tid = !empty($_POST['tid']) ? (int) strip_tags($_POST['tid']) : null;
$image = !empty($_POST['image']) ? htmlsafechars($_POST['image']) : null;
header('content-type: application/json');
global $container;

$imdb = '';
if (!empty($url)) {
    preg_match('/(tt[\d]{7,8})/i', $url, $imdb);
    $imdb = !empty($imdb[1]) ? $imdb[1] : null;
}

if (!empty($imdb)) {
    $banner = $background = null;
    $poster = !empty($image) ? $image : get_image_by_id('movie', $imdb, 'movieposter');
    if (empty($poster)) {
        $poster = get_image_by_id('tmdb_id', $imdb, 'movieposter');
    }
    if (empty($poster)) {
        $images_class = $container->get(Image::class);
        $poster = $images_class->find_images($imdb);
    }
    if (empty($poster)) {
        $poster = null;
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
