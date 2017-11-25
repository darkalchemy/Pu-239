<?php
/**
 * @param $data
 */
function cheatclean_update($data)
{
    global $site_config, $queries, $cache;
    set_time_limit(1200);
    ignore_user_abort(true);
    //== Delete cheaters
    $dt = (TIME_NOW - (30 * 86400));
    sql_query('DELETE FROM cheaters WHERE added < ' . sqlesc($dt)) or sqlerr(__FILE__, __LINE__);
    if ($data['clean_log'] && $queries > 0) {
        write_log("Cheaters List Cleanup: Removed old cheater entrys. Completed using $queries queries");
    }
}
