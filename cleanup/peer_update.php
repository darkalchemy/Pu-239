<?php

/**
 * @param $data
 */
function peer_update($data)
{
    $time_start = microtime(true);
    dbconn();
    global $site_config, $queries, $torrent_stuffs, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);

    require_once INCL_DIR . 'function_announce.php';
    $torrent_seeds = $torrent_leeches = [];
    $deadtime = TIME_NOW - floor($site_config['tracker']['announce_interval'] * 1.3);
    $dead_peers = sql_query('SELECT torrent, userid, peer_id, seeder FROM peers WHERE last_action < ' . $deadtime) or sqlerr(__FILE__, __LINE__);
    while ($dead_peer = mysqli_fetch_assoc($dead_peers)) {
        $torrentid = (int) $dead_peer['torrent'];
        $seed = $dead_peer['seeder'] === 'yes';
        sql_query('DELETE FROM peers WHERE torrent = ' . $torrentid . ' AND peer_id=' . sqlesc($dead_peer['peer_id'])) or sqlerr(__FILE__, __LINE__);
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
    foreach (array_keys($torrent_seeds) as $tid) {
        $update = [];
        $torrent_stuffs->adjust_torrent_peers($tid, -$torrent_seeds[$tid], -$torrent_leeches[$tid], 0);
        if ($torrent_seeds[$tid]) {
            $update[] = 'seeders = (seeders - ' . $torrent_seeds[$tid] . ')';
        }
        if ($torrent_leeches[$tid]) {
            $update[] = 'leechers = (leechers - ' . $torrent_leeches[$tid] . ')';
        }
        sql_query('UPDATE torrents SET ' . implode(', ', $update) . ' WHERE id=' . $tid) or sqlerr(__FILE__, __LINE__);
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $queries > 0) {
        write_log("Peers Cleanup: Completed using $queries queries" . $text);
    }
}
