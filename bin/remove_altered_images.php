<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\ImageProxy;

require_once __DIR__ . '/../include/bittorrent.php';
global $container;

$image_proxy = $container->get(ImageProxy::class);
$path = IMAGES_DIR . 'proxy/';
$fluent = $container->get(Database::class);
$urls = $fluent->from('images')
               ->select('url');
$images = [];
foreach ($urls as $url) {
    $hash = hash('sha512', $url['url']);
    $images[] = PROXY_IMAGES_DIR . $hash;
}
$filesize = $i = 0;
$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
foreach ($objects as $name => $object) {
    if (!in_array($name, $images) && $name != $path . '.gitignore') {
        $filesize += filesize($name);
        ++$i;
        unlink($name);
    }
}

$set = [
    'fetched' => 'no',
    'updated' => 0,
    'checked' => 0,
];
$fluent->update('images')
       ->set($set)
       ->where('added > 0')
       ->execute();

echo "$i altered images removed
Images size: " . mksize($filesize) . "\n";
