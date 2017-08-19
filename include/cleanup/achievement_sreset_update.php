<?php
function docleanup($data)
{
    global $INSTALLER09, $queries;
    set_time_limit(0);
    ignore_user_abort(1);
    $dt = TIME_NOW;
    $subject = sqlesc('New Achievement Earned!');
    $points = random_int(1, 3);
    //Reset the daily AJAX Chat limits
    sql_query("UPDATE `usersachiev` SET `dailyshouts` = '0'") or sqlerr(__FILE__, __LINE__);
    if ($queries > 0) {
        write_log("Achievements Cleanup:  Achievements dailyshouts reset Completed using $queries queries");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
