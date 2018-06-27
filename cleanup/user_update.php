<?php

/**
 * @param $data
 */
function user_update($data)
{
    global $queries;

    set_time_limit(1200);
    ignore_user_abort(true);

    $dt = TIME_NOW;
    sql_query('UPDATE freeslots SET addedup = 0 WHERE addedup != 0 AND addedup < ' . $dt) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE freeslots SET addedfree = 0 WHERE addedfree != 0 AND addedfree < ' . $dt) or sqlerr(__FILE__, __LINE__);
    sql_query('DELETE FROM freeslots WHERE addedup = 0 AND addedfree = 0') or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE users SET free_switch = 0 WHERE free_switch > 1 AND free_switch < ' . $dt) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE torrents SET free = 0 WHERE free > 1 AND free < ' . $dt) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE users SET downloadpos = 1 WHERE downloadpos > 1 AND downloadpos < ' . $dt) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE users SET uploadpos = 1 WHERE uploadpos > 1 AND uploadpos < ' . $dt) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE users SET chatpost = 1 WHERE chatpost > 1 AND chatpost < ' . $dt) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE users SET avatarpos = 1 WHERE avatarpos > 1 AND avatarpos < ' . $dt) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE users SET immunity = 0 WHERE immunity > 1 AND immunity < ' . $dt) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE users SET warned = 0 WHERE warned > 1 AND warned < ' . $dt) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE users SET pirate = 0 WHERE pirate > 1 AND pirate < ' . $dt) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE users SET king = 0 WHERE king > 1 AND king < ' . $dt) or sqlerr(__FILE__, __LINE__);
    if ($data['clean_log'] && $queries > 0) {
        write_log("User Cleanup: Completed using $queries queries");
    }
}
