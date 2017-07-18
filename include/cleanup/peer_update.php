<?php
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL			                                    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V4					    |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless,putyn.					    |
 |--------------------------------------------------------------------------|
  _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
 / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
/** sync torrent counts - pdq **/
function docleanup($data)
{
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(0);
    ignore_user_abort(1);
    require_once (INCL_DIR . 'ann_functions.php');
    $torrent_seeds = $torrent_leeches = array();
    $deadtime = TIME_NOW - floor($INSTALLER09['announce_interval'] * 1.3);
    $dead_peers = sql_query('SELECT torrent, userid, peer_id, seeder FROM peers WHERE last_action < ' . $deadtime);
    while ($dead_peer = mysqli_fetch_assoc($dead_peers)) {
        $torrentid = (int)$dead_peer['torrent'];
        $userid = (int)$dead_peer['userid'];
        $seed = $dead_peer['seeder'] === 'yes'; // you use 'yes' i thinks :P
        sql_query('DELETE FROM peers WHERE torrent = ' . $torrentid . ' AND peer_id = ' . sqlesc($dead_peer['peer_id']));
        if (!isset($torrent_seeds[$torrentid])) $torrent_seeds[$torrentid] = $torrent_leeches[$torrentid] = 0;
        if ($seed) $torrent_seeds[$torrentid]++;
        else $torrent_leeches[$torrentid]++;
    }
    foreach (array_keys($torrent_seeds) as $tid) {
        $update = array();
        adjust_torrent_peers($tid, -$torrent_seeds[$tid], -$torrent_leeches[$tid], 0);
        if ($torrent_seeds[$tid]) $update[] = 'seeders = (seeders - ' . $torrent_seeds[$tid] . ')';
        if ($torrent_leeches[$tid]) $update[] = 'leechers = (leechers - ' . $torrent_leeches[$tid] . ')';
        sql_query('UPDATE torrents SET ' . implode(', ', $update) . ' WHERE id = ' . $tid);
    }
    if ($queries > 0) write_log("Peers clean-------------------- Peer cleanup Complete using $queries queries --------------------");
    if (false !== mysqli_affected_rows($GLOBALS["___mysqli_ston"])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS["___mysqli_ston"]) . " items deleted/updated";
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
function cleanup_log($data)
{
    $text = sqlesc($data['clean_title']);
    $added = TIME_NOW;
    $ip = sqlesc($_SERVER['REMOTE_ADDR']);
    $desc = sqlesc($data['clean_desc']);
    sql_query("INSERT INTO cleanup_log (clog_event, clog_time, clog_ip, clog_desc) VALUES ($text, $added, $ip, {$desc})") or sqlerr(__FILE__, __LINE__);
}
?>
