<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Image;
use Pu239\ImageProxy;

require_once __DIR__ . '/../include/bittorrent.php';
global $container;

set_time_limit(1800);
$image_proxy = $container->get(ImageProxy::class);
$path = IMAGES_DIR . 'proxy/';
$fluent = $container->get(Database::class);
$images = $fluent->from('images')
                 ->select(null)
                 ->select('id')
                 ->select('url')
                 ->select('type')
                 ->where('fetched = "no"')
                 ->orderBy('id DESC')
                 ->fetchAll();

$values = [];
$image_stuffs = $container->get(Image::class);
foreach ($images as $image) {
    $start = microtime(true);
    $untouched = url_proxy($image['url'], true);
    $end = microtime(true);
    $run = $end - $start;
    $sleep = 1.5 - $run > 0 ? (1.5 - $run) * 1000000 : 0;
    usleep((int) $sleep);
    echo 'slept: ' . $sleep / 1000000 . "\n";
    echo 'untouched: ' . $run . "\n\n";

    if (!empty($untouched)) {
        $values[] = [
            'url' => $image['url'],
            'fetched' => 'yes',
        ];
        $update = [
            'fetched' => 'yes',
        ];
        if ($image['type'] === 'poster') {
            $start1 = microtime(true);
            url_proxy($image['url'], true, 450);
            $end1 = microtime(true);
            $run1 = $end1 - $start1;
            echo 'w450q100: ' . $run1 . "\n\n";

            $start2 = microtime(true);
            url_proxy($image['url'], true, 250);
            $end2 = microtime(true);
            $run2 = $end2 - $start2;
            echo 'w250q100: ' . $run2 . "\n\n";

            $start3 = microtime(true);
            url_proxy($image['url'], true, 250, null, 20);
            $end3 = microtime(true);
            $run3 = $end3 - $start3;
            echo 'w250q20: ' . $run3 . "\n\n";
        } elseif ($image['type'] === 'banner') {
            $start4 = microtime(true);
            url_proxy($image['url'], true, 1000, 185);
            $end4 = microtime(true);
            $run4 = $end4 - $start4;
            echo '1000x185: ' . $run4 . "\n\n";
        }
        $image_stuffs->update($values, $update);
    }
}

echo count($values) . " optimized and resized\n";
