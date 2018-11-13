<?php

function snatchclean_update($data)
{
    $time_start = microtime(true);
    global $snatched_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);

    $days = 90;
    $dt = TIME_NOW - ($days * 86400);
    $snatched_stuffs->delete_stale($dt);
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log("Snatch List Cleanup: Removed snatches not active for $days days." . $text);
    }
}
