<?php
/**
 * @param $data
 */
function referrer_update($data)
{
    global $site_config, $queries, $cache;
    set_time_limit(1200);
    ignore_user_abort(true);
    // Remove expired referrers...
    $days = 30 * 86400; // 30 days
    $dt = (TIME_NOW - $days);
    sql_query('DELETE FROM referrers WHERE date < ' . sqlesc($dt)) or sqlerr(__FILE__, __LINE__);
    // End Delete Last Referrers
    if ($data['clean_log'] && $queries > 0) {
        write_log("Referrer Cleanup: Completed using $queries queries");
    }
}
