<?php
function bugs_update($data)
{
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(1);
    //== delete bugs
    $days = 14;
    $time = (TIME_NOW - ($days * 86400));
    sql_query("DELETE FROM bugs WHERE status != 'na' AND added < {$time}") or sqlerr(__FILE__, __LINE__);
    //==
    if ($queries > 0) {
        write_log("Bugs Updates Cleanup: Completed using $queries queries");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items deleted/updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
