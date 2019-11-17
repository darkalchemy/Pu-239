<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
global $container;

set_time_limit(18000);
$start = microtime(true);
$childs = [];
for ($i = 1; $i <= 10; $i++) {
    $pid = pcntl_fork();

    if ($pid == -1) {
        die("Error forking...\n");
    }
    if ($pid) {
        echo "PID $pid started\n";
        $childs[] = $pid;
    } else {
        $limit = 50;
        $offset = $i === 1 ? 0 : ($i - 1) * $limit;
        exec('php ' . BIN_DIR . "optimize_resize_images.php $limit $offset");
        exit();
    }
}

while (count($childs) > 0) {
    foreach ($childs as $key => $pid) {
        $res = pcntl_waitpid($pid, $status, WNOHANG);
        if ($res == -1 || $res > 0) {
            echo "PID $pid exited, " . count($childs) - 1 . " remaining\n";
            unset($childs[$key]);
        }
    }

    sleep(5);
}

$end = microtime(true);
$run = $end - $start;
echo 'Runtime: ' . $run . "\nThreads: $i\n\n";
