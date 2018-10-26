<?php

function torrents_update($data)
{
    global $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);
    $torrents = $fluent->from('torrents')
        ->select(null)
        ->select('id')
        ->select('seeders')
        ->select('leechers')
        ->select('comments')
        ->orderBy('id')
        ->fetchAll();

    $peers = $fluent->from('peers')
        ->select(null)
        ->select('seeder')
        ->select('torrent')
        ->fetchAll();

    $comments = $fluent->from('comments')
        ->select(null)
        ->select('torrent')
        ->fetchAll();

    foreach ($torrents as $torrent) {
        $torrent['seeders_num'] = $torrent['leechers_num'] = $torrent['comments_num'] = 0;

        foreach ($peers as $peer) {
            if ($peer['torrent'] === $torrent['id']) {
                if ($seeder === 'yes') {
                    ++$torrent['seeders_num'];
                } else {
                    ++$torrent['leechers_num'];
                }
            }
        }

        foreach ($comments as $comment) {
            if ($comment['torrent'] === $torrent['id']) {
                ++$torrent['comments_num'];
            }
        }

        if ($torrent['seeders'] != $torrent['seeders_num'] || $torrent['leechers'] != $torrent['leechers_num'] || $torrent['comments'] != $torrent['comments_num']) {
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

    if ($data['clean_log']) {
        write_log('Torrent Cleanup completed');
    }
}
