<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
global $cache;

if (!empty($argv[1]) && !is_array($argv[1])) {
    $cache->delete($argv[1]);
    die("Cache: {$argv[1]} cleared\n");
}

echo "Nothing was done\n";
