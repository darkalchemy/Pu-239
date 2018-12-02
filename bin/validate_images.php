<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
$paths = [
    IMAGES_DIR . DIRECTORY_SEPARATOR . 'proxy',
];

$dirsize = $o = $i = 0;
foreach ($paths as $path) {
    $dirsize += (int) GetDirectorySize($path, false);
    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($objects as $name => $object) {
        if (!exif_imagetype($name)) {
            if (basename($name) === '.gitignore') {
                continue;
            }
            ++$i;
            unlink($name);
            echo "$name \n";
        } else {
            ++$o;
        }
    }
}
$dirsize = mksize($dirsize);
echo "$o images validated
Images size: $dirsize
$i bad images removed\n";
