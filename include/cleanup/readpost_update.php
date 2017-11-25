<?php
/**
 * @param $data
 */
function readpost_update($data)
{
    global $site_config, $queries, $cache;
    set_time_limit(1200);
    ignore_user_abort(true);
    $dt = TIME_NOW - $site_config['readpost_expiry'];
    sql_query('DELETE read_posts FROM read_posts LEFT JOIN posts ON read_posts.last_post_read = posts.id WHERE posts.added < ' . sqlesc($dt)) or sqlerr(__FILE__, __LINE__);
    if ($data['clean_log'] && $queries > 0) {
        write_log("Readpost Cleanup: Completed using $queries queries");
    }
}
