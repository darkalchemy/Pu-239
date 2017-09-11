<?php
function freeslot_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    sql_query('UPDATE `freeslots` SET `addedup` = 0 WHERE `addedup` != 0 AND `addedup` < ' . TIME_NOW) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE `freeslots` SET `addedfree` = 0 WHERE `addedfree` != 0 AND `addedfree` < ' . TIME_NOW) or sqlerr(__FILE__, __LINE__);
    sql_query('DELETE FROM `freeslots` WHERE `addedup` = 0 AND `addedfree` = 0') or sqlerr(__FILE__, __LINE__);
    if ($queries > 0) {
        write_log("Freeslot Cleanup: Completed using $queries queries");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items deleted/updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
