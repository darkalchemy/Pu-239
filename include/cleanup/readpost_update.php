<?php
function readpost_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    // Remove expired readposts...
    $dt = TIME_NOW - $site_config['readpost_expiry'];
    sql_query('DELETE read_posts FROM read_posts LEFT JOIN posts ON read_posts.last_post_read = posts.id WHERE posts.added < ' . sqlesc($dt)) or sqlerr(__FILE__, __LINE__);
    if ($queries > 0) {
        write_log("Readpost Cleanup: Completed using $queries queries");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items deleted/updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
