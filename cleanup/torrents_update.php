<?php

/**
 * @param $data
 *
 * @throws Exception
 */
function torrents_update($data)
{
    global $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);
    $i = 1;
    $torrents = $fluent->from('torrents')
        ->select(null)
        ->select('seeders')
        ->select('leechers')
        ->select('comments')
        ->orderBy('id');

    foreach ($torrents as $torrent) {
        $i++;
        $seeders = $fluent->from('peers')
            ->select(null)
            ->select('COUNT(*) AS count')
            ->where('seeder = ?', 'yes')
            ->where('torrent = ?', $torrent['id'])
            ->fetch('count');
        $torrent['seeders_num'] = $seeders;

        $i++;
        $leechers = $fluent->from('peers')
            ->select(null)
            ->select('COUNT(*) AS count')
            ->where('seeder = ?', 'no')
            ->where('torrent = ?', $torrent['id'])
            ->fetch('count');
        $torrent['leechers_num'] = $leechers;

        $i++;
        $comments = $fluent->from('comments')
            ->select(null)
            ->select('COUNT(*) AS count')
            ->where('torrent = ?', $torrent['id'])
            ->fetch('count');
        $torrent['comments_num'] = $comments;

        if ($torrent['seeders'] != $torrent['seeders_num'] || $torrent['leechers'] != $torrent['leechers_num'] || $torrent['comments'] != $torrent['comments_num']) {
            $i++;
            $set = [
                'seeders' => $torrent['seeders_num'],
                'leechers' => $torrent['leechers_num'],
                'comments' => $torrent['comments_num'],
            ];
            $fluent->update('torrents')
                ->set($set)
                ->where('id = ?', $torrent['id'])
                ->execute();
        }
    }


    if ($data['clean_log'] && $i > 0) {
        write_log("Torrent Cleanup: Complete using $i queries");
    }
}
