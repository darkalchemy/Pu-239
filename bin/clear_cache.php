<?php

require_once __DIR__ . '/../include/bittorrent.php';
global $cache;

if (!empty($argv[1]) && !is_array($argv[1])) {
    $cache->delete($argv[1]);
    die("Cache: {$argv[1]} cleared\n");
} else {
    if ($_ENV['CACHE_DRIVER'] === 'file') {
        exec("sudo rm -r {$_ENV['FILES_PATH']}");
    } else {
        $cache->flushDB();
    }
    die(ucfirst($_ENV['CACHE_DRIVER']) . " Cache was flushed\n");
}

echo "Nothing was done\n";
