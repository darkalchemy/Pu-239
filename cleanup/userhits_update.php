<?php

function userhits_update($data)
{
    global $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);

    $days = 14;
    $dt = TIME_NOW - ($days * 86400);
    $fluent->deleteFrom('userhits')
        ->where('added < ?', $dt)
        ->execute();

    if ($data['clean_log']) {
        write_log('Userhits Updates Cleanup completed');
    }
}
