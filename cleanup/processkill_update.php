<?php

/**
 * @param $data
 */
function processkill_update($data)
{
    global $site_config, $queries;

    $time_start = microtime(true);
    dbconn();
    set_time_limit(1200);
    ignore_user_abort(true);

    $sql = sql_query('SHOW PROCESSLIST') or sqlerr(__FILE__, __LINE__);
    $cnt = 0;
    while ($arr = mysqli_fetch_assoc($sql)) {
        if ($arr['db'] == $site_config['database']['database'] && $arr['Command'] === 'Sleep' && $arr['Time'] > 120) {
            sql_query("KILL {$arr['Id']}") or sqlerr(__FILE__, __LINE__);
            ++$cnt;
        }
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $queries > 0) {
        write_log("Process Kill Cleanup: Completed using $queries queries" . $text);
    }
}
