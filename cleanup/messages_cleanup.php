<?php

/**
 * @param $data
 */
function pms_cleanup($data)
{
    $time_start = microtime(true);
    global $message_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);

    $secs = 90 * 86400;
    $dt = TIME_NOW - $secs;
    $messages = $message_stuffs->delete_old_messages($dt);

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && !empty($messages)) {
        write_log('PMs Cleanup completed' . $text);
    }
}
