<?php
/**
 * @param $data
 */
function torrents_update($data)
{
    global $queries;
    set_time_limit(1200);
    ignore_user_abort(true);

    $tsql = 'SELECT t.id, t.seeders, (
    SELECT COUNT(*)
    FROM peers
    WHERE torrent = t.id AND seeder = "yes"
    ) AS seeders_num,
    t.leechers, (
    SELECT COUNT(*)
    FROM peers
    WHERE torrent = t.id
    AND seeder = "no"
    ) AS leechers_num,
    t.comments, (
    SELECT COUNT(*)
    FROM comments
    WHERE torrent = t.id
    ) AS comments_num
    FROM torrents AS t
    ORDER BY t.id ASC';
    $updatetorrents = [];
    $tq = sql_query($tsql);
    while ($t = mysqli_fetch_assoc($tq)) {
        if ($t['seeders'] != $t['seeders_num'] || $t['leechers'] != $t['leechers_num'] || $t['comments'] != $t['comments_num']) {
            $updatetorrents[] = '('.$t['id'].', '.$t['seeders_num'].', '.$t['leechers_num'].', '.$t['comments_num'].')';
        }
    }
    ((mysqli_free_result($tq) || (is_object($tq) && ('mysqli_result' == get_class($tq)))) ? true : false);
    if (!empty($updatetorrents) && count($updatetorrents)) {
        sql_query('INSERT INTO torrents (id, seeders, leechers, comments) VALUES '.implode(', ', $updatetorrents).' ON DUPLICATE KEY UPDATE seeders = VALUES(seeders), leechers = VALUES(leechers), comments = VALUES(comments)');
    }
    unset($updatetorrents);
    if ($data['clean_log'] && $queries > 0) {
        write_log("Torrent Cleanup: Complete using $queries queries");
    }
}
