<?php
/**
 * @param $data
 */
function silvertorrents_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    //=== Clean silver
    $res = sql_query('SELECT id, silver FROM torrents WHERE silver > 1 AND silver < ' . TIME_NOW) or sqlerr(__FILE__, __LINE__);
    $Silver_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        while ($arr = mysqli_fetch_assoc($res)) {
            $Silver_buffer[] = '(' . $arr['id'] . ', \'0\')';
            $mc1->begin_transaction('torrent_details_' . $arr['id']);
            $mc1->update_row(false, [
                'silver' => 0,
            ]);
            $mc1->commit_transaction($site_config['expires']['torrent_details']);
        }
        $count = count($Silver_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO torrents (id, silver) VALUES ' . implode(', ', $Silver_buffer) . ' ON DUPLICATE key UPDATE silver=values(silver)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup - Removed Silver from ' . $count . ' torrents');
        }
        unset($Silver_buffer, $count);
    }
    //==End
    if ($data['clean_log'] && $queries > 0) {
        write_log("Silver Torrents Cleanup: Completed using $queries queries");
    }
}
