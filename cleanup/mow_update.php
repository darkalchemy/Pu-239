<?php

/**
 * @param $data
 */
function mow_update($data)
{
    $time_start = microtime(true);
    global $site_config, $fluent, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);

    $mow = $fluent->from('torrents')
        ->select(null)
        ->select('id')
        ->select('name')
        ->where('times_completed>10')
        ->where('category', $site_config['categories']['movie'])
        ->orderBy('RAND()')
        ->limit(1)
        ->fetch();

    if (!empty($mow)) {
        $set = [
            'value_u' => $mow['id'],
            'value_i' => TIME_NOW,
        ];
        if ($data['clean_log']) {
            write_log('Torrent [' . (int) $arr['id'] . '] [' . htmlentities($arr['name']) . "] was set 'Best Film of the Week' by system");
        }
    } else {
        $set = [
            'value_u' => 0,
            'value_i' => TIME_NOW,
        ];
        if ($data['clean_log']) {
            write_log("'Best Film of the Week' was emptied by system");
        }
    }
    $fluent->update('avps')
        ->set($set)
        ->where("avps.arg = 'bestfilmofweek'")
        ->execute();

    $cache->delete('motw_');
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Movie of the Week Cleanup: Completed.' . $text);
    }
}
