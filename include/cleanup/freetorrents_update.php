<?php
function freetorrents_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    //=== Clean free
    $res = sql_query('SELECT id, free FROM torrents WHERE free > 1 AND free < ' . TIME_NOW) or sqlerr(__FILE__, __LINE__);
    $Free_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        while ($arr = mysqli_fetch_assoc($res)) {
            $Free_buffer[] = '(' . $arr['id'] . ', \'0\')';
            $mc1->begin_transaction('torrent_details_' . $arr['id']);
            $mc1->update_row(false, [
                'free' => 0,
            ]);
            $mc1->commit_transaction($site_config['expires']['torrent_details']);
        }
        $count = count($Free_buffer);
        if ($data['clean_log'] && $count > 0) {
            sql_query('INSERT INTO torrents (id, free) VALUES ' . implode(', ', $Free_buffer) . ' ON DUPLICATE key UPDATE free=values(free)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup - Removed Free from ' . $count . ' torrents');
        }
        unset($Free_buffer, $count);
    }
    //==End
    if ($data['clean_log'] && $queries > 0) {
        write_log("Free Cleanup: Completed using $queries queries");
    }
}
