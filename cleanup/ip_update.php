<?php

/**
 * @param $data
 */
function ip_update($data)
{
    dbconn();
    global $queries;

    set_time_limit(1200);
    ignore_user_abort(true);

    $dt = sqlesc(TIME_NOW - 1 * 86400);
    sql_query('DELETE FROM ips WHERE lastbrowse < ' . $dt . ' OR lastlogin < ' . $dt . ' OR  lastannounce < ' . $dt) or sqlerr(__FILE__, __LINE__);
    if ($data['clean_log'] && $queries > 0) {
        write_log("IP Cleanup: Completed using $queries queries");
    }
}
