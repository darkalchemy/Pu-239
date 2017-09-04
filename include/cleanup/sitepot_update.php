<?php
function sitepot_update($data)
{
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(1);
    //== sitepot
    sql_query("UPDATE avps SET value_i = 0, value_s = '0' WHERE arg = 'sitepot' AND value_u < " . TIME_NOW . " AND value_s = '1'") or sqlerr(__FILE__, __LINE__);
    $mc1->delete_value('Sitepot_');
    if ($queries > 0) {
        write_log("Sitepot Cleanup: Completed using $queries queries");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items deleted/updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
