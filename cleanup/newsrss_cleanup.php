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
function newsrss_cleanup($data)
{
    global $container;

    $time_start = microtime(true);
    $fluent = $container->get(Database::class);
    $fluent->deleteFrom('newsrss')
           ->where('added < NOW() - INTERVAL 30 DAY')
           ->execute();

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('NewsRSS Cleanup: Completed using 1 queries' . $text);
    }
}
