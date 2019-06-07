<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once BIN_DIR . 'functions.php';
global $site_config, $site_config;

if (empty($BLOCKS)) {
    die('BLOCKS are empty');
}

$site_config['cache']['driver'] = 'memory';
$user = get_webserver_user();
$group = get_webserver_user();
cleanup($group);
$paths = [
    ROOT_DIR,
];
$styles = get_styles();
$dirs = [];
foreach ($styles as $style) {
    $dirs[] = CHAT_DIR . "css/$style/";
    $dirs[] = TEMPLATE_DIR . "$style/css/";
}
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
    'cache',
];

$folders = [
    BIN_DIR,
    CACHE_DIR,
    IMDB_CACHE_DIR,
    BACKUPS_DIR,
    TORRENTS_DIR,
    USER_TORRENTS_DIR,
    LOGS_DIR,
    SQLERROR_LOGS_DIR,
    BITBUCKET_DIR,
    UPLOADSUB_DIR,
    ROOT_DIR . '.git',
    ROOT_DIR . 'dir_list/',
    ROOT_DIR . 'uploads/',
    CHAT_DIR . 'js/',
    IMAGES_DIR,
];

$folders = array_merge($dirs, $folders);

$excludes = [
    ROOT_DIR . 'vendor',
    ROOT_DIR . 'node_modules',
];

foreach ($folders as $folder) {
    if (file_exists($folder)) {
        chmod_r($folder, $group);
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

foreach ($folders as $folder) {
    if (file_exists($folder)) {
        chown_r($folder, $group);
    }
}

/**
 * @param $path
 * @param $group
 */
function chown_r($path, $group)
{
    if (!file_exists($path)) {
        return;
    }
    $user_group = false;
    if ($path === IMAGES_DIR || $path === CACHE_DIR) {
        $user_group = true;
    }
    $dir = new DirectoryIterator($path);
    chown($path, $group);
    foreach ($dir as $item) {
        chown($item->getPathname(), $group);
        if ($user_group) {
            chgrp($item->getPathname(), $group);
        }
        if ($item->isDir() && !$item->isDot()) {
            chown_r($item->getPathname(), $group);
        }
    }
}

cleanup($group);
echo "$i files processed\n";
