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
function freeslot_update($data)
{
    $time_start = microtime(true);
    $dt = TIME_NOW;
    sql_query('UPDATE freeslots SET addedup = 0 WHERE addedup != 0 AND addedup < ' . $dt) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE freeslots SET addedfree = 0 WHERE addedfree != 0 AND addedfree < ' . $dt) or sqlerr(__FILE__, __LINE__);
    sql_query('DELETE FROM freeslots WHERE addedup = 0 AND addedfree = 0') or sqlerr(__FILE__, __LINE__);
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Freeslot Cleanup: Completed' . $text);
    }
}
