<?php
function docleanup($data)
{
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(1);
    // Remove expired referrers...
    $days = 30 * 86400; // 30 days
    $dt = (TIME_NOW - $days);
    sql_query('DELETE FROM referrers WHERE date < ' . sqlesc($dt)) or sqlerr(__FILE__, __LINE__);
    // End Delete Last Referrers
    if ($queries > 0) {
        write_log("Referrer Clean -------------------- Referrer cleanup Complete using $queries queries --------------------");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items deleted/updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
