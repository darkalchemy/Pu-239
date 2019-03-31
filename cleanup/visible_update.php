<?php

/**
 * @param $data
 *
 * @throws \Envms\FluentPDO\Exception
 */
function visible_update($data)
{
    $time_start = microtime(true);
    global $site_config, $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);

    $deadtime_tor = TIME_NOW - $site_config['max_dead_torrent_time'];
    $set = [
        'visible' => 'no',
    ];
    $fluent->update('torrents')
           ->set($set)
           ->where('visible = "yes"')
           ->where('last_action < ?', $deadtime_tor)
           ->execute();

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Torrent Visible Cleanup completed' . $text);
    }
}
