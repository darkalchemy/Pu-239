<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;

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
    $time = TIME_NOW - ($days * 86400);
    sql_query("DELETE FROM bugs WHERE status != 'na' AND added < {$time}") or sqlerr(__FILE__, __LINE__);

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Bugs Updates Cleanup: Completed' . $text);
    }
}
