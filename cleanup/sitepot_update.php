<?php
/**
 * @param $data
 */
function sitepot_update($data)
{
    global $queries;

$cache = new DarkAlchemy\Pu239\Cache();
    set_time_limit(1200);
    ignore_user_abort(true);

    sql_query("UPDATE avps SET value_i = 0, value_s = '0' WHERE arg = 'sitepot' AND value_u < " . TIME_NOW . " AND value_s = '1'") or sqlerr(__FILE__, __LINE__);
    $cache->delete('Sitepot_');
    if ($data['clean_log'] && $queries > 0) {
        write_log("Sitepot Cleanup: Completed using $queries queries");
    }
}
