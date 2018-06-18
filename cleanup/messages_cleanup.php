<?php
/**
 * @param $data
 */
function pms_cleanup($data)
{
    global $site_config, $queries, $cache;

    require_once INCL_DIR . 'user_functions.php';
    set_time_limit(1200);
    ignore_user_abort(true);

    $secs = 90 * 86400;
    $dt = sqlesc(TIME_NOW - $secs);
    $query = sql_query("SELECT id, receiver FROM messages WHERE saved != 'yes' AND added <= $dt") or sqlerr(__FILE__, __LINE__);
    while ($row = mysqli_fetch_assoc($query)) {
        $cache->delete('inbox_' . $row['receiver']);
        $messages[] = $row['id'];
    }
    if (!empty($messages)) {
        $list = implode(', ', $messages);
        sql_query('DELETE FROM messages WHERE id IN (' . $list . ')') or sqlerr(__FILE__, __LINE__);
    }

    if ($data['clean_log'] && $queries > 0) {
        write_log("PMs Cleanup: Private Messages Deleted using $queries queries");
    }
}
