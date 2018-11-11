<?php

/**
 * @param $data
 *
 * @throws Exception
 */
function prime_caches($data)
{

    global $fluent, $torrent_stuffs, $user_stuffs, $snatched_stuffs;

    $peer_stuffs = new DarkAlchemy\Pu239\Peer();
    $event_stuffs = new DarkAlchemy\Pu239\Event();

    set_time_limit(1200);
    ignore_user_abort(true);

    $torrents = $fluent->from('torrents')
        ->select(null)
        ->select('id')
        ->select('info_hash')
        ->select('owner');

    $users = $user_stuffs->get_all_ids();
    $event_stuffs->get_event();
    foreach ($torrents as $torrent) {
        $torrent_stuffs->get($torrent['id']);
        $torrent_stuffs->get_torrent_from_hash($torrent['info_hash']);
        $user_stuffs->getUserFromId($torrent['owner']);
        $peer_stuffs->get_torrent_peers_by_tid($torrent['id']);
        foreach ($users as $user) {
            $snatched_stuffs->get_snatched($user['id'], $torrent['id']);
            $torrent_pass = $user_stuffs->get_item('torrent_pass', $user['id']);
            $user_stuffs->get_user_from_torrent_pass($torrent_pass);
        }
    }

    if ($data['clean_log']) {
        write_log('Prime Caches Cleanup: Completed');
    }
}

