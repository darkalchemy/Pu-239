<?php

/**
 * @param $data
 */
function newsrss_cleanup($data)
{
    $time_start = microtime(true);
    global $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);

    $fluent->deleteFrom('newsrss')
        ->where('added < NOW() - INTERVAL 30 DAY')
        ->execute();

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('NewsRSS Cleanup: Completed using 1 queries' . $text);
    }
}
