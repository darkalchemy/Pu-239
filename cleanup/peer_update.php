<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Torrent;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function peer_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $fluent = $container->get(Database::class);
    $cache = $container->get(Cache::class);
    require_once INCL_DIR . 'function_announce.php';
    $torrent_seeds = $torrent_leeches = [];
    $deadtime = TIME_NOW - floor($site_config['tracker']['announce_interval'] * 1.3);
    $dead_peers = $fluent->from('peers')
                         ->select(null)
                         ->select('id')
                         ->select('torrent')
                         ->select('userid')
                         ->select('seeder')
                         ->where('last_action < ?', $deadtime);
    foreach ($dead_peers as $dead_peer) {
        $torrentid = $dead_peer['torrent'];
        $fluent->deleteFrom('peers')
               ->where('id = ?', $dead_peer['id'])
               ->execute();
        if (!isset($torrent_seeds[$torrentid])) {
            $torrent_seeds[$torrentid] = $torrent_leeches[$torrentid] = 0;
        }
        if ($dead_peer['seeder'] === 'yes') {
            ++$torrent_seeds[$torrentid];
        } else {
            ++$torrent_leeches[$torrentid];
        }
        $cache->delete('peers_' . $dead_peer['userid']);
    }
    $torrents_class = $container->get(Torrent::class);
    foreach (array_keys($torrent_seeds) as $tid) {
        $torrents_class->adjust_torrent_peers($tid, -$torrent_seeds[$tid], -$torrent_leeches[$tid], 0);
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Peers Cleanup: Completed' . $text);
    }
}
