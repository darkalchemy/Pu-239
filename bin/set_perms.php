<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
$user = trim(`logname`);
$group = posix_getgrgid(filegroup(__FILE__))['name'];
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
    'example',
    'sql',
];

$folders = [
    BIN_DIR,
    CACHE_DIR,
    BACKUPS_DIR,
    TORRENTS_DIR,
    USER_TORRENTS_DIR,
    SQLERROR_LOGS_DIR,
    BITBUCKET_DIR,
    ROOT_DIR . '.git',
    ROOT_DIR . 'dir_list/',
    ROOT_DIR . 'uploads/',
    PUBLIC_DIR . 'install/',
    PUBLIC_DIR . 'install/extra/',
    CHAT_DIR . 'css/',
    CHAT_DIR . 'js/',
    TEMPLATE_DIR . '1/css/',
    TEMPLATE_DIR . '2/css/',
    PUBLIC_DIR . 'images/proxy/',
];

$excludes = [
    ROOT_DIR . 'vendor',
    ROOT_DIR . 'node_modules',
];

foreach ($folders as $folder) {
    if (file_exists($folder)) {
        chmod_r($folder);
    }
}

$i = 1;
foreach ($paths as $path) {
    if (file_exists($path)) {
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $name => $object) {
            if (is_file($name)) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $parent = dirname($name);
                $continue = true;
                foreach ($excludes as $exclude) {
                    if (preg_match('#' . $exclude . '#', $parent)) {
                        $continue = false;
                    }
                }
                if ($continue && in_array($ext, $exts)) {
                    if (chmod($name, 0664)) {
                        chown($name, $user);
                        chgrp($name, $group);
                        ++$i;
                    }
                }
            }
        }
    }
}

function chmod_r($path)
{
    if (!file_exists($path) && is_dir($path)) {
        return;
    }
    $dir = new DirectoryIterator($path);
    foreach ($dir as $item) {
        chmod($item->getPathname(), 0775);
        if ($item->isDir() && !$item->isDot()) {
            chmod_r($item->getPathname());
        }
    }
}

echo "$i files processed\n";
