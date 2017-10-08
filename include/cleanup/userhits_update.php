<?php
function userhits_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    // Remove userprofile views
    $days = 7;
    $dt = TIME_NOW - ($days * 86400);
    sql_query("DELETE FROM userhits WHERE added < $dt");
    if ($data['clean_log'] && $queries > 0) {
        write_log("Userhits Updates Cleanup: Completed using $queries queries");
    }
}
