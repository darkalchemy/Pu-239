<?php
function ip_update($data)
{
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(0);
    ignore_user_abort(1);
    //== Delete iplog
    $dt = sqlesc(TIME_NOW - 1 * 86400);
    sql_query('DELETE FROM ips WHERE lastbrowse < ' . $dt . ' OR lastlogin < ' . $dt . ' OR  lastannounce < ' . $dt) or sqlerr(__FILE__, __LINE__);
    if ($queries > 0) {
        write_log("Ip clean-------------------- Ip cleanup Complete using $queries queries --------------------");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items deleted/updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
