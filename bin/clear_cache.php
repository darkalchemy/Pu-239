<?php

declare(strict_types = 1);

global $site_config;

require_once __DIR__ . '/../include/bittorrent.php';
$database = '';
clear_di_cache();
cleanup($group);
if (!empty($argv[1]) && !is_array($argv[1])) {
    $cache->delete($argv[1]);
    die("Cache: {$argv[1]} cleared\n");
} else {
    if ($site_config['cache']['driver'] === 'file') {
        exec("sudo rm -r {$site_config['files']['path']}");
    } else {
        $cache->flushDB();
        if ($site_config['cache']['driver'] === 'redis') {
            $database = " [DB:{$site_config['redis']['database']}]";
        }
    }
    die(ucfirst($site_config['cache']['driver']) . " Cache{$database} was flushed\n");
}
