<?php
/**
 * @param $data
 */
function freeslot_update($data)
{
    global $queries;
    set_time_limit(1200);
    ignore_user_abort(true);

    $dt = TIME_NOW;
    sql_query('UPDATE `freeslots` SET `addedup` = 0 WHERE `addedup` != 0 AND `addedup` < ' . $dt) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE `freeslots` SET `addedfree` = 0 WHERE `addedfree` != 0 AND `addedfree` < ' . $dt) or sqlerr(__FILE__, __LINE__);
    sql_query('DELETE FROM `freeslots` WHERE `addedup` = 0 AND `addedfree` = 0') or sqlerr(__FILE__, __LINE__);
    if ($data['clean_log'] && $queries > 0) {
        write_log("Freeslot Cleanup: Completed using $queries queries");
    }
}
