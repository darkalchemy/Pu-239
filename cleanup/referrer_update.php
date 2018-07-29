<?php

/**
 * @param $data
 */
function referrer_update($data)
{
    dbconn();
    global $queries;

    set_time_limit(1200);
    ignore_user_abort(true);

    $days = 30 * 86400;
    $dt = TIME_NOW - $days;
    sql_query('DELETE FROM referrers WHERE date < ' . sqlesc($dt)) or sqlerr(__FILE__, __LINE__);

    if ($data['clean_log'] && $queries > 0) {
        write_log("Referrer Cleanup: Completed using $queries queries");
    }
}
