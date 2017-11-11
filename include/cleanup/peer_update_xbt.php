<?php
/**
 * @param $data
 */
function peer_update_xbt($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    $torrent_seeds = $torrent_leeches = [];
    $deadtime = TIME_NOW - floor($site_config['announce_interval'] * 1.3);
    $dead_peers = sql_query('SELECT fid, uid, peer_id, `left`, `active` FROM xbt_files_users WHERE mtime < ' . $deadtime);
    while ($dead_peer = mysqli_fetch_assoc($dead_peers)) {
        $torrentid = (int)$dead_peer['fid'];
        $userid = (int)$dead_peer['uid'];
        $seed = $dead_peer['left'] === 0;
        sql_query('DELETE FROM xbt_files_users WHERE fid = ' . sqlesc($torrentid) . ' AND peer_id = ' . sqlesc($dead_peer['peer_id']) . ' AND `active` = "0"');
        if (!isset($torrent_seeds[$torrentid])) {
            $torrent_seeds[$torrentid] = $torrent_leeches[$torrentid] = 0;
        }
        if ($seed) {
            ++$torrent_seeds[$torrentid];
        } else {
            ++$torrent_leeches[$torrentid];
        }
    }
    foreach (array_keys($torrent_seeds) as $tid) {
        $update = [];
        if ($torrent_seeds[$tid]) {
            $update[] = 'seeders = (seeders - ' . $torrent_seeds[$tid] . ')';
        }
        if ($torrent_leeches[$tid]) {
            $update[] = 'leechers = (leechers - ' . $torrent_leeches[$tid] . ')';
        }
        sql_query('UPDATE torrents SET ' . implode(', ', $update) . ' WHERE id = ' . sqlesc($tid));
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("XBT Peers Cleanup: Completed using $queries queries");
    }
}
