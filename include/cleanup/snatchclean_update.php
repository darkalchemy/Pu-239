<?php
function snatchclean_update($data)
{
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(0);
    ignore_user_abort(1);
    //== Delete snatched
    $dt = (TIME_NOW - (30 * 86400));
    sql_query('DELETE FROM snatched WHERE complete_date < ' . sqlesc($dt)) or sqlerr(__FILE__, __LINE__);
    if ($queries > 0) {
        write_log("Snatch list clean-------------------- Removed snatches not seeded for 99 days. Cleanup Complete using $queries queries --------------------");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items deleted/updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
