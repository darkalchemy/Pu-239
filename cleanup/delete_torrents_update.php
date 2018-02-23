<?php
/**
 * @param $data
 */
function delete_torrents_update($data)
{
    global $site_config, $queries, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);

    $days = 30;
    $dt   = (TIME_NOW - ($days * 86400));
    $res  = sql_query("SELECT id, name, owner FROM torrents WHERE last_action < $dt AND seeders='0' AND leechers='0'");
    while ($arr = mysqli_fetch_assoc($res)) {
        sql_query('DELETE peers.*, files.*, comments.*, snatched.*, thankyou.*, thanks.*, bookmarks.*, coins.*, rating.*, torrents.* FROM torrents 
                 LEFT JOIN peers ON peers.torrent = torrents.id
                 LEFT JOIN files ON files.torrent = torrents.id
                 LEFT JOIN comments ON comments.torrent = torrents.id
                 LEFT JOIN thankyou ON thankyou.torid = torrents.id
                 LEFT JOIN thanks ON thanks.torrentid = torrents.id
                 LEFT JOIN bookmarks ON bookmarks.torrentid = torrents.id
                 LEFT JOIN coins ON coins.torrentid = torrents.id
                 LEFT JOIN rating ON rating.torrent = torrents.id
                 LEFT JOIN snatched ON snatched.torrentid = torrents.id
                 WHERE torrents.id = ' . sqlesc($arr['id'])) or sqlerr(__FILE__, __LINE__);
        @unlink("{$site_config['torrent_dir']}/{$arr['id']}.torrent");
        $msg = 'Torrent ' . (int) $arr['id'] . ' (' . htmlsafechars($arr['name']) . ") was deleted by system (older than $days days and no seeders)";
        sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, saved, location) VALUES (0, ' . (int) $arr['owner'] . ', ' .
                  TIME_NOW . ', ' . sqlesc($msg) . ", 'Torrent Deleted', 'yes', 1)") or sqlerr(__FILE__, __LINE__);
        $cache->increment('inbox_' . (int) $arr['owner']);
        $cache->delete('torrent_poster_count_');
        if ($data['clean_log']) {
            write_log($msg);
        }
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Delete Old Torrents Cleanup: Completed using $queries queries");
    }
}
