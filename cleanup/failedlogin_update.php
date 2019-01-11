<?php

/**
 * @param $data
 */
function failedlogin_update($data)
{
    $time_start = microtime(true);
    dbconn();
    global $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);

    $secs = 86400;
    $dt = TIME_NOW - $secs;
    $fluent->deleteFrom('failedlogins')
        ->where('added < ?', $dt)
        ->execute();

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $queries > 0) {
        write_log("Expired Failed Login Cleanup: Completed." . $text);
    }
}
