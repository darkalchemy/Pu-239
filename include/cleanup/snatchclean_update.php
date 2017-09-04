<?php
function snatchclean_update($data)
{
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(0);
    ignore_user_abort(1);
    //== Delete snatched
    $days = 30;
    $dt = (TIME_NOW - ($days * 86400));
    sql_query('DELETE FROM snatched WHERE complete_date < ' . sqlesc($dt)) or sqlerr(__FILE__, __LINE__);
    if ($queries > 0) {
        write_log("Snatch List Cleanup: Removed snatches not seeded for $days days. Completed using $queries queries");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items deleted/updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
