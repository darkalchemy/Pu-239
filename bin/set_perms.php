<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';

$paths = [
    ROOT_DIR,
];

$exts = [
    'php',
    'js',
    'txt',
    'css',
    'md',
    'json',
    'gz',
];

$i = 0;
foreach ($paths as $path) {
    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($objects as $name => $object) {
        if (is_file($name)) {
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            if (in_array($ext, $exts)) {
                if (chmod($name, 0664)) {
                    $i++;
                }
            }
        }
    }
}

echo "$i files processed\n";
