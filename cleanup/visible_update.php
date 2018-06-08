<?php
/**
 * @param $data
 */
function visible_update($data)
{
    global $site_config, $queries;
    set_time_limit(1200);
    ignore_user_abort(true);

    $deadtime_tor = TIME_NOW - $site_config['max_dead_torrent_time'];
    $What_Time    = (XBT_TRACKER ? 'mtime' : 'last_action');
    sql_query("UPDATE torrents SET visible='no' WHERE visible='yes' AND $What_Time < $deadtime_tor") or sqlerr(__FILE__, __LINE__);
    if (XBT_TRACKER) {
        sql_query("UPDATE torrents SET visible='yes' WHERE visible='no' AND seeders > 0") or sqlerr(__FILE__, __LINE__);
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Torrent Visible Cleanup: Completed using $queries queries");
    }
}
