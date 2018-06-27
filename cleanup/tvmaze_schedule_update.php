<?php

/**
 * @param $data
 *
 * @throws Exception
 */
function tvmaze_schedule_update($data)
{
    require_once INCL_DIR . 'function_tvmaze.php';
    global $cache;

    $tvmaze_data = get_schedule(false);

    if ($data['clean_log']) {
        write_log('TVMaze Schedule Cleanup: Completed using 0 queries');
    }
}
