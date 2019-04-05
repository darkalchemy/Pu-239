<?php

require_once __DIR__ . '/../include/bittorrent.php';
global $cache;

if (!empty($argv[1]) && !is_array($argv[1])) {
    $cache->delete($argv[1]);
    die("Cache: {$argv[1]} cleared\n");
} else {
    if ($site_config['cache']['driver'] === 'file') {
        exec("sudo rm -r {$site_config['files']['path']}");
    } else {
        $cache->flushDB();
    }
    die(ucfirst($site_config['cache']['driver']) . " Cache was flushed\n");
}

echo "Nothing was done\n";
