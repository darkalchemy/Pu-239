<?php
function achievement_sreset_update($data)
{
    global $site_config, $queries;
    set_time_limit(1200);
    ignore_user_abort(true);

    //Reset the daily AJAX Chat limits
    sql_query("UPDATE usersachiev SET dailyshouts = 0") or sqlerr(__FILE__, __LINE__);
    if ($queries > 0) {
        write_log("Achievements Cleanup: Daily Shouts reset Completed using $queries queries");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
