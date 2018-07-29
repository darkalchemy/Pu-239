<?php

/**
 * @param $data
 */
function processkill_update($data)
{
    dbconn();
    global $queries;

    set_time_limit(1200);
    ignore_user_abort(true);

    $sql = sql_query('SHOW PROCESSLIST') or sqlerr(__FILE__, __LINE__);
    $cnt = 0;
    while ($arr = mysqli_fetch_assoc($sql)) {
        if ($arr['db'] == $_ENV['DB_DATABASE'] && $arr['Command'] === 'Sleep' && $arr['Time'] > 120) {
            sql_query("KILL {$arr['Id']}") or sqlerr(__FILE__, __LINE__);
            ++$cnt;
        }
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Process Kill Cleanup: Completed using $queries queries");
    }
}
