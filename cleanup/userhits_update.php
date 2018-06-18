<?php
/**
 * @param $data
 */
function userhits_update($data)
{
    global $queries;
    set_time_limit(1200);
    ignore_user_abort(true);

    $days = 14;
    $dt = TIME_NOW - ($days * 86400);
    sql_query("DELETE FROM userhits WHERE added < $dt") or sqlerr(__FILE__, __LINE__);
    if ($data['clean_log'] && $queries > 0) {
        write_log("Userhits Updates Cleanup: Completed using $queries queries");
    }
}
