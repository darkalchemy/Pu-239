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

    $start = date('Y-m-d');
    $date = new DateTime($start);
    $end = $date->modify('+1 week')->format('Y-m-d');

    if (!empty($tvmaze_data)) {
        foreach ($tvmaze_data as $tv) {
            if ($tv['airdate'] >= $start && $tv['airdate'] <= $end && $tv['_embedded']['show']['language'] === 'English') {
                $poster = !empty($tv['image']['original']) ? $tv['image']['original'] : !empty($tv['_embedded']['show']['image']['original']) ? $tv['_embedded']['show']['image']['original'] : '';
                if (!empty($poster)) {
                    url_proxy($poster, true, 150);
                    url_proxy($poster, true, null, null, 20);
                }
            }
        }
    }

    if ($data['clean_log']) {
        write_log('TVMaze Schedule Cleanup: Completed using 0 queries');
    }
}
