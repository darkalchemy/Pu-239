<?php
function cleanlog_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    //== Delete cleanup log
    $dt = sqlesc(TIME_NOW - 1 * 86400);
    sql_query('DELETE FROM cleanup_log WHERE clog_time < ' . $dt) or sqlerr(__FILE__, __LINE__);
    if ($queries > 0) {
        write_log("Cleanup Log Cleanup: Completed using $queries queries");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items deleted/updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
