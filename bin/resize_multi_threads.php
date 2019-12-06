<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

require_once __DIR__ . '/../include/bittorrent.php';
global $container;

set_time_limit(18000);
$start = microtime(true);
$i = 1;
$cache = $container->get(Cache::class);
$cores = $cache->get('cores_');
if (!$cores || is_null($cores)) {
    $cores = `grep -c processor /proc/cpuinfo`;
    $cores = empty($cores) ? 1 : (int) $cores;
    $cache->set('cores_', $cores, 0);
}
$use_cores = $cores * 2;
$threads = $use_cores > 20 ? 20 : $use_cores;
$limit = 50;
$childs = [];
$fluent = $container->get(Database::class);
$images = $fluent->from('images')
                 ->select(null)
                 ->select('COUNT(url) AS count')
                 ->where('fetched = "no"')
                 ->fetch('count');
$persons = $fluent->from('person')
                  ->select(null)
                  ->select('COUNT(photo) AS count')
                  ->where('photo IS NOT NULL')
                  ->where('updated + 604800 < ?', TIME_NOW)
                  ->fetch('count');

if ($images > 0 && $images < $threads * $limit) {
    $threads = (int) floor($images / $limit);
}
if ($persons > 0 && $persons < $threads * $limit) {
    $threads = (int) floor($persons / $limit);
}
if ($images === 0 && $persons === 0) {
    $threads = 0;
}

echo "$images images from the image table\n";
echo "$persons images from the photo table\n";
if (isset($argv[1]) && $argv[1] === 'count') {
    echo "threads: $threads\n";
    echo "limit: $limit\n";
    die();
}
if ($threads < 2) {
    passthru('php ' . BIN_DIR . "optimize_resize_images.php $limit 0");
} else {
    for ($i = 1; $i <= $threads; $i++) {
        $pid = pcntl_fork();

        if ($pid == -1) {
            die("Error forking...\n");
        }
        if ($pid) {
            echo "PID $pid started\n";
            $childs[] = $pid;
        } else {
            $offset = $i === 1 ? 0 : ($i - 1) * $limit;
            exec('php ' . BIN_DIR . "optimize_resize_images.php $limit $offset");
            exit();
        }
    }

    while (count($childs) > 0) {
        foreach ($childs as $key => $pid) {
            $res = pcntl_waitpid($pid, $status, WNOHANG);
            if ($res == -1 || $res > 0) {
                unset($childs[$key]);
                echo "PID $pid exited, " . count($childs) . " remaining\n";
            }
        }

        sleep(5);
    }
}
$end = microtime(true);
$run = $end - $start;
echo 'Runtime: ' . $run . "\nThreads: $i\n\n";
