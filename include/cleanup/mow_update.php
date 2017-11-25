<?php
/**
 * @param $data
 */
function mow_update($data)
{
    global $site_config, $queries, $cache;
    set_time_limit(1200);
    ignore_user_abort(true);
    //== Movie of the week
    $res_tor = sql_query('SELECT id, name
                            FROM torrents
                            WHERE times_completed > 0 AND category IN (' . join(', ', $site_config['movie_cats']) . ')
                            ORDER BY times_completed DESC
                            LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res_tor);
    if (!empty($arr)) {
        sql_query('UPDATE avps SET value_u = ' . sqlesc($arr['id']) . ', value_i = ' . sqlesc(TIME_NOW) . " WHERE avps.arg = 'bestfilmofweek'") or sqlerr(__FILE__, __LINE__);
        $cache->delete('top_movie_2');
    }
    if ($data['clean_log']) {
        write_log('Torrent [' . (int)$arr['id'] . '] [' . htmlentities($arr['name']) . "] was set 'Best Film of the Week' by system");
    }
    //==End
    if ($data['clean_log'] && $queries > 0) {
        write_log("Movie of the Week Cleanup: Completed using $queries queries");
    }
}
