<?php

function referrer_update($data)
{
    $time_start = microtime(true);
    global $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);

    $days = 30 * 86400;
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
