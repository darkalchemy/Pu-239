<?php
function announcement_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    //== Delete expired announcements and processors
    sql_query('DELETE announcement_process FROM announcement_process LEFT JOIN users ON announcement_process.user_id = users.id WHERE users.id IS NULL') or sqlerr(__FILE__, __LINE__);
    sql_query('DELETE FROM announcement_main WHERE expires < ' . TIME_NOW) or sqlerr(__FILE__, __LINE__);
    sql_query('DELETE announcement_process FROM announcement_process LEFT JOIN announcement_main ON announcement_process.main_id = announcement_main.main_id WHERE announcement_main.main_id IS NULL') or sqlerr(__FILE__, __LINE__);
    if ($queries > 0) {
        write_log("Announcement Cleanup: Completed using $queries queries");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items deleted/updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
