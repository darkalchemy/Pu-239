<?php

function referrer_update($data)
{
    global $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);

    $days = 30 * 86400;
    $dt = TIME_NOW - $days;
    $fluent->deleteFrom('referrers')
        ->where('date < ?', $dt)
        ->execute();

    if ($data['clean_log']) {
        write_log('Referrer Cleanup completed');
    }
}
