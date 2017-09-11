<?php
function visible_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    $deadtime_tor = TIME_NOW - $site_config['max_dead_torrent_time'];
    $What_Time = (XBT_TRACKER == true ? 'mtime' : 'last_action');
    sql_query("UPDATE torrents SET visible='no' WHERE visible='yes' AND $What_Time < $deadtime_tor");
    if (XBT_TRACKER == true) {
        sql_query("UPDATE torrents SET visible='yes' WHERE visible='no' AND seeders > 0");
    }
    if ($queries > 0) {
        write_log("Torrent Visible Cleanup: Completed using $queries queries");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items deleted/updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
