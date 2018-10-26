<?php

/**
 * @param $data
 */
function visible_update($data)
{
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

    if ($data['clean_log']) {
        write_log('Torrent Visible Cleanup completed');
    }
}
