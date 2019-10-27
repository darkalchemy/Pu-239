<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once BIN_DIR . 'functions.php';
global $site_config, $site_config;

if (empty($BLOCKS)) {
    die('BLOCKS are empty');
}

toggle_site_status(true);
$site_config['cache']['driver'] = 'memory';
$user = get_username();
$group = get_webserver_user();
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
    NFO_DIR,
    VENDOR_DIR,
    NODE_DIR,
];

$folders = array_merge($dirs, $folders);
$excludes = [
    ROOT_DIR . 'vendor/',
    ROOT_DIR . 'node_modules/',
    ROOT_DIR . '.git/',
    ROOT_DIR . '.idea/',
];

$chmod_folders = [
    VENDOR_DIR,
];

cleanup($group);
chmod(ROOT_DIR, 0774);
$i = 1;

foreach ($paths as $path) {
    if (file_exists($path)) {
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $name => $object) {
            if (is_dir($name) && !preg_match('#' . implode('|', $excludes) . '#', realpath($name) . '/')) {
                if (preg_match('#' . IMAGES_DIR . '|' . NFO_DIR . '|' . CACHE_DIR . '|' . IMDB_CACHE_DIR . '#', realpath($name) . '/')) {
                    chown($name, $group);
                } else {
                    chown($name, $user);
                }
                chgrp($name, $group);
                chmod($name, 0774);
                ++$i;
            } elseif (!is_dir($name) && !preg_match('#' . implode('|', $excludes) . '#', realpath($name) . '/')) {
                if (basename($name) === 'i18n.sh') {
                    chown($name, $group);
                    chmod($name, 0774);
                } elseif (preg_match('#' . IMAGES_DIR . '|' . NFO_DIR . '|' . CACHE_DIR . '|' . IMDB_CACHE_DIR . '#', realpath($name) . '/')) {
                    chown($name, $group);
                    chgrp($name, $group);
                    chmod($name, 0774);
                } else {
                    chmod($name, 0664);
                }
                ++$i;
            }
        }
    }
}
cleanup($group);
toggle_site_status(false);
echo "$i files processed\n";
