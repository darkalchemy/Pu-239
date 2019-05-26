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
function processkill_update($data)
{
    global $site_config;

    $time_start = microtime(true);
    $sql = sql_query('SHOW PROCESSLIST') or sqlerr(__FILE__, __LINE__);
    $cnt = 0;
    while ($arr = mysqli_fetch_assoc($sql)) {
        if ($arr['db'] == $site_config['db']['database'] && $arr['Command'] === 'Sleep' && $arr['Time'] > 120) {
            sql_query("KILL {$arr['Id']}") or sqlerr(__FILE__, __LINE__);
            ++$cnt;
        }
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Process Kill Cleanup: Completed' . $text);
    }
}
