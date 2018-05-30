<?php
/**
 * @param $data
 */
function processkill_update($data)
{
    global $queries;

    set_time_limit(1200);
    ignore_user_abort(true);

    $sql = sql_query('SHOW PROCESSLIST');
    $cnt = 0;
    while ($arr = mysqli_fetch_assoc($sql)) {
        if ($arr['db'] == $_ENV['DB_DATABASE'] && $arr['Command'] === 'Sleep' && $arr['Time'] > 120) {
            sql_query("KILL {$arr['Id']}");
            ++$cnt;
        }
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Process Kill Cleanup: Completed using $queries queries");
    }
}
