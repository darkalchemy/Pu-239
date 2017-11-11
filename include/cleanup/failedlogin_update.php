<?php
/**
 * @param $data
 */
function failedlogin_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    //== Failed logins
    $secs = 1 * 86400; // Delete failed login attempts per one day.
    $dt = (TIME_NOW - $secs); // calculate date.
    sql_query("DELETE FROM failedlogins WHERE added < $dt") or sqlerr(__FILE__, __LINE__);
    if ($data['clean_log'] && $queries > 0) {
        write_log("Expired Failed Login Cleanup: Completed using $queries queries");
    }
}
