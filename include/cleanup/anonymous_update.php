<?php
/**
 * @param $data
 */
function anonymous_update($data)
{
    global $site_config, $queries, $cache;
    set_time_limit(1200);
    ignore_user_abort(true);
    $res = sql_query('SELECT id, modcomment FROM users WHERE anonymous_until < ' . TIME_NOW . " AND anonymous_until <> '0'") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $subject = 'Anonymous profile expired.';
        $msg = "Your Anonymous profile has timed out and has been auto-removed by the system. If you would like to have it again, exchange some Karma Bonus Points again. Cheers!\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = $arr['modcomment'];
            $modcomment = get_date(TIME_NOW, 'DATE', 1) . " - Anonymous profile Automatically Removed By System.\n" . $modcomment;
            $modcom = sqlesc($modcomment);
            $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ' )';
            $users_buffer[] = '(' . $arr['id'] . ', \'0\', \'no\', ' . $modcom . ')';
            $cache->update_row('user' . $arr['id'], [
                'anonymous_until' => 0,
                'anonymous'       => 'no',
            ], $site_config['expires']['user_cache']);
            $cache->update_row('user_stats_' . $arr['id'], [
                'modcomment' => $modcomment,
            ], $site_config['expires']['user_stats']);
            $cache->update_row('MyUser_' . $arr['id'], [
                'anonymous_until' => 0,
                'anonymous'       => 'no',
            ], $site_config['expires']['curuser']);
            $cache->increment('inbox_' . $arr['id']);
        }
        $count = count($users_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO users (id, anonymous_until, anonymous, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE anonymous_until = VALUES(anonymous_until),anonymous = VALUES(anonymous), modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup - Removed Anonymous profile from ' . $count . ' members');
        }
        unset($users_buffer, $msgs_buffer, $count);
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Anonymous Profile Cleanup: Completed using $queries queries");
    }
}
