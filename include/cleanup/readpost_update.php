<?php
function docleanup($data)
{
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(1);
    // Remove expired readposts...
    $dt = TIME_NOW - $INSTALLER09['readpost_expiry'];
    sql_query('DELETE read_posts FROM read_posts LEFT JOIN posts ON read_posts.last_post_read = posts.id WHERE posts.added < ' . sqlesc($dt)) or sqlerr(__FILE__, __LINE__);
    if ($queries > 0) {
        write_log("Readpost Clean -------------------- Readpost cleanup Complete using $queries queries --------------------");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items deleted/updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
