<?php

/**
 * @param $data
 */
function achievement_sreset_update($data)
{
    $time_start = microtime(true);
    dbconn();
    global $queries;

    set_time_limit(1200);
    ignore_user_abort(true);

    sql_query('UPDATE usersachiev SET dailyshouts = 0') or sqlerr(__FILE__, __LINE__);
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $queries > 0) {
        write_log("Achievements Cleanup: Daily Shouts reset Completed using $queries queries." . $text);
    }
}
