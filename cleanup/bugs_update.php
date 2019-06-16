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
function bugs_update($data)
{
    $time_start = microtime(true);
    $days = 30;
    $dt = TIME_NOW - ($days * 86400);
    global $container;

    $fluent = $container->get(Database::class);
    $fluent->deleteFrom('bugs')
           ->where('status != "na"')
           ->where('added < ?', $dt)
           ->execute();

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Bugs Updates Cleanup: Completed' . $text);
    }
}
