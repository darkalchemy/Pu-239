<?php

/**
 * @param $data
 */
function snatchclean_update($data)
{
    global $snatched_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);

    $days = 90;
    $dt = (TIME_NOW - ($days * 86400));
    $snatched_stuffs->delete_stale($dt);
    if ($data['clean_log']) {
        write_log("Snatch List Cleanup: Removed snatches not active for $days days. Completed using 1 query");
    }
}
