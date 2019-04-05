<?php

/**
 * @param $data
 *
 * @throws \Envms\FluentPDO\Exception
 */
function userhits_update($data)
{
    $time_start = microtime(true);
    global $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);

    $days = 14;
    $dt = TIME_NOW - ($days * 86400);
    $fluent->deleteFrom('userhits')
        ->where('added < ?', $dt)
        ->execute();

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Userhits Updates Cleanup completed' . $text);
    }
}
