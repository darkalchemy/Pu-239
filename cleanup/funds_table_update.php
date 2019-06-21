<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function funds_table_update($data)
{
    global $container;

    $time_start = microtime(true);
    $pdo = $container->get(PDO::class);
    $stmt = $pdo->prepare('TRUNCATE table funds');
    $stmt->execute();
    $cache = $container->get(Cache::class);
    $cache->delete('totalfunds_');
    if ($data['clean_log']) {
        write_log('Cleanup: Funds Table truncated');
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Delete Old Funds Cleanup: Completed' . $text);
    }
}
