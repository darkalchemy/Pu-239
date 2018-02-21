<?php
/**
 * @param $data
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function king_update($data)
{
    global $site_config, $queries;

$cache = new Cache();
    set_time_limit(1200);
    ignore_user_abort(true);

    $dt = TIME_NOW;
    $res = sql_query('SELECT id, modcomment FROM users WHERE king > 0 AND king < ' . $dt) or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $subject = 'King status expired.';
        $msg = "Your King status has timed out and has been auto-removed by the system. If you would like to have it again, exchange some Karma Bonus Points again. Cheers!\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = $arr['modcomment'];
            $modcomment = get_date($dt, 'DATE', 1) . " - King status Automatically Removed By System.\n" . $modcomment;
            $modcom = sqlesc($modcomment);
            $msgs_buffer[] = '(0,' . $arr['id'] . ',' . $dt . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ' )';
            $users_buffer[] = '(' . $arr['id'] . ', \'0\', ' . $modcom . ')';
            $cache->update_row('user' . $arr['id'], [
                'king'       => 0,
                'modcomment' => $modcomment,
            ], $site_config['expires']['user_cache']);
            $cache->increment('inbox_' . $arr['id']);
        }
        $count = count($users_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO users (id, king, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE king = VALUES(king), modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup - Removed King status from ' . $count . ' members');
        }
        unset($users_buffer, $msgs_buffer, $count);
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("King Cleanup: Completed using $queries queries");
    }
}
