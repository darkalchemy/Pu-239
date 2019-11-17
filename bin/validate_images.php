<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\ImageProxy;

require_once __DIR__ . '/../include/bittorrent.php';
global $container, $site_config;

set_time_limit(18000);
if (!isset($argv[1]) || ($argv[1] !== 'validate' && $argv[1] !== 'optimize' && $argv[1] !== 'purge')) {
    die("This script can validate, optimize and delete all images found in public/images/proxy\n\nTo run:\n{$argv[0]} [purge|validate|optimize]\n\n");
}
foreach ($argv as $arg) {
    $optimize = $arg === 'optimize' ? true : false;
    $purge = $arg === 'purge' ? true : false;
    $validate = $arg === 'validate' ? true : false;
}
if ($purge) {
    $fluent = $container->get(Database::class);
    $images = $fluent->from('images')
                     ->select('null')
                     ->select('url')
                     ->select('type')
                     ->fetchAll();
    $photos = $fluent->from('person')
                     ->select('null')
                     ->select('photo AS url')
                     ->where('photo IS NOT null')
                     ->fetchAll();
    $hashes = [];
    $urls = array_merge($images, $photos);
    foreach ($urls as $url) {
        $hashes[] = hash('sha256', $url['url']);
        if (empty($url['type'])) {
            $url['type'] = 'person';
        }
        if ($url['type'] === 'poster') {
            $hashes[] = hash('sha256', $url['url'] . '_converted_' . '20');
            $hashes[] = hash('sha256', $url['url'] . '_450');
            $hashes[] = hash('sha256', $url['url'] . '_250');
            $hashes[] = hash('sha256', $url['url'] . '_100');
            $hashes[] = hash('sha256', $url['url'] . '_300');
        } elseif ($url['type'] === 'banner') {
            $hashes[] = hash('sha256', $url['url'] . '_1000');
        } elseif ($url['type'] === 'person') {
            $hashes[] = hash('sha256', $url['url'] . '_250');
            $hashes[] = hash('sha256', $url['url'] . '_110');
        }
    }
}
$site_config['cache']['driver'] = 'memory';
$image_proxy = $container->get(ImageProxy::class);
$paths = [
    PROXY_IMAGES_DIR,
];

$dirsize = $o = $i = $x = 0;
foreach ($paths as $path) {
    $dirsize += GetDirectorySize($path, false, false);
    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($objects as $name => $object) {
        $basename = basename($name);
        if ($purge && !in_array($basename, $hashes)) {
            unlink($name);
            ++$x;
        }
        if ($validate && !exif_imagetype($name)) {
            if ($basename === '.gitignore') {
                continue;
            }
            ++$i;
            unlink($name);
        }
        if ($optimize) {
            $image_proxy->optimize_image($name, '', true);
        }
        ++$o;
    }
}
$dirsize = mksize($dirsize);
echo "$o images validated
Images size: $dirsize
$x images not found in db\n
$i bad images removed\n";
