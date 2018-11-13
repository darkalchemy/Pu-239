<?php

/**
 * @param $data
 */
function failedlogin_update($data)
{
    $time_start = microtime(true);
    dbconn();
    global $queries;

    set_time_limit(1200);
    ignore_user_abort(true);

    $secs = 86400;
    $dt = (TIME_NOW - $secs);
    sql_query("DELETE FROM failedlogins WHERE added < $dt") or sqlerr(__FILE__, __LINE__);
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $queries > 0) {
        write_log("Expired Failed Login Cleanup: Completed using $queries queries" . $text);
    }
}
