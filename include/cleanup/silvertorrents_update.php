<?php
/**
 * @param $data
 */
function silvertorrents_update($data)
{
    global $site_config, $queries, $cache;
    set_time_limit(1200);
    ignore_user_abort(true);

    $res = sql_query('SELECT id, silver FROM torrents WHERE silver > 1 AND silver < ' . TIME_NOW) or sqlerr(__FILE__, __LINE__);
    $Silver_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        while ($arr = mysqli_fetch_assoc($res)) {
            $Silver_buffer[] = '(' . $arr['id'] . ', \'0\')';
            $cache->update_row('torrent_details_' . $arr['id'], [
                'silver' => 0,
            ], $site_config['expires']['torrent_details']);
        }
        $count = count($Silver_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO torrents (id, silver) VALUES ' . implode(', ', $Silver_buffer) . ' ON DUPLICATE KEY UPDATE silver = VALUES(silver)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup - Removed Silver from ' . $count . ' torrents');
        }
        unset($Silver_buffer, $count);
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Silver Torrents Cleanup: Completed using $queries queries");
    }
}
