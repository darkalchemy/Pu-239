<?php

/**
 * @param $data
 *
 * @throws \Envms\FluentPDO\Exception
 */
function delete_torrents_update($data)
{
    $time_start = microtime(true);
    global $site_config, $cache, $fluent, $torrent_stuffs, $message_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);

    $days = 45;
    $dt = (TIME_NOW - ($days * 86400));
    $i = 1;
    $torrents = $fluent->from('torrents')
        ->select(null)
        ->select('id')
        ->select('owner')
        ->select('name')
        ->select('info_hash')
        ->where('last_action < ?', $dt)
        ->where('seeders = 0')
        ->where('leechers = 0');

    $values = [];
    $dt = TIME_NOW;
    foreach ($torrents as $torrent) {
        $torrent_stuffs->delete_by_id($torrent['id']);
        $torrent_stuffs->remove_torrent($torrent['info_hash']);
        $msg = 'Torrent ' . (int) $torrent['id'] . ' (' . htmlsafechars($torrent['name']) . ") was deleted by system (older than $days days and no seeders)";
        $values[] = [
            'sender' => 0,
            'receiver' => $torrent['owner'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => 'Torrent Deleted [Dead]',
        ];
        if ($data['clean_log']) {
            write_log($msg);
        }
    }

    $count = count($values);
    if ($count > 0) {
        ++$i;
        $cache->delete('torrent_poster_count_');
        $message_stuffs->insert($values);
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $i > 0) {
        write_log("Delete Old Torrents Cleanup: Completed using $i queries" . $text);
    }
}
