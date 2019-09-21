<?php

declare(strict_types = 1);

use Pu239\Database;

require_once __DIR__ . '/../include/bittorrent.php';
global $container;

if (!isset($argv[1]) || $argv[1] !== 'rehash') {
    die("This script will rehash and rename all images in public/images/proxy directory\n\nTo run:\n{$argv[0]} rehash\n\n");
}

$fluent = $container->get(Database::class);
$urls = $fluent->from('images')
               ->select('null')
               ->select('url')
               ->select('type')
               ->fetchAll();

$photos = $fluent->from('person')
                 ->select(null)
                 ->select('photo AS url')
                 ->where('photo IS NOT NULL')
                 ->fetchAll();

$urls = array_merge($urls, $photos);
$i = 0;
foreach ($urls as $url) {
    if (empty($url['type'])) {
        $url['type'] = 'person';
    }
    $hash = hash('sha512', $url['url']);
    if (file_exists(PROXY_IMAGES_DIR . $hash)) {
        $new = hash('sha256', $url['url']);
        $i += rename_file($hash, $new);
    }

    if ($url['type'] === 'poster') {
        $hash = hash('sha512', $url['url'] . '_converted_' . '20');
        if (file_exists(PROXY_IMAGES_DIR . $hash)) {
            $new = hash('sha256', $url['url'] . '_converted_' . '20');
            $i += rename_file($hash, $new);
        }
        $hash = hash('sha512', $url['url'] . '_450');
        if (file_exists(PROXY_IMAGES_DIR . $hash)) {
            $new = hash('sha256', $url['url'] . '_450');
            $i += rename_file($hash, $new);
        }
    } elseif ($url['type'] === 'poster' || $url['type'] === 'person') {
        $hash = hash('sha512', $url['url'] . '_250');
        if (file_exists(PROXY_IMAGES_DIR . $hash)) {
            $new = hash('sha256', $url['url'] . '_250');
            $i += rename_file($hash, $new);
        }
        $hash = hash('sha512', $url['url'] . '_110');
        if (file_exists(PROXY_IMAGES_DIR . $hash)) {
            $new = hash('sha256', $url['url'] . '_110');
            $i += rename_file($hash, $new);
        }
    } elseif ($url['type'] === 'banner') {
        $hash = hash('sha512', $url['url'] . '_1000');
        if (file_exists(PROXY_IMAGES_DIR . $hash)) {
            $new = hash('sha256', $url['url'] . '_1000');
            $i += rename_file($hash, $new);
        }
    }
}

echo "$i images renamed\n";

/**
 * @param $old
 * @param $new
 *
 * @return int
 */
function rename_file($old, $new)
{
    if (file_exists(PROXY_IMAGES_DIR . $old) && !file_exists(PROXY_IMAGES_DIR . $new)) {
        rename(PROXY_IMAGES_DIR . $old, PROXY_IMAGES_DIR . $new);

        return 1;
    }

    return 0;
}
