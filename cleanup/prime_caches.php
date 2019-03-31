<?php

/**
 * @param $data
 *
 * @throws Exception
 */
function prime_caches($data)
{
    $time_start = microtime(true);
    global $fluent, $torrent_stuffs, $user_stuffs, $snatched_stuffs;

    $peer_stuffs = new Pu239\Peer();

    set_time_limit(1200);
    ignore_user_abort(true);

    $torrents = $fluent->from('torrents')
                       ->select(null)
                       ->select('id')
                       ->select('info_hash')
                       ->select('owner');

    $users = $fluent->from('users')
                    ->select(null)
                    ->select('users.id')
                    ->innerJoin('snatched ON users.id = snatched.userid')
                    ->groupBy('users.id');

    foreach ($torrents as $torrent) {
        $torrent_stuffs->get($torrent['id']);
        $torrent_stuffs->get_torrent_from_hash($torrent['info_hash']);
        $user_stuffs->getUserFromId($torrent['owner']);
        $peer_stuffs->get_torrent_peers_by_tid($torrent['id']);
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Prime Caches Cleanup: Completed' . $text);
    }
}
