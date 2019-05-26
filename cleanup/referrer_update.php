<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Database;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function referrer_update($data)
{
    global $container;

    $fluent = $container->get(Database::class);
    $time_start = microtime(true);
    $days = 180 * 86400;
    $dt = TIME_NOW - $days;
    $fluent->deleteFrom('referrers')
           ->where('date < ?', $dt)
           ->execute();

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Referrer Cleanup completed' . $text);
    }
}
