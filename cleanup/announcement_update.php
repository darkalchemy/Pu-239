<?php

/**
 * @param $data
 */
function announcement_update($data)
{
    $time_start = microtime(true);
    dbconn();
    global $queries;

    set_time_limit(1200);
    ignore_user_abort(true);

    sql_query('DELETE announcement_process FROM announcement_process LEFT JOIN users ON announcement_process.user_id=users.id WHERE users.id IS NULL') or sqlerr(__FILE__, __LINE__);
    sql_query('DELETE FROM announcement_main WHERE expires < ' . TIME_NOW) or sqlerr(__FILE__, __LINE__);
    sql_query('DELETE announcement_process FROM announcement_process LEFT JOIN announcement_main ON announcement_process.main_id=announcement_main.main_id WHERE announcement_main.main_id IS NULL') or sqlerr(__FILE__, __LINE__);

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $queries > 0) {
        write_log("Announcement Cleanup: Completed using $queries queries" . $text);
    }
}
