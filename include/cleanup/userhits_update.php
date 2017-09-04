<?php
function userhits_update($data)
{
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(1);
    // Remove userprofile views
    $days = 7;
    $dt = TIME_NOW - ($days * 86400);
    sql_query("DELETE FROM userhits WHERE added < $dt");
    if ($queries > 0) {
        write_log("Userhits Updates Cleanup: Completed using $queries queries");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items deleted/updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
