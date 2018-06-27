<?php

/**
 * @param $data
 */
function snatchclean_update($data)
{
    global $queries;

    set_time_limit(1200);
    ignore_user_abort(true);

    $days = 30;
    $dt = (TIME_NOW - ($days * 86400));
    sql_query('DELETE FROM snatched WHERE complete_date < ' . sqlesc($dt)) or sqlerr(__FILE__, __LINE__);
    if ($data['clean_log'] && $queries > 0) {
        write_log("Snatch List Cleanup: Removed snatches not seeded for $days days. Completed using $queries queries");
    }
}
