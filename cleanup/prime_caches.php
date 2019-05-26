<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Peer;
use Pu239\Torrent;
use Pu239\User;

/**
 * @param $data
 *
 * @throws Exception
 */
function prime_caches($data)
{
    global $container;

    //TODO not in use yet

    $time_start = microtime(true);
    $fluent = $container->get(Database::class);
    $torrents = $fluent->from('torrents')
                       ->select(null)
                       ->select('id')
                       ->select('info_hash')
                       ->select('owner');
    /*
        $users = $fluent->from('users')
                        ->select(null)
                        ->select('users.id')
                        ->innerJoin('snatched ON users.id=snatched.userid')
                        ->groupBy('users.id');
    */
    $torrent_stuffs = $container->get(Torrent::class);
    $user_stuffs = $container->get(User::class);
    $peer_stuffs = $container->get(Peer::class);
    foreach ($torrents as $torrent) {
        $torrent_stuffs->get($torrent['id']);
        $torrent_stuffs->format_descr($torrent['id']);
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
