<?php

/**
 * @param $data
 */
function newsrss_cleanup($data)
{
    global $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);

    $fluent->deleteFrom('newsrss')
        ->where('added < NOW() - INTERVAL 30 DAY')
        ->execute();

    if ($data['clean_log']) {
        write_log('NewsRSS Cleanup: Completed using 1 queries');
    }
}
