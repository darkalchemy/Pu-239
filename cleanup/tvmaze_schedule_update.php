<?php

function tvmaze_schedule_update($data)
{
    require_once INCL_DIR . 'function_tvmaze.php';
    global $cache, $BLOCKS, $image_stuffs;

    set_time_limit(1200);
    if (!$BLOCKS['tvmaze_api_on']) {
        return;
    }

    $tvmaze_data = get_schedule(false);

    $start = date('Y-m-d');
    $date = new DateTime($start);
    $end = $date->modify('+1 day')
        ->format('Y-m-d');

    $i = 0;
    if (!empty($tvmaze_data)) {
        foreach ($tvmaze_data as $tv) {
            if ($tv['airdate'] >= $start && $tv['airdate'] <= $end && $tv['_embedded']['show']['language'] === 'English') {
                $poster = !empty($tv['image']['original']) ? $tv['image']['original'] : !empty($tv['_embedded']['show']['image']['original']) ? $tv['_embedded']['show']['image']['original'] : '';
                if (!empty($poster)) {
                    $insert = $cache->get('insert_tvmaze_tvmazeid_' . $tv['id']);
                    if ($insert === false || is_null($insert)) {
                        $values = [
                            'tvmaze_id' => $tv['id'],
                            'url' => $poster,
                            'type' => 'poster',
                        ];
                        $image_stuffs->insert($values);
                        $cache->set('insert_tvmaze_tvmazeid_' . $tv['id'], 0, 604800);
                        ++$i;
                        url_proxy($poster, true, 150);
                        url_proxy($poster, true, null, null, 20);
                    }
                }
            }
        }
    }

    if ($data['clean_log']) {
        write_log('TVMaze Schedule Cleanup: Completed using 0 queries, processed $i images');
    }
}
