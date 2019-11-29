<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Peer;
use Pu239\Torrent;
use Pu239\User;

require_once INCL_DIR . 'function_imdb.php';

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
                       ->select('imdb_id')
                       ->select('info_hash')
                       ->select('owner');
    /*
        $users = $fluent->from('users')
                        ->select(null)
                        ->select('users.id')
                        ->innerJoin('snatched ON users.id=snatched.userid')
                        ->groupBy('users.id');
    */
    $torrents_class = $container->get(Torrent::class);
    $users_class = $container->get(User::class);
    $peer_class = $container->get(Peer::class);
    foreach ($torrents as $torrent) {
        $torrents_class->get($torrent['id']);
        $torrents_class->format_descr($torrent['id']);
        $torrents_class->get_torrent_from_hash($torrent['info_hash']);
        $users_class->getUserFromId($torrent['owner']);
        $peer_class->get_torrent_peers_by_tid($torrent['id']);
        get_imdb_info($torrent['imdb_id'], true, false);
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Prime Caches Cleanup: Completed' . $text);
    }
}
