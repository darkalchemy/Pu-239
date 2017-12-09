<?php
/**
 * @param $data
 */
function delete_torrents_xbt_update($data)
{
    global $site_config, $queries, $cache;
    set_time_limit(1200);
    ignore_user_abort(true);

    $days = 30;
    $dt = (TIME_NOW - ($days * 86400));
    sql_query("UPDATE torrents SET flags='1' WHERE added < $dt AND seeders='0' AND leechers='0'") or sqlerr(__FILE__, __LINE__);
    $res = sql_query("SELECT id, name, owner FROM torrents WHERE mtime < $dt AND seeders='0' AND leechers='0' AND flags='1'") or sqlerr(__FILE__, __LINE__);
    while ($arr = mysqli_fetch_assoc($res)) {
        sql_query('DELETE files.*, comments.*, thankyou.*, thanks.*, thumbsup.*, bookmarks.*, coins.*, rating.*, xbt_files_users.* FROM xbt_files_users
                                 LEFT JOIN files ON files.torrent = xbt_files_users.fid
                                 LEFT JOIN comments ON comments.torrent = xbt_files_users.fid
                                 LEFT JOIN thankyou ON thankyou.torid = xbt_files_users.fid
                                 LEFT JOIN thanks ON thanks.torrentid = xbt_files_users.fid
                                 LEFT JOIN bookmarks ON bookmarks.torrentid = xbt_files_users.fid
                                 LEFT JOIN coins ON coins.torrentid = xbt_files_users.fid
                                 LEFT JOIN rating ON rating.torrent = xbt_files_users.fid
                                 LEFT JOIN thumbsup ON thumbsup.torrentid = xbt_files_users.fid
                                 WHERE xbt_files_users.fid =' . sqlesc($arr['id'])) or sqlerr(__FILE__, __LINE__);

        @unlink("{$site_config['torrent_dir']}/{$arr['id']}.torrent");
        $msg = 'Torrent ' . (int)$arr['id'] . ' (' . htmlsafechars($arr['name']) . ") was deleted by system (older than $days days and no seeders)";
        sql_query("INSERT INTO messages (sender, receiver, added, msg, subject, saved, location) VALUES (0, " . (int)$arr['owner'] . ", " .
            TIME_NOW . ", " . sqlesc($msg) . ", 'Torrent Deleted', 'yes', 1)") or sqlerr(__FILE__, __LINE__);
        $cache->increment('inbox_' . (int)$arr['owner']);
        if ($data['clean_log']) {
            write_log($msg);
        }
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Delete Old Torrents XBT Clean -------------------- Delete Old XBT Torrents cleanup Complete using $queries queries --------------------");
    }
}
