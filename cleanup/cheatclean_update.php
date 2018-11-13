<?php

/**
 * @param $data
 */
function cheatclean_update($data)
{
    $time_start = microtime(true);
    dbconn();
    global $queries;

    set_time_limit(1200);
    ignore_user_abort(true);

    $dt = (TIME_NOW - (30 * 86400));
    sql_query('DELETE FROM cheaters WHERE added < ' . sqlesc($dt)) or sqlerr(__FILE__, __LINE__);
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $queries > 0) {
        write_log("Cheaters List Cleanup: Removed old cheater entrys. Completed using $queries queries" . $text);
    }
}
