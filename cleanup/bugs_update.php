<?php

/**
 * @param $data
 */
function bugs_update($data)
{
    global $queries;

    set_time_limit(1200);
    ignore_user_abort(true);

    $days = 30;
    $time = (TIME_NOW - ($days * 86400));
    sql_query("DELETE FROM bugs WHERE status != 'na' AND added < {$time}") or sqlerr(__FILE__, __LINE__);

    if ($data['clean_log'] && $queries > 0) {
        write_log("Bugs Updates Cleanup: Completed using $queries queries");
    }
}
