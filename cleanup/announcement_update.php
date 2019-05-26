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
function announcement_update($data)
{
    $time_start = microtime(true);
    sql_query('DELETE FROM announcement_main WHERE expires < ' . TIME_NOW) or sqlerr(__FILE__, __LINE__);

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Announcement Cleanup: Completed' . $text);
    }
}
