<?php
function docleanup($data)
{
    global $INSTALLER09, $queries;
    set_time_limit(1200);
    ignore_user_abort(1);
    $sql = sql_query('SHOW PROCESSLIST');
    $cnt = 0;
    while ($arr = mysqli_fetch_assoc($sql)) {
        if ($arr['db'] == $INSTALLER09['mysql_db'] and $arr['Command'] == 'Sleep' and $arr['Time'] > 60) {
            sql_query("KILL {$arr['Id']}");
            ++$cnt;
        }
    }
    if ($queries > 0) {
        write_log("Proccess Kill clean-------------------- Proccess Kill Complete using $queries queries --------------------");
    }
    if ($cnt != 0) {
        $data['clean_desc'] = "MySQLCleanup killed {$cnt} processes";
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
