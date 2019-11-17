<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
global $container;

set_time_limit(18000);
$start = microtime(true);
for($i = 1; $i<=10; $i++) {
    $pid = pcntl_fork();

    if($pid == -1) {
        exit("Error forking...\n");
    } else if($pid == 0) {
        $limit = 50;
        $offset = $i ===  1 ? 0 : ($i - 1) * $limit;
        echo "Starting thread $i\n";
        exec('php ' . BIN_DIR . "optimize_resize_images.php $limit $offset");
        exit();
    }
}

while(pcntl_waitpid(0, $status) != -1);

$end = microtime(true);
$run = $end - $start;
echo 'Runtime: ' . $run . "\nThreads: $i\n\n";