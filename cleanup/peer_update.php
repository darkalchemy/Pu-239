<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
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
    require_once INCL_DIR . 'function_announce.php';
    $torrent_seeds = $torrent_leeches = [];
    $deadtime = TIME_NOW - floor($site_config['tracker']['announce_interval'] * 1.3);
    $dead_peers = sql_query('SELECT torrent, userid, peer_id, seeder FROM peers WHERE last_action < ' . $deadtime) or sqlerr(__FILE__, __LINE__);
    $cache = $container->get(Cache::class);
    while ($dead_peer = mysqli_fetch_assoc($dead_peers)) {
        $torrentid = (int) $dead_peer['torrent'];
        $seed = $dead_peer['seeder'] === 'yes';
        sql_query('DELETE FROM peers WHERE torrent = ' . $torrentid . ' AND peer_id = ' . sqlesc($dead_peer['peer_id'])) or sqlerr(__FILE__, __LINE__);
        if (!isset($torrent_seeds[$torrentid])) {
            $torrent_seeds[$torrentid] = $torrent_leeches[$torrentid] = 0;
        }
        if ($seed) {
            ++$torrent_seeds[$torrentid];
        } else {
            ++$torrent_leeches[$torrentid];
        }
        $cache->delete('peers_' . $dead_peer['userid']);
    }
    $torrent_stuffs = $container->get(Torrent::class);
    foreach (array_keys($torrent_seeds) as $tid) {
        $torrent_stuffs->adjust_torrent_peers($tid, -$torrent_seeds[$tid], -$torrent_leeches[$tid], 0);
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Peers Cleanup: Completed' . $text);
    }
}
